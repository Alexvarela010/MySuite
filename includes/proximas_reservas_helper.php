<?php
/**
 * Helper de Próximas Reservas - My Suite In Cartagena
 * Gestión rápida de próximas reservas, depósitos, totales y balances por recibir.
 */

require_once __DIR__ . '/../config/database.php';

class ProximasReservasHelper {

    private static bool $tablesVerified = false;

    private static array $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];

    /**
     * Asegura que la tabla proximas_reservas y el registro en modulos/rol_permisos existan
     */
    public static function ensureTablesExist(PDO $db): void {
        if (self::$tablesVerified) {
            return;
        }
        self::$tablesVerified = true;

        try {
            $db->exec("
                CREATE TABLE IF NOT EXISTS proximas_reservas (
                    id_proxima INT AUTO_INCREMENT PRIMARY KEY,
                    id_reserva INT NULL,
                    fecha_inicio DATE NOT NULL,
                    fecha_fin DATE NOT NULL,
                    fechas_texto VARCHAR(150) NULL,
                    numero_noches INT NOT NULL DEFAULT 1,
                    precio_noche DECIMAL(12,2) NULL,
                    deposito DECIMAL(12,2) NULL DEFAULT 0,
                    total DECIMAL(12,2) NULL,
                    balance_recibir DECIMAL(12,2) NULL,
                    nombre_vendedor VARCHAR(255) NOT NULL,
                    notas TEXT NULL,
                    grupo ENUM('proximas', 'futuras') NOT NULL DEFAULT 'proximas',
                    estado ENUM('pendiente', 'completada', 'cancelada') NOT NULL DEFAULT 'pendiente',
                    creado_por VARCHAR(150) NULL,
                    activo TINYINT(1) DEFAULT 1,
                    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_id_reserva (id_reserva),
                    INDEX idx_fecha_inicio (fecha_inicio),
                    INDEX idx_grupo (grupo),
                    INDEX idx_estado (estado),
                    INDEX idx_activo (activo)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Auto-migración: asegurar que id_reserva exista en proximas_reservas
            try {
                $chkCol = $db->query("SHOW COLUMNS FROM proximas_reservas LIKE 'id_reserva'")->fetch();
                if (!$chkCol) {
                    $db->exec("ALTER TABLE proximas_reservas ADD COLUMN id_reserva INT NULL AFTER id_proxima, ADD INDEX idx_id_reserva (id_reserva)");
                }
            } catch (\Throwable $eCol) {
                // Ignorar si ya existe
            }

            // Registrar el módulo en la tabla modulos si no existe (para que aparezca en el Sidebar y RBAC)
            try {
                $chkMod = $db->query("SELECT COUNT(*) FROM modulos WHERE codigo = 'proximas_reservas'")->fetchColumn();
                if ((int)$chkMod === 0) {
                    $db->exec("
                        INSERT INTO modulos (codigo, nombre, descripcion, icono, ruta, orden, activo)
                        VALUES (
                            'proximas_reservas',
                            'Próximas Reservas',
                            'Control rápido de próximas reservas, depósitos y balances por recibir',
                            'fas fa-calendar-check',
                            '/app/views/admin/proximas_reservas.php',
                            3,
                            1
                        )
                    ");
                }

                // Asegurar permisos por defecto para admin y operador si no existen
                $stmtPerm = $db->prepare("
                    INSERT IGNORE INTO rol_permisos (rol_codigo, modulo_codigo, permitido)
                    VALUES (?, 'proximas_reservas', 1)
                ");
                $stmtPerm->execute(['admin']);
                $stmtPerm->execute(['operador']);
            } catch (\Throwable $eMod) {
                error_log("Warning registering proximas_reservas module: " . $eMod->getMessage());
            }
        } catch (\Throwable $e) {
            error_log("Error in ProximasReservasHelper::ensureTablesExist: " . $e->getMessage());
        }
    }

    /**
     * Formatea el rango de fechas en español (ej: "7 al 13 de Noviembre" o "30 de Noviembre al 4 de Diciembre")
     */
    public static function formatFechasTexto(string $inicio, string $fin): string {
        $t1 = strtotime($inicio);
        $t2 = strtotime($fin);
        if (!$t1 || !$t2) {
            return trim("{$inicio} al {$fin}");
        }

        $d1 = (int)date('j', $t1);
        $m1 = (int)date('n', $t1);
        $y1 = (int)date('Y', $t1);

        $d2 = (int)date('j', $t2);
        $m2 = (int)date('n', $t2);
        $y2 = (int)date('Y', $t2);

        $mes1 = self::$meses[$m1] ?? '';
        $mes2 = self::$meses[$m2] ?? '';

        if ($y1 === $y2 && $m1 === $m2) {
            return "{$d1} al {$d2} de {$mes1}";
        } elseif ($y1 === $y2) {
            return "{$d1} de {$mes1} al {$d2} de {$mes2}";
        }
        return "{$d1} de {$mes1} {$y1} al {$d2} de {$mes2} {$y2}";
    }

    /**
     * Construye las condiciones SQL para filtros
     */
    private static function buildWhereClause(array $filters): array {
        $where = ["p.activo = 1"];
        $params = [];

        if (!empty($filters['grupo']) && in_array($filters['grupo'], ['proximas', 'futuras'])) {
            $where[] = "p.grupo = ?";
            $params[] = $filters['grupo'];
        }

        if (!empty($filters['estado']) && in_array($filters['estado'], ['pendiente', 'completada', 'cancelada'])) {
            $where[] = "p.estado = ?";
            $params[] = $filters['estado'];
        }

        if (!empty($filters['mes']) && (int)$filters['mes'] >= 1 && (int)$filters['mes'] <= 12) {
            $where[] = "(MONTH(p.fecha_inicio) = ? OR MONTH(p.fecha_fin) = ?)";
            $params[] = (int)$filters['mes'];
            $params[] = (int)$filters['mes'];
        }

        if (!empty($filters['anio']) && (int)$filters['anio'] > 2000) {
            $where[] = "YEAR(p.fecha_inicio) = ?";
            $params[] = (int)$filters['anio'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(p.nombre_vendedor LIKE ? OR p.fechas_texto LIKE ? OR p.notas LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        return [
            'sql' => implode(" AND ", $where),
            'params' => $params
        ];
    }

    /**
     * Obtiene el listado de próximas reservas
     */
    public static function getReservas(PDO $db, array $filters = []): array {
        self::ensureTablesExist($db);

        $clause = self::buildWhereClause($filters);
        $sql = "
            SELECT 
                p.id_proxima,
                p.id_reserva,
                DATE_FORMAT(p.fecha_inicio, '%Y-%m-%d') AS fecha_inicio,
                DATE_FORMAT(p.fecha_fin, '%Y-%m-%d') AS fecha_fin,
                p.fechas_texto,
                p.numero_noches,
                p.precio_noche,
                p.deposito,
                p.total,
                p.balance_recibir,
                p.nombre_vendedor,
                p.notas,
                p.grupo,
                p.estado,
                p.creado_por,
                DATE_FORMAT(p.creado_en, '%Y-%m-%d %H:%i') AS creado_en
            FROM proximas_reservas p
            WHERE {$clause['sql']}
            ORDER BY p.fecha_inicio ASC, p.id_proxima ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($clause['params']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene resumen financiero de las próximas reservas filtradas
     */
    public static function getResumen(PDO $db, array $filters = []): array {
        self::ensureTablesExist($db);

        $clause = self::buildWhereClause($filters);
        $sql = "
            SELECT 
                COUNT(*) AS total_reservas,
                COALESCE(SUM(p.numero_noches), 0) AS total_noches,
                COALESCE(SUM(COALESCE(p.total, 0)), 0) AS suma_total,
                COALESCE(SUM(COALESCE(p.deposito, 0)), 0) AS suma_deposito,
                COALESCE(SUM(COALESCE(p.balance_recibir, 0)), 0) AS suma_balance
            FROM proximas_reservas p
            WHERE {$clause['sql']}
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($clause['params']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_reservas' => (int)($row['total_reservas'] ?? 0),
            'total_noches' => (int)($row['total_noches'] ?? 0),
            'suma_total' => (float)($row['suma_total'] ?? 0),
            'suma_deposito' => (float)($row['suma_deposito'] ?? 0),
            'suma_balance' => (float)($row['suma_balance'] ?? 0)
        ];
    }

    /**
     * Limpia un valor numérico monetario opcional (devuelve null si está vacío)
     */
    private static function parseOptionalMoney($val): ?float {
        if ($val === null || trim((string)$val) === '') {
            return null;
        }
        $clean = str_replace(['$', ' ', '.'], '', (string)$val);
        $clean = str_replace(',', '.', $clean);
        if (!is_numeric($clean)) {
            return null;
        }
        return (float)$clean;
    }

    /**
     * Crea o actualiza una próxima reserva
     */
    public static function saveReserva(PDO $db, array $data, ?string $user = null): int {
        self::ensureTablesExist($db);

        $fechaInicio = trim($data['fecha_inicio'] ?? '');
        $fechaFin = trim($data['fecha_fin'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
            throw new InvalidArgumentException("Las fechas de llegada y salida son obligatorias.");
        }
        if (strtotime($fechaFin) < strtotime($fechaInicio)) {
            throw new InvalidArgumentException("La fecha de salida no puede ser anterior a la fecha de llegada.");
        }

        $nombreVendedor = trim($data['nombre_vendedor'] ?? '');
        if (empty($nombreVendedor)) {
            throw new InvalidArgumentException("El campo Nombre / Vendedor es obligatorio.");
        }

        $noches = isset($data['numero_noches']) && (int)$data['numero_noches'] > 0
            ? (int)$data['numero_noches']
            : max(1, (int)round((strtotime($fechaFin) - strtotime($fechaInicio)) / 86400));

        $fechasTexto = trim($data['fechas_texto'] ?? '');
        if (empty($fechasTexto)) {
            $fechasTexto = self::formatFechasTexto($fechaInicio, $fechaFin);
        }

        $precioNoche = self::parseOptionalMoney($data['precio_noche'] ?? null);
        $deposito = self::parseOptionalMoney($data['deposito'] ?? null);
        $total = self::parseOptionalMoney($data['total'] ?? null);

        // Auto-calcular total si no fue enviado manualmente pero sí precio_noche
        if ($total === null && $precioNoche !== null) {
            $total = $precioNoche * $noches;
        }

        // Calcular balance por recibir
        $balanceRecibir = null;
        if ($total !== null) {
            $depVal = $deposito ?? 0.0;
            $balanceRecibir = max(0.0, $total - $depVal);
        }

        $grupo = in_array($data['grupo'] ?? '', ['proximas', 'futuras']) ? $data['grupo'] : 'proximas';
        $estado = in_array($data['estado'] ?? '', ['pendiente', 'completada', 'cancelada']) ? $data['estado'] : 'pendiente';
        $notas = !empty($data['notas']) ? trim($data['notas']) : null;

        $id = !empty($data['id_proxima']) ? (int)$data['id_proxima'] : 0;

        if ($id > 0) {
            $stmt = $db->prepare("
                UPDATE proximas_reservas
                SET
                    fecha_inicio = ?,
                    fecha_fin = ?,
                    fechas_texto = ?,
                    numero_noches = ?,
                    precio_noche = ?,
                    deposito = ?,
                    total = ?,
                    balance_recibir = ?,
                    nombre_vendedor = ?,
                    notas = ?,
                    grupo = ?,
                    estado = ?
                WHERE id_proxima = ? AND activo = 1
            ");
            $stmt->execute([
                $fechaInicio,
                $fechaFin,
                $fechasTexto,
                $noches,
                $precioNoche,
                $deposito,
                $total,
                $balanceRecibir,
                $nombreVendedor,
                $notas,
                $grupo,
                $estado,
                $id
            ]);
            return $id;
        } else {
            $stmt = $db->prepare("
                INSERT INTO proximas_reservas (
                    fecha_inicio, fecha_fin, fechas_texto, numero_noches, precio_noche,
                    deposito, total, balance_recibir, nombre_vendedor, notas,
                    grupo, estado, creado_por, activo
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $fechaInicio,
                $fechaFin,
                $fechasTexto,
                $noches,
                $precioNoche,
                $deposito,
                $total,
                $balanceRecibir,
                $nombreVendedor,
                $notas,
                $grupo,
                $estado,
                $user
            ]);
            return (int)$db->lastInsertId();
        }
    }

    /**
     * Elimina lógicamente una próxima reserva
     */
    public static function deleteReserva(PDO $db, int $id): bool {
        self::ensureTablesExist($db);
        $stmt = $db->prepare("UPDATE proximas_reservas SET activo = 0 WHERE id_proxima = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Sincroniza automáticamente las próximas reservas de la página web (no iCal/Booking/Airbnb)
     * hacia el módulo de Próximas Reservas.
     * - Inserta nuevas reservas aprobadas/abonadas con salida >= hoy.
     * - Actualiza montos/depósitos/balances si pasaron a abonada o pagada.
     * - Marca como completadas las que ya pasaron su fecha de salida.
     * - Marca como canceladas las que fueron canceladas o rechazadas.
     */
    public static function syncFromWebReservas(PDO $db): array {
        self::ensureTablesExist($db);

        $insertadas = 0;
        $actualizadas = 0;
        $completadas = 0;
        $canceladas = 0;

        // 1. Obtener reservas activas de la página web (no iCal) vigentes
        $sqlWeb = "
            SELECT 
                r.id_reserva, r.nombre, r.apellido, r.fecha_entrada, r.fecha_salida,
                r.num_adultos, r.num_ninos, r.total, r.estado, r.estado_pago
            FROM reservas r
            WHERE (r.ical_uid IS NULL OR r.ical_uid = '')
              AND (r.ical_platform IS NULL OR r.ical_platform = '')
              AND r.estado IN ('aprobada', 'abonada')
              AND r.fecha_salida >= CURDATE()
            ORDER BY r.fecha_entrada ASC
        ";
        $reservasWeb = $db->query($sqlWeb)->fetchAll(PDO::FETCH_ASSOC);

        $stmtCheck = $db->prepare("SELECT id_proxima, activo, deposito, total, estado FROM proximas_reservas WHERE id_reserva = ? LIMIT 1");
        $stmtContabSum = null;
        try {
            $chkContabTable = $db->query("SHOW TABLES LIKE 'contabilidad_movimientos'")->fetch();
            if ($chkContabTable) {
                $stmtContabSum = $db->prepare("
                    SELECT COALESCE(SUM(monto), 0) 
                    FROM contabilidad_movimientos 
                    WHERE id_reserva = ? AND tipo = 'entrada' AND activo = 1
                ");
            }
        } catch (\Throwable $eT) {
            $stmtContabSum = null;
        }

        $stmtInsert = $db->prepare("
            INSERT INTO proximas_reservas (
                id_reserva, fecha_inicio, fecha_fin, fechas_texto, numero_noches, precio_noche,
                deposito, total, balance_recibir, nombre_vendedor, notas,
                grupo, estado, creado_por, activo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, 'proximas', 'pendiente', 'Auto-Sync Web', 1)
        ");

        $stmtUpdate = $db->prepare("
            UPDATE proximas_reservas
            SET
                fecha_inicio = ?,
                fecha_fin = ?,
                fechas_texto = ?,
                numero_noches = ?,
                precio_noche = ?,
                deposito = ?,
                total = ?,
                balance_recibir = ?,
                nombre_vendedor = ?
            WHERE id_proxima = ?
        ");

        foreach ($reservasWeb as $r) {
            $idReserva = (int)$r['id_reserva'];
            $fechaInicio = $r['fecha_entrada'];
            $fechaFin = $r['fecha_salida'];
            $t1 = strtotime($fechaInicio);
            $t2 = strtotime($fechaFin);
            $noches = ($t1 && $t2 && $t2 > $t1) ? max(1, (int)round(($t2 - $t1) / 86400)) : 1;
            $fechasTexto = self::formatFechasTexto($fechaInicio, $fechaFin);

            $totalRaw = (float)($r['total'] ?? 0);
            $total = $totalRaw > 0 ? $totalRaw : null;
            $precioNoche = ($total !== null) ? round($total / $noches, 2) : null;

            $estadoRes = strtolower(trim((string)($r['estado'] ?? '')));
            $estadoPago = strtolower(trim((string)($r['estado_pago'] ?? '')));

            $deposito = null;
            if ($total !== null) {
                if ($estadoPago === 'pagada') {
                    $deposito = $total;
                } elseif ($estadoRes === 'abonada') {
                    $sumContab = 0.0;
                    if ($stmtContabSum) {
                        $stmtContabSum->execute([$idReserva]);
                        $sumContab = (float)$stmtContabSum->fetchColumn();
                    }
                    $deposito = ($sumContab > 0) ? min($total, $sumContab) : round($total * 0.20, 2);
                } else {
                    $deposito = 0.0;
                }
            }

            $balanceRecibir = ($total !== null) ? max(0.0, $total - ($deposito ?? 0.0)) : null;

            $nombreCompleto = trim(($r['nombre'] ?? '') . ' ' . ($r['apellido'] ?? ''));
            $pax = (int)($r['num_adultos'] ?? 0) + (int)($r['num_ninos'] ?? 0);
            $nombreVendedor = "{$nombreCompleto} (Directo)" . ($pax > 0 ? " - {$pax} pax" : '');

            $stmtCheck->execute([$idReserva]);
            $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$existing) {
                $stmtInsert->execute([
                    $idReserva,
                    $fechaInicio,
                    $fechaFin,
                    $fechasTexto,
                    $noches,
                    $precioNoche,
                    $deposito,
                    $total,
                    $balanceRecibir,
                    $nombreVendedor
                ]);
                $insertadas++;
            } elseif ((int)$existing['activo'] === 1) {
                $stmtUpdate->execute([
                    $fechaInicio,
                    $fechaFin,
                    $fechasTexto,
                    $noches,
                    $precioNoche,
                    $deposito,
                    $total,
                    $balanceRecibir,
                    $nombreVendedor,
                    (int)$existing['id_proxima']
                ]);
                if ($stmtUpdate->rowCount() > 0) {
                    $actualizadas++;
                }
            }
        }

        // 2. Actualizar estados de reservas vinculadas que fueron canceladas, rechazadas o ya finalizaron
        $stmtCancel = $db->exec("
            UPDATE proximas_reservas p
            INNER JOIN reservas r ON p.id_reserva = r.id_reserva
            SET p.estado = 'cancelada'
            WHERE p.id_reserva IS NOT NULL
              AND p.activo = 1
              AND p.estado != 'cancelada'
              AND r.estado IN ('cancelada', 'rechazada')
        ");
        $canceladas = (int)$stmtCancel;

        $stmtComplete = $db->exec("
            UPDATE proximas_reservas p
            INNER JOIN reservas r ON p.id_reserva = r.id_reserva
            SET p.estado = 'completada'
            WHERE p.id_reserva IS NOT NULL
              AND p.activo = 1
              AND p.estado = 'pendiente'
              AND r.estado IN ('aprobada', 'abonada')
              AND r.fecha_salida < CURDATE()
        ");
        $completadas = (int)$stmtComplete;

        return [
            'insertadas' => $insertadas,
            'actualizadas' => $actualizadas,
            'completadas' => $completadas,
            'canceladas' => $canceladas
        ];
    }
}

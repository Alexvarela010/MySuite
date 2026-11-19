<?php
/**
 * Helper de Contabilidad - My Suite In Cartagena
 * Manejo centralizado de movimientos de ingresos y gastos, catálogo de categorías y exportaciones.
 */

require_once __DIR__ . '/../config/database.php';

class ContabilidadHelper {

    public static $default_entradas = [
        'WEB RESERVAS 20%',
        'WEB RESERVAS 80%',
        'WEB RESERVAS TOTAL',
        'RESERVA TOTAL BOOKING',
        'RESERVA TOTAL AIRBNB',
        'RESERVA TOTAL FOREVER SUMMER',
        'COMISION TRANSPORTE',
        'COMISION TURISMO',
        'VENTAS MINIMARKET',
        'OTRA ENTRADA'
    ];

    public static $default_salidas = [
        'ASEO APARTAMENTO',
        'ASEO EXTRA',
        'ADMINISTRACION',
        'AGUA',
        'CLARO',
        'COMISION ANDRES',
        'COMISION BOOKING',
        'COMISION EVELYN',
        'ENERGIA',
        'GAS',
        'PRODUCTOS ASEO',
        'FONTUR',
        'MANTENIMIENTO AIRE ACONDICIONADO',
        'OTRA SALIDA',
        'PUBLICIDAD',
        'ALIMENTOS',
        'COMISION 3',
        'COMISION 4',
        'COMPRA EQUIPOS',
        'COMPRAS MINIMARKET',
        'ELECTRODOMESTICOS',
        'LENCERIA',
        'MANTENIMIENTO CLOSETS Y PUERTAS',
        'MUEBLES Y ENSERES',
        'PINTURA',
        'PREDIAL',
        'SEGUROS DE APARTAMENTO',
        'SOFTWARE',
        'TAXI CARTAGENA',
        'TAXI PALMIRA'
    ];

    public static $default_metodos_pago = [
        'EFECTIVO',
        'LLAVE',
        'BOLD',
        'TRANSFERENCIA BANCOLOMBIA',
        'DAVIPLATA',
        'TARJETA DE CREDITO',
        'OTRO'
    ];

    public static $default_responsables = [
        'Evelyn Acuña',
        'Carlos Viveros',
        'Andrés Diaz',
        'Otro'
    ];

    public static $default_cajas = [
        'Evelyn',
        'Carlos'
    ];

    private static bool $tablesVerified = false;

    /**
     * Asegura que las tablas y categorías iniciales existan en la base de datos
     */
    public static function ensureTablesExist(PDO $db): void {
        if (self::$tablesVerified) {
            return;
        }
        self::$tablesVerified = true;
        try {
            $db->exec("
                CREATE TABLE IF NOT EXISTS contabilidad_categorias (
                    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
                    tipo ENUM('entrada', 'salida') NOT NULL,
                    nombre VARCHAR(100) NOT NULL,
                    descripcion VARCHAR(255) NULL,
                    orden INT DEFAULT 0,
                    activo TINYINT(1) DEFAULT 1,
                    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uq_tipo_nombre (tipo, nombre)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $db->exec("
                CREATE TABLE IF NOT EXISTS contabilidad_movimientos (
                    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
                    tipo ENUM('entrada', 'salida') NOT NULL,
                    fecha DATE NOT NULL,
                    categoria VARCHAR(100) NOT NULL,
                    monto DECIMAL(12,2) NOT NULL,
                    caja VARCHAR(50) NOT NULL DEFAULT 'Evelyn',
                    metodo_pago VARCHAR(50) NOT NULL DEFAULT 'EFECTIVO',
                    responsable VARCHAR(100) NULL,
                    descripcion TEXT NULL,
                    comprobante_numero VARCHAR(100) NULL,
                    id_reserva INT NULL,
                    numero_noches INT NULL,
                    cantidad_personas INT NULL,
                    creado_por VARCHAR(150) NULL,
                    activo TINYINT(1) DEFAULT 1,
                    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_fecha (fecha),
                    INDEX idx_tipo (tipo),
                    INDEX idx_categoria (categoria),
                    INDEX idx_caja (caja),
                    INDEX idx_activo (activo),
                    INDEX idx_reserva (id_reserva)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Auto-migración: asegurar que las columnas 'caja', 'numero_noches' y 'cantidad_personas' existan en contabilidad_movimientos
            try {
                $chkCol = $db->query("SHOW COLUMNS FROM contabilidad_movimientos LIKE 'caja'")->fetch();
                if (!$chkCol) {
                    $db->exec("ALTER TABLE contabilidad_movimientos ADD COLUMN caja VARCHAR(50) NOT NULL DEFAULT 'Evelyn' AFTER monto, ADD INDEX idx_caja (caja)");
                }
                $chkNoches = $db->query("SHOW COLUMNS FROM contabilidad_movimientos LIKE 'numero_noches'")->fetch();
                if (!$chkNoches) {
                    $db->exec("ALTER TABLE contabilidad_movimientos ADD COLUMN numero_noches INT NULL AFTER id_reserva");
                }
                $chkPersonas = $db->query("SHOW COLUMNS FROM contabilidad_movimientos LIKE 'cantidad_personas'")->fetch();
                if (!$chkPersonas) {
                    $db->exec("ALTER TABLE contabilidad_movimientos ADD COLUMN cantidad_personas INT NULL AFTER numero_noches");
                }
            } catch (\Throwable $eCol) {
                // Se ignora si la tabla no existe aún o ya tiene el índice
            }

            // Verificar si hay categorías cargadas
            $chk = $db->query("SELECT COUNT(*) FROM contabilidad_categorias")->fetchColumn();
            if ((int)$chk === 0) {
                $stmt = $db->prepare("
                    INSERT INTO contabilidad_categorias (tipo, nombre, orden, activo) 
                    VALUES (?, ?, ?, 1)
                    ON DUPLICATE KEY UPDATE orden = VALUES(orden)
                ");

                $orden = 10;
                foreach (self::$default_entradas as $cat) {
                    $stmt->execute(['entrada', $cat, $orden]);
                    $orden += 10;
                }

                $orden = 10;
                foreach (self::$default_salidas as $cat) {
                    $stmt->execute(['salida', $cat, $orden]);
                    $orden += 10;
                }
            }

            // Auto-migración de renombramientos, nuevas categorías y ordenamiento solicitado
            try {
                // Solo ejecutar la sincronización completa si aún no está aplicada (verificar orden de ASEO APARTAMENTO = 10 y existencia de WEB RESERVAS TOTAL)
                $needsSync = true;
                $chkSync = $db->query("
                    SELECT COUNT(*) FROM contabilidad_categorias 
                    WHERE (tipo = 'salida' AND nombre = 'ASEO APARTAMENTO' AND orden = 10)
                       OR (tipo = 'entrada' AND nombre = 'WEB RESERVAS TOTAL' AND orden = 30)
                ")->fetchColumn();
                $chkOldMovs = $db->query("
                    SELECT COUNT(*) FROM contabilidad_movimientos 
                    WHERE categoria IN ('RESERVAS 20%', 'RESERVA 80', 'RESERVAS 80%', 'CUOTA EXTRA EDIFICIO', 'SEGUROS APTO', 'COMISION 5', 'MANTENIMIENTO CLOSETS')
                ")->fetchColumn();

                if ((int)$chkSync === 2 && (int)$chkOldMovs === 0) {
                    $needsSync = false;
                }

                if ($needsSync) {
                    $renames = [
                        ['tipo' => 'entrada', 'old' => 'RESERVAS 20%', 'new' => 'WEB RESERVAS 20%'],
                        ['tipo' => 'entrada', 'old' => 'RESERVA 80', 'new' => 'WEB RESERVAS 80%'],
                        ['tipo' => 'entrada', 'old' => 'RESERVAS 80%', 'new' => 'WEB RESERVAS 80%'],
                        ['tipo' => 'salida',  'old' => 'CUOTA EXTRA EDIFICIO', 'new' => 'SEGUROS DE APARTAMENTO'],
                        ['tipo' => 'salida',  'old' => 'SEGUROS APTO', 'new' => 'SEGUROS DE APARTAMENTO'],
                        ['tipo' => 'salida',  'old' => 'COMISION 5', 'new' => 'COMISION BOOKING'],
                        ['tipo' => 'salida',  'old' => 'MANTENIMIENTO CLOSETS', 'new' => 'MANTENIMIENTO CLOSETS Y PUERTAS'],
                    ];

                    $stmtCheckNew = $db->prepare("SELECT id_categoria FROM contabilidad_categorias WHERE tipo = ? AND nombre = ?");
                    $stmtCheckOld = $db->prepare("SELECT id_categoria FROM contabilidad_categorias WHERE tipo = ? AND nombre = ?");
                    $stmtRenameCat = $db->prepare("UPDATE contabilidad_categorias SET nombre = ? WHERE id_categoria = ?");
                    $stmtDeleteCat = $db->prepare("DELETE FROM contabilidad_categorias WHERE id_categoria = ?");
                    $stmtUpdateMov = $db->prepare("UPDATE contabilidad_movimientos SET categoria = ? WHERE tipo = ? AND categoria = ?");

                    foreach ($renames as $r) {
                        $stmtCheckOld->execute([$r['tipo'], $r['old']]);
                        $oldId = $stmtCheckOld->fetchColumn();

                        if ($oldId) {
                            $stmtCheckNew->execute([$r['tipo'], $r['new']]);
                            $newId = $stmtCheckNew->fetchColumn();
                            if ($newId) {
                                $stmtDeleteCat->execute([$oldId]);
                            } else {
                                $stmtRenameCat->execute([$r['new'], $oldId]);
                            }
                        }

                        // Actualizar movimientos históricos que aún tengan el nombre anterior
                        $stmtUpdateMov->execute([$r['new'], $r['tipo'], $r['old']]);
                    }

                    // Asegurar y sincronizar el orden exacto de entradas y salidas en el catálogo
                    $stmtUpsertCat = $db->prepare("
                        INSERT INTO contabilidad_categorias (tipo, nombre, orden, activo) 
                        VALUES (?, ?, ?, 1)
                        ON DUPLICATE KEY UPDATE orden = VALUES(orden), activo = 1
                    ");

                    $orden = 10;
                    foreach (self::$default_entradas as $cat) {
                        $stmtUpsertCat->execute(['entrada', $cat, $orden]);
                        $orden += 10;
                    }

                    $orden = 10;
                    foreach (self::$default_salidas as $cat) {
                        $stmtUpsertCat->execute(['salida', $cat, $orden]);
                        $orden += 10;
                    }
                }
            } catch (\Throwable $eRen) {
                error_log("Warning in ContabilidadHelper category migration: " . $eRen->getMessage());
            }
        } catch (\Throwable $e) {
            error_log("Error in ContabilidadHelper::ensureTablesExist: " . $e->getMessage());
        }
    }

    /**
     * Obtiene el listado de categorías disponibles agrupadas por tipo
     */
    public static function getCategorias(PDO $db, ?string $tipo = null): array {
        self::ensureTablesExist($db);

        $sql = "SELECT id_categoria, tipo, nombre, orden FROM contabilidad_categorias WHERE activo = 1";
        $params = [];
        if ($tipo && in_array($tipo, ['entrada', 'salida'])) {
            $sql .= " AND tipo = ?";
            $params[] = $tipo;
        }
        $sql .= " ORDER BY tipo ASC, orden ASC, nombre ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [
            'entradas' => [],
            'salidas' => []
        ];

        foreach ($rows as $row) {
            if ($row['tipo'] === 'entrada') {
                $result['entradas'][] = $row['nombre'];
            } else {
                $result['salidas'][] = $row['nombre'];
            }
        }

        // Si la tabla estuviese vacía por alguna razón, usar defaults
        if (empty($result['entradas'])) {
            $result['entradas'] = self::$default_entradas;
        }
        if (empty($result['salidas'])) {
            $result['salidas'] = self::$default_salidas;
        }

        if ($tipo === 'entrada') return $result['entradas'];
        if ($tipo === 'salida') return $result['salidas'];

        return $result;
    }

    /**
     * Construye las condiciones SQL para los filtros dados
     */
    private static function buildWhereClause(array $filters): array {
        $where = ["m.activo = 1"];
        $params = [];

        // Filtro por Mes y Año
        if (!empty($filters['mes']) && (int)$filters['mes'] >= 1 && (int)$filters['mes'] <= 12) {
            $where[] = "MONTH(m.fecha) = ?";
            $params[] = (int)$filters['mes'];
        }

        if (!empty($filters['anio']) && (int)$filters['anio'] > 2000) {
            $where[] = "YEAR(m.fecha) = ?";
            $params[] = (int)$filters['anio'];
        }

        // Rango de fechas libre si se especifica
        if (!empty($filters['fecha_desde'])) {
            $where[] = "m.fecha >= ?";
            $params[] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $where[] = "m.fecha <= ?";
            $params[] = $filters['fecha_hasta'];
        }

        // Tipo: entrada / salida
        if (!empty($filters['tipo']) && in_array(strtolower($filters['tipo']), ['entrada', 'salida'])) {
            $where[] = "m.tipo = ?";
            $params[] = strtolower($filters['tipo']);
        }

        // Categoría
        if (!empty($filters['categoria']) && $filters['categoria'] !== 'todas') {
            $where[] = "m.categoria = ?";
            $params[] = trim($filters['categoria']);
        }

        // Método de pago
        if (!empty($filters['metodo_pago']) && $filters['metodo_pago'] !== 'todos') {
            $where[] = "m.metodo_pago = ?";
            $params[] = trim($filters['metodo_pago']);
        }

        // Responsable
        if (!empty($filters['responsable']) && $filters['responsable'] !== 'todos') {
            $where[] = "m.responsable = ?";
            $params[] = trim($filters['responsable']);
        }

        // Filtro por Caja (Evelyn / Carlos)
        if (!empty($filters['caja']) && $filters['caja'] !== 'todas') {
            $cajaFiltro = strcasecmp(trim($filters['caja']), 'Carlos') === 0 ? 'Carlos' : 'Evelyn';
            if ($cajaFiltro === 'Carlos') {
                $where[] = "m.caja = 'Carlos'";
            } else {
                $where[] = "(m.caja = 'Evelyn' OR m.caja IS NULL OR m.caja = '')";
            }
        }

        // Búsqueda de texto (descripción, comprobante, responsable, categoría)
        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(m.descripcion LIKE ? OR m.comprobante_numero LIKE ? OR m.responsable LIKE ? OR m.categoria LIKE ?)";
            $params[] = $term;
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
     * Obtiene los totales acumulados (Entradas, Salidas, Balance, Saldo Caja Evelyn y Saldo Caja Carlos)
     */
    public static function getTotales(PDO $db, array $filters): array {
        self::ensureTablesExist($db);

        $clause = self::buildWhereClause($filters);

        $sql = "
            SELECT 
                COALESCE(SUM(CASE WHEN m.tipo = 'entrada' THEN m.monto ELSE 0 END), 0) AS total_entradas,
                COALESCE(SUM(CASE WHEN m.tipo = 'salida' THEN m.monto ELSE 0 END), 0) AS total_salidas,
                COALESCE(SUM(CASE WHEN (m.caja = 'Evelyn' OR m.caja IS NULL OR m.caja = '') AND m.tipo = 'entrada' THEN m.monto 
                                  WHEN (m.caja = 'Evelyn' OR m.caja IS NULL OR m.caja = '') AND m.tipo = 'salida' THEN -m.monto 
                                  ELSE 0 END), 0) AS saldo_caja_evelyn_periodo,
                COALESCE(SUM(CASE WHEN m.caja = 'Carlos' AND m.tipo = 'entrada' THEN m.monto 
                                  WHEN m.caja = 'Carlos' AND m.tipo = 'salida' THEN -m.monto 
                                  ELSE 0 END), 0) AS saldo_caja_carlos_periodo,
                COUNT(*) AS total_registros
            FROM contabilidad_movimientos m
            WHERE {$clause['sql']}
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($clause['params']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_entradas' => 0, 
            'total_salidas' => 0, 
            'saldo_caja_evelyn_periodo' => 0,
            'saldo_caja_carlos_periodo' => 0,
            'total_registros' => 0
        ];

        // Obtener saldo acumulado total histórico para cada caja (independiente de filtros temporales)
        $sqlCajasGlobal = "
            SELECT 
                COALESCE(SUM(CASE WHEN (m.caja = 'Evelyn' OR m.caja IS NULL OR m.caja = '') AND m.tipo = 'entrada' THEN m.monto 
                                  WHEN (m.caja = 'Evelyn' OR m.caja IS NULL OR m.caja = '') AND m.tipo = 'salida' THEN -m.monto 
                                  ELSE 0 END), 0) AS saldo_caja_evelyn_global,
                COALESCE(SUM(CASE WHEN m.caja = 'Carlos' AND m.tipo = 'entrada' THEN m.monto 
                                  WHEN m.caja = 'Carlos' AND m.tipo = 'salida' THEN -m.monto 
                                  ELSE 0 END), 0) AS saldo_caja_carlos_global
            FROM contabilidad_movimientos m
            WHERE m.activo = 1
        ";
        $rowCajasGlobal = $db->query($sqlCajasGlobal)->fetch(PDO::FETCH_ASSOC) ?: [
            'saldo_caja_evelyn_global' => 0, 
            'saldo_caja_carlos_global' => 0
        ];

        $entradas = (float)$row['total_entradas'];
        $salidas = (float)$row['total_salidas'];
        $balance = $entradas - $salidas;

        return [
            'total_entradas' => $entradas,
            'total_salidas' => $salidas,
            'balance_neto' => $balance,
            'total_registros' => (int)$row['total_registros'],
            'saldo_caja_evelyn' => (float)$rowCajasGlobal['saldo_caja_evelyn_global'],
            'saldo_caja_carlos' => (float)$rowCajasGlobal['saldo_caja_carlos_global'],
            'saldo_caja_evelyn_periodo' => (float)$row['saldo_caja_evelyn_periodo'],
            'saldo_caja_carlos_periodo' => (float)$row['saldo_caja_carlos_periodo']
        ];
    }

    /**
     * Obtiene la lista paginada de movimientos
     */
    public static function getMovimientos(PDO $db, array $filters, int $page = 1, int $limit = 20): array {
        self::ensureTablesExist($db);

        $clause = self::buildWhereClause($filters);

        // Contar total
        $sqlCount = "SELECT COUNT(*) FROM contabilidad_movimientos m WHERE {$clause['sql']}";
        $stmtCount = $db->prepare($sqlCount);
        $stmtCount->execute($clause['params']);
        $totalRecords = (int)$stmtCount->fetchColumn();

        $limit = max(1, min($limit, 500000));
        $totalPages = $totalRecords > 0 ? (int)ceil($totalRecords / $limit) : 1;
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $limit;

        $sql = "
            SELECT 
                m.id_movimiento,
                m.tipo,
                DATE_FORMAT(m.fecha, '%Y-%m-%d') AS fecha,
                DAY(m.fecha) AS dia,
                MONTH(m.fecha) AS mes,
                YEAR(m.fecha) AS anio,
                m.categoria,
                m.monto,
                COALESCE(m.caja, 'Evelyn') AS caja,
                m.metodo_pago,
                m.responsable,
                m.descripcion,
                m.comprobante_numero,
                m.id_reserva,
                m.numero_noches,
                m.cantidad_personas,
                m.creado_por,
                DATE_FORMAT(m.creado_en, '%Y-%m-%d %H:%i') AS creado_en
            FROM contabilidad_movimientos m
            WHERE {$clause['sql']}
            ORDER BY m.fecha DESC, m.id_movimiento DESC
            LIMIT {$limit} OFFSET {$offset}
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($clause['params']);
        $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $movimientos,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $totalRecords,
                'per_page' => $limit
            ]
        ];
    }

    /**
     * Guarda (crea o edita) un movimiento contable
     */
    public static function saveMovimiento(PDO $db, array $data, ?string $user = null): int {
        self::ensureTablesExist($db);

        $tipo = strtolower(trim($data['tipo'] ?? ''));
        if (!in_array($tipo, ['entrada', 'salida'])) {
            throw new InvalidArgumentException("El tipo debe ser 'entrada' o 'salida'.");
        }

        $fecha = trim($data['fecha'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new InvalidArgumentException("Formato de fecha inválido (use YYYY-MM-DD).");
        }

        $categoria = trim($data['categoria'] ?? '');
        if (empty($categoria)) {
            throw new InvalidArgumentException("Debe seleccionar una categoría.");
        }

        // Limpiar monto (soporta cadenas formateadas ej: $ 150.000,00 o 150000)
        $montoRaw = str_replace(['$', ' ', '.'], '', (string)($data['monto'] ?? '0'));
        $montoRaw = str_replace(',', '.', $montoRaw);
        $monto = (float)$montoRaw;
        if ($monto <= 0) {
            throw new InvalidArgumentException("El monto debe ser un valor numérico positivo mayor a 0.");
        }

        $cajaRaw = trim($data['caja'] ?? 'Evelyn');
        $caja = (strcasecmp($cajaRaw, 'Carlos') === 0) ? 'Carlos' : 'Evelyn';

        $metodo_pago = trim($data['metodo_pago'] ?? 'EFECTIVO');
        if (empty($metodo_pago)) $metodo_pago = 'EFECTIVO';

        $responsable = !empty($data['responsable']) ? trim($data['responsable']) : null;
        $descripcion = !empty($data['descripcion']) ? trim($data['descripcion']) : null;
        $comprobante_numero = !empty($data['comprobante_numero']) ? trim($data['comprobante_numero']) : null;
        $id_reserva = !empty($data['id_reserva']) && (int)$data['id_reserva'] > 0 ? (int)$data['id_reserva'] : null;
        $numero_noches = isset($data['numero_noches']) && $data['numero_noches'] !== '' && (int)$data['numero_noches'] > 0 ? (int)$data['numero_noches'] : null;
        $cantidad_personas = isset($data['cantidad_personas']) && $data['cantidad_personas'] !== '' && (int)$data['cantidad_personas'] > 0 ? (int)$data['cantidad_personas'] : null;

        $id_movimiento = !empty($data['id_movimiento']) ? (int)$data['id_movimiento'] : 0;

        if ($id_movimiento > 0) {
            // Actualizar existente
            $stmt = $db->prepare("
                UPDATE contabilidad_movimientos
                SET 
                    tipo = ?,
                    fecha = ?,
                    categoria = ?,
                    monto = ?,
                    caja = ?,
                    metodo_pago = ?,
                    responsable = ?,
                    descripcion = ?,
                    comprobante_numero = ?,
                    id_reserva = ?,
                    numero_noches = ?,
                    cantidad_personas = ?
                WHERE id_movimiento = ? AND activo = 1
            ");
            $stmt->execute([
                $tipo,
                $fecha,
                $categoria,
                $monto,
                $caja,
                $metodo_pago,
                $responsable,
                $descripcion,
                $comprobante_numero,
                $id_reserva,
                $numero_noches,
                $cantidad_personas,
                $id_movimiento
            ]);
            return $id_movimiento;
        } else {
            // Insertar nuevo
            $stmt = $db->prepare("
                INSERT INTO contabilidad_movimientos (
                    tipo, fecha, categoria, monto, caja, metodo_pago, responsable, 
                    descripcion, comprobante_numero, id_reserva, numero_noches, cantidad_personas, creado_por, activo
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $tipo,
                $fecha,
                $categoria,
                $monto,
                $caja,
                $metodo_pago,
                $responsable,
                $descripcion,
                $comprobante_numero,
                $id_reserva,
                $numero_noches,
                $cantidad_personas,
                $user
            ]);
            return (int)$db->lastInsertId();
        }
    }

    /**
     * Elimina un movimiento contable (marcado lógico activo = 0)
     */
    public static function deleteMovimiento(PDO $db, int $id): bool {
        self::ensureTablesExist($db);
        $stmt = $db->prepare("UPDATE contabilidad_movimientos SET activo = 0 WHERE id_movimiento = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Registra automáticamente en contabilidad el abono (20%) o saldo (80% / total)
     * cuando una reserva web de la página se marca como abonada o pagada.
     * Ignora reservas externas de iCal (Booking / Airbnb / Forever Summer) y reservas de $0.
     */
    public static function registrarPagoReserva(PDO $db, int $idReserva, string $etapa, array $options = [], ?string $user = null): array {
        self::ensureTablesExist($db);

        $etapa = strtolower(trim($etapa));
        if (!in_array($etapa, ['abonada', 'pagada'])) {
            return ['success' => false, 'skipped' => true, 'reason' => 'Etapa inválida'];
        }

        $stmtRes = $db->prepare("
            SELECT id_reserva, nombre, apellido, ical_uid, ical_platform,
                   fecha_entrada, fecha_salida, num_adultos, num_ninos,
                   metodo_pago, total, estado, estado_pago
            FROM reservas
            WHERE id_reserva = ?
            LIMIT 1
        ");
        $stmtRes->execute([$idReserva]);
        $reserva = $stmtRes->fetch(PDO::FETCH_ASSOC);

        if (!$reserva) {
            return ['success' => false, 'skipped' => true, 'reason' => 'Reserva no encontrada'];
        }

        // Solo aplica para reservas directas de la página (no Booking, Airbnb, ni iCal)
        if (!empty($reserva['ical_uid']) || !empty($reserva['ical_platform'])) {
            return ['success' => true, 'skipped' => true, 'reason' => 'Reserva externa (iCal) omitida'];
        }

        $total = (float)($reserva['total'] ?? 0);
        if ($total <= 0) {
            return ['success' => true, 'skipped' => true, 'reason' => 'Reserva sin valor total (> 0) omitida'];
        }

        // Calcular categoría y monto según etapa
        if ($etapa === 'abonada') {
            $categoria = 'WEB RESERVAS 20%';
            $monto = round($total * 0.20, 2);
            $etiqueta = 'Anticipo 20%';
        } else {
            $stmt20 = $db->prepare("
                SELECT COUNT(*) FROM contabilidad_movimientos 
                WHERE id_reserva = ? AND categoria = 'WEB RESERVAS 20%' AND activo = 1
            ");
            $stmt20->execute([$idReserva]);
            $tiene20 = ((int)$stmt20->fetchColumn() > 0) || !empty($options['was_abonada']);

            if ($tiene20) {
                $categoria = 'WEB RESERVAS 80%';
                $monto = round($total * 0.80, 2);
                $etiqueta = 'Saldo 80%';
            } else {
                $categoria = 'WEB RESERVAS TOTAL';
                $monto = round($total, 2);
                $etiqueta = 'Pago Total';
            }
        }

        // Evitar registros duplicados para la misma reserva y categoría
        $stmtDup = $db->prepare("
            SELECT id_movimiento FROM contabilidad_movimientos 
            WHERE id_reserva = ? AND categoria = ? AND activo = 1 
            LIMIT 1
        ");
        $stmtDup->execute([$idReserva, $categoria]);
        $existingId = $stmtDup->fetchColumn();
        if ($existingId) {
            return [
                'success' => true,
                'skipped' => true,
                'reason' => "Movimiento {$categoria} ya registrado anteriormente",
                'id_movimiento' => (int)$existingId
            ];
        }

        // Calcular noches y personas
        $t1 = strtotime($reserva['fecha_entrada']);
        $t2 = strtotime($reserva['fecha_salida']);
        $noches = ($t1 && $t2 && $t2 > $t1) ? max(1, (int)round(($t2 - $t1) / 86400)) : 1;
        $personas = (int)($reserva['num_adultos'] ?? 0) + (int)($reserva['num_ninos'] ?? 0);

        // Formatear rango de fechas legible
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $rangoFechas = '';
        if ($t1 && $t2) {
            $d1 = (int)date('j', $t1); $m1 = (int)date('n', $t1); $y1 = (int)date('Y', $t1);
            $d2 = (int)date('j', $t2); $m2 = (int)date('n', $t2); $y2 = (int)date('Y', $t2);
            if ($y1 === $y2 && $m1 === $m2) {
                $rangoFechas = "{$d1} al {$d2} de " . ($meses[$m1] ?? '');
            } else {
                $rangoFechas = "{$d1} de " . ($meses[$m1] ?? '') . " al {$d2} de " . ($meses[$m2] ?? '');
            }
        }

        // Medio de pago
        $metodoOpt = trim((string)($options['metodo_pago'] ?? ''));
        if ($metodoOpt !== '' && strtolower($metodoOpt) !== 'auto') {
            $metodoPago = $metodoOpt;
        } else {
            $mpRaw = strtolower(trim((string)($reserva['metodo_pago'] ?? '')));
            if ($mpRaw === 'tarjeta_credito') {
                $metodoPago = 'TARJETA DE CREDITO';
            } elseif ($mpRaw === 'transferencia') {
                $metodoPago = 'TRANSFERENCIA BANCOLOMBIA';
            } else {
                $metodoPago = 'EFECTIVO';
            }
        }

        // Caja y Responsable
        $caja = (!empty($options['caja']) && strcasecmp(trim((string)$options['caja']), 'Carlos') === 0) ? 'Carlos' : 'Evelyn';
        $responsable = ($caja === 'Carlos') ? 'Carlos Viveros' : 'Evelyn Acuña';

        $nombreHuesped = trim(($reserva['nombre'] ?? '') . ' ' . ($reserva['apellido'] ?? ''));
        $descripcion = "{$etiqueta} Reserva #{$idReserva}"
            . ($nombreHuesped !== '' ? " - {$nombreHuesped}" : '')
            . ($rangoFechas !== '' ? " ({$rangoFechas})" : '');

        $notas = trim((string)($options['notas'] ?? ''));
        if ($notas !== '') {
            $descripcion .= " | {$notas}";
        }

        $idMov = self::saveMovimiento($db, [
            'tipo' => 'entrada',
            'fecha' => date('Y-m-d'),
            'categoria' => $categoria,
            'monto' => $monto,
            'caja' => $caja,
            'metodo_pago' => $metodoPago,
            'responsable' => $responsable,
            'descripcion' => $descripcion,
            'id_reserva' => $idReserva,
            'numero_noches' => $noches,
            'cantidad_personas' => $personas > 0 ? $personas : null
        ], $user);

        return [
            'success' => true,
            'skipped' => false,
            'id_movimiento' => $idMov,
            'categoria' => $categoria,
            'monto' => $monto
        ];
    }

    /**
     * Anula (marcado lógico activo = 0) los movimientos contables asociados a una reserva cancelada.
     * Retorna la cantidad de registros eliminados de contabilidad.
     */
    public static function eliminarMovimientosReserva(PDO $db, int $idReserva): int {
        self::ensureTablesExist($db);
        $stmt = $db->prepare("UPDATE contabilidad_movimientos SET activo = 0 WHERE id_reserva = ? AND activo = 1");
        $stmt->execute([$idReserva]);
        return $stmt->rowCount();
    }
}

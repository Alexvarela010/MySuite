<?php
/**
 * Helper de Contabilidad - My Suite In Cartagena
 * Manejo centralizado de movimientos de ingresos y gastos, catálogo de categorías y exportaciones.
 */

require_once __DIR__ . '/../config/database.php';

class ContabilidadHelper {

    public static $default_entradas = [
        'RESERVAS 20%',
        'RESERVA 80',
        'RESERVA TOTAL BOOKING',
        'RESERVA TOTAL AIRBNB',
        'RESERVA TOTAL FOREVER SUMMER',
        'COMISION TRANSPORTE',
        'COMISION TURISMO',
        'VENTAS MINIMARKET',
        'OTRA ENTRADA'
    ];

    public static $default_salidas = [
        'AGUA',
        'ENERGIA',
        'GAS',
        'CLARO',
        'PRODUCTOS ASEO',
        'ALIMENTOS',
        'LENCERIA',
        'COMPRA EQUIPOS',
        'MANTENIMIENTO AIRE ACONDICIONADO',
        'MANTENIMIENTO CLOSETS',
        'PINTURA',
        'FONTUR',
        'PREDIAL',
        'ADMINISTRACION',
        'ASEO APARTAMENTO',
        'ASEO EXTRA',
        'COMISION EVELYN',
        'COMISION ANDRES',
        'COMISION 3',
        'COMISION 4',
        'COMISION 5',
        'PUBLICIDAD',
        'MUEBLES Y ENSERES',
        'ELECTRODOMESTICOS',
        'CUOTA EXTRA EDIFICIO',
        'TAXI PALMIRA',
        'COMPRAS MINIMARKET',
        'TAXI CARTAGENA',
        'SOFTWARE',
        'OTRA SALIDA'
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

    /**
     * Asegura que las tablas y categorías iniciales existan en la base de datos
     */
    public static function ensureTablesExist(PDO $db): void {
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

            // Auto-migración: asegurar que la columna 'caja' exista en contabilidad_movimientos
            try {
                $chkCol = $db->query("SHOW COLUMNS FROM contabilidad_movimientos LIKE 'caja'")->fetch();
                if (!$chkCol) {
                    $db->exec("ALTER TABLE contabilidad_movimientos ADD COLUMN caja VARCHAR(50) NOT NULL DEFAULT 'Evelyn' AFTER monto, ADD INDEX idx_caja (caja)");
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

        $limit = max(1, min($limit, 500));
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
                    id_reserva = ?
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
                $id_movimiento
            ]);
            return $id_movimiento;
        } else {
            // Insertar nuevo
            $stmt = $db->prepare("
                INSERT INTO contabilidad_movimientos (
                    tipo, fecha, categoria, monto, caja, metodo_pago, responsable, 
                    descripcion, comprobante_numero, id_reserva, creado_por, activo
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
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
}

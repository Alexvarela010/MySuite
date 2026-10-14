<?php
/**
 * Migración de Tablas de Contabilidad - My Suite In Cartagena
 * Crea:
 *  - contabilidad_categorias (catálogo de categorías para Entradas y Salidas basadas en Excel)
 *  - contabilidad_movimientos (registro de ingresos y gastos)
 */

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos.");
    }
    
    echo "1. Creando tabla contabilidad_categorias...\n";
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
    echo "✓ Tabla contabilidad_categorias lista.\n";

    echo "2. Creando tabla contabilidad_movimientos...\n";
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

    // Verificar si la columna 'caja' ya existe por si la tabla fue creada anteriormente
    try {
        $chkCol = $db->query("SHOW COLUMNS FROM contabilidad_movimientos LIKE 'caja'")->fetch();
        if (!$chkCol) {
            $db->exec("ALTER TABLE contabilidad_movimientos ADD COLUMN caja VARCHAR(50) NOT NULL DEFAULT 'Evelyn' AFTER monto, ADD INDEX idx_caja (caja)");
            echo "✓ Columna 'caja' añadida a contabilidad_movimientos.\n";
        }
    } catch (\Throwable $eCol) {
        // Ignorar
    }
    echo "✓ Tabla contabilidad_movimientos lista.\n";

    echo "3. Insertando categorías iniciales desde Excel...\n";
    
    $entradas = [
        ['nombre' => 'RESERVAS 20%', 'orden' => 10],
        ['nombre' => 'RESERVA 80', 'orden' => 20],
        ['nombre' => 'RESERVA TOTAL BOOKING', 'orden' => 30],
        ['nombre' => 'RESERVA TOTAL AIRBNB', 'orden' => 40],
        ['nombre' => 'RESERVA TOTAL FOREVER SUMMER', 'orden' => 50],
        ['nombre' => 'COMISION TRANSPORTE', 'orden' => 60],
        ['nombre' => 'COMISION TURISMO', 'orden' => 70],
        ['nombre' => 'VENTAS MINIMARKET', 'orden' => 80],
        ['nombre' => 'OTRA ENTRADA', 'orden' => 99]
    ];

    $salidas = [
        ['nombre' => 'AGUA', 'orden' => 10],
        ['nombre' => 'ENERGIA', 'orden' => 20],
        ['nombre' => 'GAS', 'orden' => 30],
        ['nombre' => 'CLARO', 'orden' => 40],
        ['nombre' => 'PRODUCTOS ASEO', 'orden' => 50],
        ['nombre' => 'ALIMENTOS', 'orden' => 60],
        ['nombre' => 'LENCERIA', 'orden' => 70],
        ['nombre' => 'COMPRA EQUIPOS', 'orden' => 80],
        ['nombre' => 'MANTENIMIENTO AIRE ACONDICIONADO', 'orden' => 90],
        ['nombre' => 'MANTENIMIENTO CLOSETS', 'orden' => 100],
        ['nombre' => 'PINTURA', 'orden' => 110],
        ['nombre' => 'FONTUR', 'orden' => 120],
        ['nombre' => 'PREDIAL', 'orden' => 130],
        ['nombre' => 'ADMINISTRACION', 'orden' => 140],
        ['nombre' => 'ASEO APARTAMENTO', 'orden' => 150],
        ['nombre' => 'ASEO EXTRA', 'orden' => 160],
        ['nombre' => 'COMISION EVELYN', 'orden' => 170],
        ['nombre' => 'COMISION ANDRES', 'orden' => 180],
        ['nombre' => 'COMISION 3', 'orden' => 190],
        ['nombre' => 'COMISION 4', 'orden' => 200],
        ['nombre' => 'COMISION 5', 'orden' => 210],
        ['nombre' => 'PUBLICIDAD', 'orden' => 220],
        ['nombre' => 'MUEBLES Y ENSERES', 'orden' => 230],
        ['nombre' => 'ELECTRODOMESTICOS', 'orden' => 240],
        ['nombre' => 'CUOTA EXTRA EDIFICIO', 'orden' => 250],
        ['nombre' => 'TAXI PALMIRA', 'orden' => 260],
        ['nombre' => 'COMPRAS MINIMARKET', 'orden' => 270],
        ['nombre' => 'TAXI CARTAGENA', 'orden' => 280],
        ['nombre' => 'SOFTWARE', 'orden' => 290],
        ['nombre' => 'OTRA SALIDA', 'orden' => 999]
    ];

    $stmt = $db->prepare("
        INSERT INTO contabilidad_categorias (tipo, nombre, orden, activo) 
        VALUES (?, ?, ?, 1)
        ON DUPLICATE KEY UPDATE orden = VALUES(orden), activo = VALUES(activo)
    ");

    foreach ($entradas as $cat) {
        $stmt->execute(['entrada', $cat['nombre'], $cat['orden']]);
    }

    foreach ($salidas as $cat) {
        $stmt->execute(['salida', $cat['nombre'], $cat['orden']]);
    }

    echo "✓ Categorías precargadas exitosamente.\n";
    echo "¡Migración de contabilidad finalizada con éxito!\n";

} catch (Exception $e) {
    echo "ERROR en la migración: " . $e->getMessage() . "\n";
    exit(1);
}

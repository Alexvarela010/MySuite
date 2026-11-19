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

    // Verificar si las columnas 'caja', 'numero_noches' y 'cantidad_personas' ya existen
    try {
        $chkCol = $db->query("SHOW COLUMNS FROM contabilidad_movimientos LIKE 'caja'")->fetch();
        if (!$chkCol) {
            $db->exec("ALTER TABLE contabilidad_movimientos ADD COLUMN caja VARCHAR(50) NOT NULL DEFAULT 'Evelyn' AFTER monto, ADD INDEX idx_caja (caja)");
            echo "✓ Columna 'caja' añadida a contabilidad_movimientos.\n";
        }
        $chkNoches = $db->query("SHOW COLUMNS FROM contabilidad_movimientos LIKE 'numero_noches'")->fetch();
        if (!$chkNoches) {
            $db->exec("ALTER TABLE contabilidad_movimientos ADD COLUMN numero_noches INT NULL AFTER id_reserva");
            echo "✓ Columna 'numero_noches' añadida a contabilidad_movimientos.\n";
        }
        $chkPersonas = $db->query("SHOW COLUMNS FROM contabilidad_movimientos LIKE 'cantidad_personas'")->fetch();
        if (!$chkPersonas) {
            $db->exec("ALTER TABLE contabilidad_movimientos ADD COLUMN cantidad_personas INT NULL AFTER numero_noches");
            echo "✓ Columna 'cantidad_personas' añadida a contabilidad_movimientos.\n";
        }
    } catch (\Throwable $eCol) {
        // Ignorar
    }
    echo "✓ Tabla contabilidad_movimientos lista.\n";

    echo "3. Insertando categorías iniciales desde Excel...\n";
    
    $entradas = [
        ['nombre' => 'WEB RESERVAS 20%', 'orden' => 10],
        ['nombre' => 'WEB RESERVAS 80%', 'orden' => 20],
        ['nombre' => 'WEB RESERVAS TOTAL', 'orden' => 30],
        ['nombre' => 'RESERVA TOTAL BOOKING', 'orden' => 40],
        ['nombre' => 'RESERVA TOTAL AIRBNB', 'orden' => 50],
        ['nombre' => 'RESERVA TOTAL FOREVER SUMMER', 'orden' => 60],
        ['nombre' => 'COMISION TRANSPORTE', 'orden' => 70],
        ['nombre' => 'COMISION TURISMO', 'orden' => 80],
        ['nombre' => 'VENTAS MINIMARKET', 'orden' => 90],
        ['nombre' => 'OTRA ENTRADA', 'orden' => 100]
    ];

    $salidas = [
        ['nombre' => 'ASEO APARTAMENTO', 'orden' => 10],
        ['nombre' => 'ASEO EXTRA', 'orden' => 20],
        ['nombre' => 'ADMINISTRACION', 'orden' => 30],
        ['nombre' => 'AGUA', 'orden' => 40],
        ['nombre' => 'CLARO', 'orden' => 50],
        ['nombre' => 'COMISION ANDRES', 'orden' => 60],
        ['nombre' => 'COMISION BOOKING', 'orden' => 70],
        ['nombre' => 'COMISION EVELYN', 'orden' => 80],
        ['nombre' => 'ENERGIA', 'orden' => 90],
        ['nombre' => 'GAS', 'orden' => 100],
        ['nombre' => 'PRODUCTOS ASEO', 'orden' => 110],
        ['nombre' => 'FONTUR', 'orden' => 120],
        ['nombre' => 'MANTENIMIENTO AIRE ACONDICIONADO', 'orden' => 130],
        ['nombre' => 'OTRA SALIDA', 'orden' => 140],
        ['nombre' => 'PUBLICIDAD', 'orden' => 150],
        ['nombre' => 'ALIMENTOS', 'orden' => 160],
        ['nombre' => 'COMISION 3', 'orden' => 170],
        ['nombre' => 'COMISION 4', 'orden' => 180],
        ['nombre' => 'COMPRA EQUIPOS', 'orden' => 190],
        ['nombre' => 'COMPRAS MINIMARKET', 'orden' => 200],
        ['nombre' => 'ELECTRODOMESTICOS', 'orden' => 210],
        ['nombre' => 'LENCERIA', 'orden' => 220],
        ['nombre' => 'MANTENIMIENTO CLOSETS Y PUERTAS', 'orden' => 230],
        ['nombre' => 'MUEBLES Y ENSERES', 'orden' => 240],
        ['nombre' => 'PINTURA', 'orden' => 250],
        ['nombre' => 'PREDIAL', 'orden' => 260],
        ['nombre' => 'SEGUROS DE APARTAMENTO', 'orden' => 270],
        ['nombre' => 'SOFTWARE', 'orden' => 280],
        ['nombre' => 'TAXI CARTAGENA', 'orden' => 290],
        ['nombre' => 'TAXI PALMIRA', 'orden' => 300]
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

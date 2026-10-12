<?php
/**
 * Migración RBAC - My Suite In Cartagena
 * Crea las tablas de roles, modulos y rol_permisos, y actualiza usuarios.rol
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos.");
    }
    
    echo "1. Actualizando enum en columna usuarios.rol...\n";
    // Modificar ENUM para incluir 'operador'
    $db->exec("ALTER TABLE usuarios MODIFY COLUMN rol ENUM('admin','operador','cliente') DEFAULT 'cliente'");
    echo "✓ Columna usuarios.rol actualizada.\n";

    echo "2. Creando tabla roles...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS roles (
            id_rol INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(50) NOT NULL UNIQUE,
            nombre VARCHAR(100) NOT NULL,
            descripcion VARCHAR(255),
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Tabla roles lista.\n";

    echo "3. Creando tabla modulos...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS modulos (
            id_modulo INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(50) NOT NULL UNIQUE,
            nombre VARCHAR(100) NOT NULL,
            descripcion VARCHAR(255),
            icono VARCHAR(50) NOT NULL,
            ruta VARCHAR(255) NOT NULL,
            orden INT DEFAULT 0,
            activo TINYINT(1) DEFAULT 1,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Tabla modulos lista.\n";

    echo "4. Creando tabla rol_permisos...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS rol_permisos (
            id_permiso INT AUTO_INCREMENT PRIMARY KEY,
            rol_codigo VARCHAR(50) NOT NULL,
            modulo_codigo VARCHAR(50) NOT NULL,
            permitido TINYINT(1) DEFAULT 0,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_rol_modulo (rol_codigo, modulo_codigo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Tabla rol_permisos lista.\n";

    echo "5. Insertando roles base...\n";
    $stmtRole = $db->prepare("INSERT INTO roles (codigo, nombre, descripcion) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), descripcion = VALUES(descripcion)");
    $stmtRole->execute(['admin', 'Administrador General', 'Acceso total y configuración del sistema']);
    $stmtRole->execute(['operador', 'Operador de Suite', 'Gestión operativa de reservas, calendario y contabilidad']);
    echo "✓ Roles base insertados.\n";

    echo "6. Insertando módulos del sistema...\n";
    $modulos = [
        ['dashboard', 'Dashboard', 'Panel de métricas y estadísticas', 'fas fa-tachometer-alt', '/app/views/admin/index.php', 1],
        ['reservas', 'Reservas', 'Gestión operativa de reservas', 'fas fa-book', '/app/views/admin/reservas.php', 2],
        ['calendario', 'Calendario', 'Disponibilidad y bloqueos', 'fas fa-calendar-alt', '/app/views/admin/calendario.php', 3],
        ['usuarios', 'Usuarios Registrados', 'Listado y búsqueda de clientes', 'fas fa-users', '/app/views/admin/usuarios.php', 4],
        ['tarifas', 'Tarifas', 'Precios por día y carga Excel', 'fas fa-dollar-sign', '/app/views/admin/tarifas.php', 5],
        ['descuentos', 'Descuentos', 'Configuración de descuentos', 'fas fa-percentage', '/app/views/admin/descuentos.php', 6],
        ['manillas', 'Tarifas Manillas', 'Cobro de manillas del edificio', 'fas fa-id-badge', '/admin/manillas.php', 7],
        ['servicios', 'Servicios Adicionales', 'Early check-in y Late checkout', 'fas fa-concierge-bell', '/admin/servicios_adicionales.php', 8],
        ['contabilidad', 'Contabilidad', 'Control financiero y saldos', 'fas fa-file-invoice-dollar', '/app/views/admin/contabilidad.php', 9],
        ['permisos', 'Permisos de Acceso', 'Control RBAC de roles y módulos', 'fas fa-user-shield', '/app/views/admin/permisos.php', 10],
    ];

    $stmtMod = $db->prepare("
        INSERT INTO modulos (codigo, nombre, descripcion, icono, ruta, orden, activo) 
        VALUES (?, ?, ?, ?, ?, ?, 1)
        ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), descripcion = VALUES(descripcion), icono = VALUES(icono), ruta = VALUES(ruta), orden = VALUES(orden)
    ");
    foreach ($modulos as $mod) {
        $stmtMod->execute($mod);
    }
    echo "✓ Módulos insertados.\n";

    echo "7. Configurando permisos iniciales para admin y operador...\n";
    $permisos = [
        // Admin: Todo permitido
        ['admin', 'dashboard', 1],
        ['admin', 'reservas', 1],
        ['admin', 'calendario', 1],
        ['admin', 'usuarios', 1],
        ['admin', 'tarifas', 1],
        ['admin', 'descuentos', 1],
        ['admin', 'manillas', 1],
        ['admin', 'servicios', 1],
        ['admin', 'contabilidad', 1],
        ['admin', 'permisos', 1],
        
        // Operador: Según especificación
        ['operador', 'dashboard', 0],   // PRIVADO
        ['operador', 'reservas', 1],    // LIBRE
        ['operador', 'calendario', 1],  // LIBRE
        ['operador', 'usuarios', 0],    // PRIVADO
        ['operador', 'tarifas', 0],     // PRIVADO
        ['operador', 'descuentos', 0],  // PRIVADO
        ['operador', 'manillas', 0],    // PRIVADO
        ['operador', 'servicios', 0],   // PRIVADO
        ['operador', 'contabilidad', 1],// LIBRE
        ['operador', 'permisos', 0],    // PRIVADO
    ];

    $stmtPerm = $db->prepare("
        INSERT INTO rol_permisos (rol_codigo, modulo_codigo, permitido)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE permitido = VALUES(permitido)
    ");
    foreach ($permisos as $p) {
        $stmtPerm->execute($p);
    }
    echo "✓ Permisos configurados con éxito.\n";

    echo "\n=== MIGRACIÓN RBAC COMPLETADA SATISFACTORIAMENTE ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

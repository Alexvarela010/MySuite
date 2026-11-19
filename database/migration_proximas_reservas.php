<?php
/**
 * Migración de Próximas Reservas - My Suite In Cartagena
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/proximas_reservas_helper.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos.");
    }

    ProximasReservasHelper::ensureTablesExist($db);
    echo "✓ Tabla proximas_reservas y módulo RBAC registrados exitosamente.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

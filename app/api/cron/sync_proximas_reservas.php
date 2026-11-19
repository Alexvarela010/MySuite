<?php
/**
 * Cron Job: Sincronizar automáticamente las próximas reservas de la página web
 * hacia el módulo de Próximas Reservas.
 * My Suite In Cartagena
 */

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/proximas_reservas_helper.php';

// Configuración de log
$logDir = __DIR__ . '/../../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
ini_set('log_errors', 'On');
ini_set('error_log', $logDir . '/cron_proximas_reservas.log');

$timestamp = date('Y-m-d H:i:s');
echo "[{$timestamp}] Iniciando sincronización de Próximas Reservas...\n";

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos.");
    }

    $stats = ProximasReservasHelper::syncFromWebReservas($db);

    $msg = sprintf(
        "[%s] Sincronización completada -> Insertadas: %d | Actualizadas: %d | Completadas: %d | Canceladas: %d",
        date('Y-m-d H:i:s'),
        $stats['insertadas'],
        $stats['actualizadas'],
        $stats['completadas'],
        $stats['canceladas']
    );
    echo $msg . "\n";
    error_log($msg);

} catch (\Throwable $e) {
    $err = "[" . date('Y-m-d H:i:s') . "] ERROR en cron sync_proximas_reservas: " . $e->getMessage();
    echo $err . "\n";
    error_log($err);
    exit(1);
}

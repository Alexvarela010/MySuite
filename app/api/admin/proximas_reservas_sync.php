<?php
/**
 * API: Sincronizar manualmente Próximas Reservas desde las reservas de la página web
 * My Suite In Cartagena
 */

require_once __DIR__ . '/../../../includes/auth_helper.php';
AuthHelper::requireModule('proximas_reservas');

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/proximas_reservas_helper.php';

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
ob_start();

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido.");
    }

    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("Error de conexión a la base de datos.");
    }

    $stats = ProximasReservasHelper::syncFromWebReservas($db);

    if (ob_get_length()) {
        ob_clean();
    }

    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'message' => sprintf(
            'Sincronización lista: %d nuevas, %d actualizadas, %d completadas, %d canceladas.',
            $stats['insertadas'],
            $stats['actualizadas'],
            $stats['completadas'],
            $stats['canceladas']
        )
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al sincronizar: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

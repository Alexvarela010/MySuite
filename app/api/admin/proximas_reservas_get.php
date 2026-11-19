<?php
/**
 * API: Obtener listado y resumen de Próximas Reservas
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
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Error de conexión a la base de datos.");
    }

    $filters = [
        'grupo' => isset($_GET['grupo']) && $_GET['grupo'] !== '' ? trim($_GET['grupo']) : null,
        'estado' => isset($_GET['estado']) && $_GET['estado'] !== '' ? trim($_GET['estado']) : null,
        'mes' => isset($_GET['mes']) && $_GET['mes'] !== '' ? (int)$_GET['mes'] : null,
        'anio' => isset($_GET['anio']) && $_GET['anio'] !== '' ? (int)$_GET['anio'] : null,
        'search' => isset($_GET['search']) && $_GET['search'] !== '' ? trim($_GET['search']) : null,
    ];

    $reservas = ProximasReservasHelper::getReservas($db, $filters);
    $resumen = ProximasReservasHelper::getResumen($db, $filters);

    if (ob_get_length()) {
        ob_clean();
    }

    echo json_encode([
        'success' => true,
        'reservas' => $reservas,
        'resumen' => $resumen
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

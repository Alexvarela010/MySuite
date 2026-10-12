<?php
/**
 * API: Guardar (Crear / Actualizar) movimiento contable
 * My Suite In Cartagena
 */

require_once __DIR__ . '/../../../includes/auth_helper.php';
AuthHelper::requireModule('contabilidad');

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/contabilidad_helper.php';

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
ob_start();

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido.");
    }

    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Error de conexión a la base de datos.");
    }

    // Aceptar tanto JSON como form-data
    $rawInput = file_get_contents('php://input');
    $jsonData = json_decode($rawInput, true);
    $data = is_array($jsonData) ? $jsonData : $_POST;

    // Determinar usuario autenticado que registra
    $currentUser = $_SESSION['user_nombre'] ?? $_SESSION['user_correo'] ?? AuthHelper::getCurrentRole();

    $id = ContabilidadHelper::saveMovimiento($db, $data, $currentUser);

    if (ob_get_length()) {
        ob_clean();
    }

    echo json_encode([
        'success' => true,
        'message' => !empty($data['id_movimiento']) ? 'Movimiento actualizado exitosamente.' : 'Movimiento registrado exitosamente.',
        'id_movimiento' => $id
    ], JSON_UNESCAPED_UNICODE);

} catch (\InvalidArgumentException $e) {
    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

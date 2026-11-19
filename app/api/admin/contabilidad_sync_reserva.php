<?php
/**
 * API Asíncrona: Sincronizar automáticamente un abono (20%) o pago (80%) de reserva web en Contabilidad
 * My Suite In Cartagena
 */

require_once __DIR__ . '/../../../includes/auth_helper.php';
AuthHelper::requireModule('reservas');

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

    $rawInput = file_get_contents('php://input');
    $jsonData = json_decode($rawInput, true);
    $data = is_array($jsonData) ? $jsonData : $_POST;

    $idReserva = isset($data['id_reserva']) ? (int)$data['id_reserva'] : 0;
    $etapa = trim((string)($data['etapa'] ?? ''));

    if ($idReserva <= 0 || !in_array($etapa, ['abonada', 'pagada', 'cancelada'])) {
        throw new InvalidArgumentException("Parámetros inválidos para sincronización contable.");
    }

    $currentUser = $_SESSION['user_nombre'] ?? $_SESSION['user_correo'] ?? AuthHelper::getCurrentRole();

    if ($etapa === 'cancelada') {
        $deleted = ContabilidadHelper::eliminarMovimientosReserva($db, $idReserva);
        $result = [
            'success' => true,
            'etapa' => 'cancelada',
            'deleted_count' => $deleted
        ];
    } else {
        $result = ContabilidadHelper::registrarPagoReserva($db, $idReserva, $etapa, [
            'was_abonada' => !empty($data['was_abonada']),
            'notas' => $data['notas'] ?? '',
            'caja' => $data['caja'] ?? 'Carlos',
            'metodo_pago' => $data['metodo_pago'] ?? 'auto'
        ], $currentUser);
    }

    if (ob_get_length()) {
        ob_clean();
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (\InvalidArgumentException $e) {
    if (ob_get_length()) ob_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

<?php
/**
 * Actualizar datos de una reserva (nombre, correo, teléfono, total)
 * Panel de Administración - My Suite In Cartagena
 */

session_start();

// Verificar si el usuario está logueado como admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Obtener datos del POST
    $id_reserva = $_POST['id_reserva'] ?? 0;
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $total = $_POST['total'] ?? 0;

    // Validaciones
    if (!$id_reserva || !is_numeric($id_reserva)) {
        throw new Exception('ID de reserva inválido');
    }

    if (empty($nombre) || empty($apellido)) {
        throw new Exception('El nombre y apellido son obligatorios');
    }

    if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El correo electrónico es obligatorio y debe ser válido');
    }

    if (empty($telefono)) {
        throw new Exception('El teléfono es obligatorio');
    }

    $total = floatval($total);
    if ($total < 0) {
        throw new Exception('El total debe ser mayor o igual a 0');
    }

    // Conectar a la base de datos
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Error de conexión a la base de datos");
    }

    // Verificar que la reserva existe
    $checkQuery = "SELECT id_reserva FROM reservas WHERE id_reserva = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$id_reserva]);
    
    if (!$checkStmt->fetch()) {
        throw new Exception('Reserva no encontrada');
    }

    // Actualizar la reserva
    $updateQuery = "
        UPDATE reservas 
        SET 
            nombre = :nombre,
            apellido = :apellido,
            correo = :correo,
            telefono = :telefono,
            total = :total,
            actualizado_en = NOW()
        WHERE id_reserva = :id_reserva
    ";

    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bindParam(':nombre', $nombre);
    $updateStmt->bindParam(':apellido', $apellido);
    $updateStmt->bindParam(':correo', $correo);
    $updateStmt->bindParam(':telefono', $telefono);
    $updateStmt->bindParam(':total', $total);
    $updateStmt->bindParam(':id_reserva', $id_reserva);
    
    $updateStmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Datos de la reserva actualizados exitosamente'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

<?php
/**
 * API: Eliminar tarifa de manillas
 * POST /app/api/admin/delete_manillas_tarifa.php
 */

header('Content-Type: application/json');

// Verificar sesión
session_start();
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['user_rol'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Error de conexión a la base de datos");
    }
    
    // Obtener datos del POST
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    if (!isset($data['id_tarifa'])) {
        throw new Exception("ID de tarifa requerido");
    }
    
    $id_tarifa = (int)$data['id_tarifa'];
    
    // Eliminar tarifa
    $query = "DELETE FROM manillas_tarifas WHERE id_tarifa = :id_tarifa";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_tarifa', $id_tarifa, PDO::PARAM_INT);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Tarifa eliminada exitosamente'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    error_log("Error en delete_manillas_tarifa.php: " . $e->getMessage());
}
?>


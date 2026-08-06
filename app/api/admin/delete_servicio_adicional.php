<?php
/**
 * API: Eliminar Servicio Adicional
 * My Suite In Cartagena
 */

session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id_servicio = $data['id_servicio'] ?? null;
    
    if (!$id_servicio) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit;
    }
    
    try {
        $db = (new Database())->getConnection();
        
        $query = "DELETE FROM servicios_adicionales WHERE id_servicio = ?";
        $stmt = $db->prepare($query);
        $result = $stmt->execute([$id_servicio]);
        
        if ($result && $stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Servicio eliminado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Servicio no encontrado']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>


<?php
/**
 * API: Guardar/Actualizar Servicio Adicional
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
    $nombre = $data['nombre'] ?? '';
    $tipo = $data['tipo'] ?? '';
    $precio = $data['precio'] ?? 0;
    $descripcion = $data['descripcion'] ?? '';
    $activo = $data['activo'] ?? 1;
    
    if (empty($nombre) || empty($tipo) || $precio <= 0) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }
    
    try {
        $db = (new Database())->getConnection();
        
        if ($id_servicio) {
            // Actualizar
            $query = "UPDATE servicios_adicionales 
                      SET nombre = ?, tipo = ?, precio = ?, descripcion = ?, activo = ?, actualizado_en = NOW()
                      WHERE id_servicio = ?";
            $stmt = $db->prepare($query);
            $result = $stmt->execute([$nombre, $tipo, $precio, $descripcion, $activo, $id_servicio]);
            $message = 'Servicio actualizado exitosamente';
        } else {
            // Insertar
            $query = "INSERT INTO servicios_adicionales (nombre, tipo, precio, descripcion, activo)
                      VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $result = $stmt->execute([$nombre, $tipo, $precio, $descripcion, $activo]);
            $message = 'Servicio creado exitosamente';
        }
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el servicio']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>


<?php
/**
 * API: Obtener Servicios Adicionales
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

try {
    $db = (new Database())->getConnection();
    
    $query = "SELECT * FROM servicios_adicionales ORDER BY tipo, creado_en DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'servicios' => $servicios
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener servicios: ' . $e->getMessage()
    ]);
}
?>


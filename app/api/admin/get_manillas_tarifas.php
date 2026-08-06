<?php
/**
 * API: Obtener tarifas de manillas
 * GET /app/api/admin/get_manillas_tarifas.php
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
    
    // Obtener todas las tarifas de manillas
    $query = "
        SELECT 
            id_tarifa,
            personas_desde,
            personas_hasta,
            precio,
            activo,
            creado_en,
            actualizado_en
        FROM manillas_tarifas
        ORDER BY personas_desde ASC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $tarifas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'tarifas' => $tarifas
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    error_log("Error en get_manillas_tarifas.php: " . $e->getMessage());
}
?>


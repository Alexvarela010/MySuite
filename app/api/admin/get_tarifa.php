<?php
/**
 * Obtener tarifa de una fecha específica
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Error de conexión a la base de datos");
    }
    
    // Obtener parámetros
    $fecha = $_GET['fecha'] ?? '';
    $apartamento_id = $_GET['apartamento_id'] ?? 1;
    
    if (empty($fecha)) {
        throw new Exception("Fecha no proporcionada");
    }
    
    // Validar fecha
    $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fecha_obj) {
        throw new Exception("Fecha inválida");
    }
    
    // Obtener tarifa
    $query = "
        SELECT precio, temporada 
        FROM tarifas 
        WHERE id_apartamento = :apartamento_id 
        AND fecha = :fecha
    ";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':apartamento_id', $apartamento_id);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->execute();
    
    $tarifa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($tarifa) {
        echo json_encode([
            'success' => true,
            'precio' => (float)$tarifa['precio'],
            'temporada' => $tarifa['temporada']
        ]);
    } else {
        // Retornar precio base si no existe
        echo json_encode([
            'success' => true,
            'precio' => 200000,
            'temporada' => 'baja'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>


<?php
/**
 * Actualizar o crear tarifa por fecha
 * Sistema de Reservas - My Suite In Cartagena
 */

session_start();

// Verificar si el usuario está logueado como admin
if (!((isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) ||
    (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && 
     isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin'))) {
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

$fecha = $_POST['fecha'] ?? '';
$precio = $_POST['precio'] ?? '';
$temporada = 'baja'; // Temporada por defecto, ya no se recibe del frontend
$apartamento_id = $_POST['apartamento_id'] ?? 1;

// Validaciones
if (empty($fecha) || empty($precio)) {
    echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
    exit;
}

// Validar fecha
$fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha);
if (!$fecha_obj) {
    echo json_encode(['success' => false, 'message' => 'Fecha inválida']);
    exit;
}

// Validar precio
$precio = floatval($precio);
if ($precio < 0) {
    echo json_encode(['success' => false, 'message' => 'El precio debe ser mayor o igual a 0']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Error de conexión a la base de datos");
    }
    
    // Verificar si ya existe una tarifa para esta fecha
    $checkQuery = "
        SELECT id_tarifa 
        FROM tarifas 
        WHERE id_apartamento = :apartamento_id 
        AND fecha = :fecha
    ";
    
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':apartamento_id', $apartamento_id);
    $checkStmt->bindParam(':fecha', $fecha);
    $checkStmt->execute();
    
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Actualizar tarifa existente
        $updateQuery = "
            UPDATE tarifas 
            SET precio = :precio, temporada = :temporada 
            WHERE id_tarifa = :id_tarifa
        ";
        
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(':precio', $precio);
        $updateStmt->bindParam(':temporada', $temporada);
        $updateStmt->bindParam(':id_tarifa', $existing['id_tarifa']);
        $updateStmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Tarifa actualizada exitosamente',
            'action' => 'updated'
        ]);
    } else {
        // Crear nueva tarifa
        $insertQuery = "
            INSERT INTO tarifas (id_apartamento, fecha, precio, temporada) 
            VALUES (:apartamento_id, :fecha, :precio, :temporada)
        ";
        
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bindParam(':apartamento_id', $apartamento_id);
        $insertStmt->bindParam(':fecha', $fecha);
        $insertStmt->bindParam(':precio', $precio);
        $insertStmt->bindParam(':temporada', $temporada);
        $insertStmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Tarifa creada exitosamente',
            'action' => 'created'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}
?>


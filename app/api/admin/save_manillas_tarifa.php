<?php
/**
 * API: Guardar/Actualizar tarifas de manillas
 * POST /app/api/admin/save_manillas_tarifa.php
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
        // Si no es JSON, intentar con $_POST
        $data = $_POST;
    }
    
    // Validar datos requeridos
    if (!isset($data['personas_desde']) || !isset($data['precio'])) {
        throw new Exception("Datos incompletos. Se requieren: personas_desde, precio");
    }
    
    $id_tarifa = isset($data['id_tarifa']) ? (int)$data['id_tarifa'] : 0;
    $personas_desde = (int)$data['personas_desde'];
    $personas_hasta = isset($data['personas_hasta']) && $data['personas_hasta'] !== '' 
        ? (int)$data['personas_hasta'] 
        : null;
    $precio = (float)$data['precio'];
    $activo = isset($data['activo']) ? (int)$data['activo'] : 1;
    
    // Validaciones
    if ($personas_desde < 1) {
        throw new Exception("La cantidad de personas debe ser mayor a 0");
    }
    
    if ($precio < 0) {
        throw new Exception("El precio no puede ser negativo");
    }
    
    if ($personas_hasta !== null && $personas_hasta < $personas_desde) {
        throw new Exception("La cantidad máxima no puede ser menor a la mínima");
    }
    
    if ($id_tarifa > 0) {
        // Actualizar tarifa existente
        $query = "
            UPDATE manillas_tarifas 
            SET 
                personas_desde = :personas_desde,
                personas_hasta = :personas_hasta,
                precio = :precio,
                activo = :activo,
                actualizado_en = CURRENT_TIMESTAMP
            WHERE id_tarifa = :id_tarifa
        ";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_tarifa', $id_tarifa, PDO::PARAM_INT);
        $stmt->bindParam(':personas_desde', $personas_desde, PDO::PARAM_INT);
        $stmt->bindParam(':personas_hasta', $personas_hasta, PDO::PARAM_INT);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
        $stmt->execute();
        
        $mensaje = "Tarifa actualizada exitosamente";
    } else {
        // Crear nueva tarifa
        $query = "
            INSERT INTO manillas_tarifas 
            (personas_desde, personas_hasta, precio, activo)
            VALUES 
            (:personas_desde, :personas_hasta, :precio, :activo)
        ";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':personas_desde', $personas_desde, PDO::PARAM_INT);
        $stmt->bindParam(':personas_hasta', $personas_hasta, PDO::PARAM_INT);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
        $stmt->execute();
        
        $id_tarifa = $db->lastInsertId();
        $mensaje = "Tarifa creada exitosamente";
    }
    
    echo json_encode([
        'success' => true,
        'message' => $mensaje,
        'id_tarifa' => $id_tarifa
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    error_log("Error en save_manillas_tarifa.php: " . $e->getMessage());
}
?>


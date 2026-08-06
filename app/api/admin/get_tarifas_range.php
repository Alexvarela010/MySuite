<?php
/**
 * Obtener tarifas por rango de fechas
 * Para el calendario del cliente
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
    $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
    $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d', strtotime('+30 days'));
    $apartamento_id = $_GET['apartamento_id'] ?? 1;
    
    // Validar fechas
    $fecha_inicio_obj = new DateTime($fecha_inicio);
    $fecha_fin_obj = new DateTime($fecha_fin);
    
    if ($fecha_inicio_obj > $fecha_fin_obj) {
        throw new Exception("La fecha de inicio debe ser anterior a la fecha de fin");
    }
    
    // Obtener tarifas del rango
    $query = "
        SELECT fecha, precio, temporada 
        FROM tarifas 
        WHERE id_apartamento = :apartamento_id 
        AND fecha BETWEEN :fecha_inicio AND :fecha_fin
        ORDER BY fecha ASC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':apartamento_id', $apartamento_id);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_fin', $fecha_fin);
    $stmt->execute();
    
    $tarifas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convertir a formato objeto para fácil acceso por fecha
    $tarifas_por_fecha = [];
    foreach ($tarifas as $tarifa) {
        $tarifas_por_fecha[$tarifa['fecha']] = [
            'precio' => (float)$tarifa['precio'],
            'temporada' => $tarifa['temporada']
        ];
    }
    
    // Precio base por defecto
    $precio_base = 200000;
    
    // Generar array completo de fechas con precios
    $fecha_actual = clone $fecha_inicio_obj;
    $resultado = [];
    
    while ($fecha_actual <= $fecha_fin_obj) {
        $fecha_str = $fecha_actual->format('Y-m-d');
        
        // Verificar si existe tarifa específica (incluso si el precio es 0)
        if (isset($tarifas_por_fecha[$fecha_str])) {
            // Guardar el precio (puede ser 0, que es válido)
            $precio = (float)$tarifas_por_fecha[$fecha_str]['precio'];
            $resultado[$fecha_str] = $precio;
        } else {
            // Si no existe tarifa específica, usar precio base
            $resultado[$fecha_str] = $precio_base;
        }
        
        $fecha_actual->add(new DateInterval('P1D'));
    }
    
    echo json_encode([
        'success' => true,
        'tarifas' => $resultado,
        'precio_base' => $precio_base
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>


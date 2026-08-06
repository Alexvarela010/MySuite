<?php
/**
 * Obtener reservas del usuario logueado
 * Sistema de Reservas - My Suite In Cartagena
 */

// Iniciar sesi車n si no est芍 iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario est芍 logueado
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Error de conexi車n a la base de datos");
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Obtener reservas del usuario (por id_usuario o por correo si id_usuario es NULL)
    $user_email = $_SESSION['user_correo'] ?? '';
    
    $query = "
        SELECT 
            r.id_reserva,
            r.fecha_entrada,
            r.fecha_salida,
            r.num_adultos as numero_adultos,
            r.num_ninos as numero_ninos,
            r.total,
            r.estado,
            r.estado_pago,
            r.metodo_pago,
            r.creado_en,
            a.nombre as apartamento_nombre
        FROM reservas r
        LEFT JOIN apartamentos a ON r.id_apartamento = a.id_apartamento
        WHERE (r.id_usuario = ? OR (r.id_usuario IS NULL AND r.correo = ?))
        ORDER BY r.creado_en DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id, $user_email]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear fechas y calcular noches
    foreach ($reservations as &$reservation) {
        // Crear objetos DateTime sin zona horaria para evitar problemas de conversi車n
        $fecha_entrada = DateTime::createFromFormat('Y-m-d', $reservation['fecha_entrada']);
        $fecha_salida = DateTime::createFromFormat('Y-m-d', $reservation['fecha_salida']);
        
        // Si createFromFormat falla, usar el constructor normal
        if (!$fecha_entrada) {
            $fecha_entrada = new DateTime($reservation['fecha_entrada']);
        }
        if (!$fecha_salida) {
            $fecha_salida = new DateTime($reservation['fecha_salida']);
        }
        
        // Calcular noches correctamente (diferencia en d赤as)
        $reservation['noches'] = $fecha_entrada->diff($fecha_salida)->days;
        
        // Formatear fechas sin conversi車n de zona horaria
        $reservation['fecha_entrada_formatted'] = $fecha_entrada->format('d/m/Y');
        $reservation['fecha_salida_formatted'] = $fecha_salida->format('d/m/Y');
        $reservation['creado_en_formatted'] = date('d/m/Y H:i', strtotime($reservation['creado_en']));
    }
    
    echo json_encode([
        'success' => true,
        'reservations' => $reservations
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>


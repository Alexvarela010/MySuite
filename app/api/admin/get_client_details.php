<?php
/**
 * Obtener detalles completos de un cliente específico
 */

session_start();

// Verificar si el usuario está logueado como admin
if (!((isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) ||
    (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && 
     isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin'))) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

$clientId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($clientId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de cliente inválido']);
    exit;
}

$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

try {
    // Obtener información del cliente
    $clientSql = "SELECT 
                    u.id_usuario,
                    u.nombre,
                    u.apellido,
                    u.correo,
                    u.telefono,
                    u.fecha_nacimiento,
                    u.creado_en as fecha_registro,
                    u.ciudad,
                    u.rol,
                    COUNT(r.id_reserva) as total_reservas,
                    SUM(CASE WHEN r.estado = 'aprobada' THEN 1 ELSE 0 END) as reservas_aprobadas,
                    SUM(CASE WHEN r.estado = 'pendiente' THEN 1 ELSE 0 END) as reservas_pendientes
                FROM usuarios u
                LEFT JOIN reservas r ON u.id_usuario = r.id_usuario
                WHERE u.id_usuario = :client_id
                GROUP BY u.id_usuario";
    
    $clientStmt = $pdo->prepare($clientSql);
    $clientStmt->execute(['client_id' => $clientId]);
    $client = $clientStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$client) {
        echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
        exit;
    }
    
    // Obtener historial de reservas del cliente
    $reservationsSql = "SELECT 
                            id_reserva,
                            fecha_entrada,
                            fecha_salida,
                            estado,
                            total,
                            creado_en as fecha_creacion
                        FROM reservas 
                        WHERE id_usuario = :client_id 
                        ORDER BY creado_en DESC 
                        LIMIT 10";
    
    $reservationsStmt = $pdo->prepare($reservationsSql);
    $reservationsStmt->execute(['client_id' => $clientId]);
    $reservations = $reservationsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'client' => $client,
        'reservations' => $reservations
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}
?>

<?php
/**
 * Obtener clientes que viven en Palmira
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

$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

try {
    // Buscar clientes que viven en Palmira
    $sql = "SELECT 
                u.id_usuario,
                u.nombre,
                u.apellido,
                u.correo,
                u.telefono,
                u.creado_en as fecha_registro,
                COUNT(r.id_reserva) as total_reservas
            FROM usuarios u
            LEFT JOIN reservas r ON u.id_usuario = r.id_usuario
            WHERE u.ciudad = 'Palmira' OR u.ciudad LIKE '%Palmira%'
            GROUP BY u.id_usuario
            ORDER BY u.creado_en DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'clients' => $clients
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}
?>

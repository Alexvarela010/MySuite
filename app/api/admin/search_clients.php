<?php
/**
 * Buscar clientes por nombre o email
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

$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($searchTerm)) {
    echo json_encode(['success' => false, 'message' => 'Término de búsqueda vacío']);
    exit;
}

try {
    // Buscar clientes por nombre, apellido o email
    $sql = "SELECT 
                u.id_usuario,
                u.nombre,
                u.apellido,
                u.correo,
                u.telefono,
                u.fecha_nacimiento,
                u.creado_en as fecha_registro,
                u.ciudad,
                COUNT(r.id_reserva) as total_reservas,
                SUM(CASE WHEN r.estado = 'confirmada' THEN 1 ELSE 0 END) as reservas_confirmadas,
                SUM(CASE WHEN r.estado = 'pendiente' THEN 1 ELSE 0 END) as reservas_pendientes
            FROM usuarios u
            LEFT JOIN reservas r ON u.id_usuario = r.id_usuario
            WHERE u.rol = 'cliente' 
            AND (u.nombre LIKE :search 
                 OR u.apellido LIKE :search 
                 OR u.correo LIKE :search
                 OR CONCAT(u.nombre, ' ', u.apellido) LIKE :search)
            GROUP BY u.id_usuario
            ORDER BY u.creado_en DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['search' => '%' . $searchTerm . '%']);
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

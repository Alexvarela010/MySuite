<?php
/**
 * Desbloquear fechas manualmente
 * Sistema de Reservas - My Suite In Cartagena
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

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$date = $_POST['date'] ?? '';

// Validaciones básicas
if (empty($date)) {
    echo json_encode(['success' => false, 'message' => 'Fecha requerida']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Error de conexión a la base de datos");
    }
    
    // Buscar bloqueo que contenga esta fecha
    $checkSql = "SELECT id_bloqueo FROM fechas_bloqueadas 
                 WHERE fecha_inicio <= :date AND fecha_fin >= :date
                 AND activo = 1
                 LIMIT 1";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute(['date' => $date]);
    $bloqueo = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$bloqueo) {
        echo json_encode(['success' => false, 'message' => 'No se encontró un bloqueo activo para esta fecha']);
        exit;
    }
    
    // Desactivar el bloqueo (marcar como inactivo)
    $updateSql = "UPDATE fechas_bloqueadas SET activo = 0 WHERE id_bloqueo = :id_bloqueo";
    $updateStmt = $db->prepare($updateSql);
    $result = $updateStmt->execute(['id_bloqueo' => $bloqueo['id_bloqueo']]);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Fecha desbloqueada exitosamente: ' . date('d/m/Y', strtotime($date)),
            'date' => $date
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al desbloquear la fecha']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>


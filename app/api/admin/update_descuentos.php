<?php
session_start();
header('Content-Type: application/json');

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
    // Actualizar descuento de fidelidad
    if (isset($_POST['fidelidad_porcentaje'])) {
        $porcentaje = floatval($_POST['fidelidad_porcentaje']);
        $activo = isset($_POST['fidelidad_activo']) ? 1 : 0;
        
        $sql = "UPDATE descuentos_config SET porcentaje = :porcentaje, activo = :activo WHERE tipo_descuento = 'fidelidad'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['porcentaje' => $porcentaje, 'activo' => $activo]);
    }
    
    // Actualizar descuento de cumpleaños
    if (isset($_POST['cumpleanos_porcentaje'])) {
        $porcentaje = floatval($_POST['cumpleanos_porcentaje']);
        $activo = isset($_POST['cumpleanos_activo']) ? 1 : 0;
        
        $sql = "UPDATE descuentos_config SET porcentaje = :porcentaje, activo = :activo WHERE tipo_descuento = 'cumpleanos'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['porcentaje' => $porcentaje, 'activo' => $activo]);
    }
    
    // Actualizar descuento promocional
    if (isset($_POST['promocional_porcentaje'])) {
        $porcentaje = floatval($_POST['promocional_porcentaje']);
        $activo = isset($_POST['promocional_activo']) ? 1 : 0;
        
        $sql = "UPDATE descuentos_config SET porcentaje = :porcentaje, activo = :activo WHERE tipo_descuento = 'promocional'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['porcentaje' => $porcentaje, 'activo' => $activo]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Descuentos actualizados exitosamente']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>

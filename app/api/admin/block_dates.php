<?php
/**
 * Bloquear fechas manualmente
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

$startDate = $_POST['start_date'] ?? '';
$endDate = $_POST['end_date'] ?? '';
$reason = $_POST['reason'] ?? '';
$description = $_POST['description'] ?? '';
$blockType = $_POST['block_type'] ?? 'range'; // 'single' o 'range'

// Validaciones básicas
if (empty($startDate) || empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
    exit;
}

// Si es un solo día, usar la misma fecha para inicio y fin
if ($blockType === 'single') {
    $endDate = $startDate;
}

// Si es rango, validar que se proporcione fecha de fin
if ($blockType === 'range' && empty($endDate)) {
    echo json_encode(['success' => false, 'message' => 'Para bloquear un rango debe proporcionar fecha de fin']);
    exit;
}

// Validar fechas
if (strtotime($startDate) > strtotime($endDate)) {
    echo json_encode(['success' => false, 'message' => 'La fecha de inicio debe ser anterior a la fecha de fin']);
    exit;
}

if (strtotime($startDate) < strtotime(date('Y-m-d'))) {
    echo json_encode(['success' => false, 'message' => 'No se pueden bloquear fechas pasadas']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Error de conexión a la base de datos");
    }
    
    // Verificar si ya existe un bloqueo en esas fechas
    $checkSql = "SELECT COUNT(*) FROM fechas_bloqueadas 
                 WHERE (fecha_inicio <= :end_date AND fecha_fin >= :start_date)
                 AND activo = 1";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
    
    if ($checkStmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Ya existe un bloqueo activo en esas fechas']);
        exit;
    }
    
    // Verificar si hay reservas aprobadas en esas fechas
    // Tratar la fecha de salida como exclusiva: una reserva 10-12 no bloquea el día 12 como inicio de otra reserva.
    $checkReservas = "SELECT COUNT(*) FROM reservas 
                      WHERE estado = 'aprobada' 
                      AND (fecha_entrada < :end_date AND fecha_salida > :start_date)";
    $checkReservasStmt = $db->prepare($checkReservas);
    $checkReservasStmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
    
    if ($checkReservasStmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'No se pueden bloquear fechas que tienen reservas aprobadas']);
        exit;
    }
    
    // Insertar el bloqueo
    $insertSql = "INSERT INTO fechas_bloqueadas (fecha_inicio, fecha_fin, motivo, descripcion, activo, creado_en) 
                   VALUES (:start_date, :end_date, :reason, :description, 1, NOW())";
    
    $insertStmt = $db->prepare($insertSql);
    
    $result = $insertStmt->execute([
        'start_date' => $startDate,
        'end_date' => $endDate,
        'reason' => $reason,
        'description' => $description
    ]);
    
    if ($result) {
        $message = $blockType === 'single' 
            ? "Fecha bloqueada exitosamente: " . date('d/m/Y', strtotime($startDate))
            : "Rango de fechas bloqueado exitosamente: " . date('d/m/Y', strtotime($startDate)) . " - " . date('d/m/Y', strtotime($endDate));
            
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'blocked_dates' => [
                'start' => $startDate,
                'end' => $endDate,
                'type' => $blockType
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al bloquear las fechas']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>

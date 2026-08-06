<?php
/**
 * Ver Comprobante de Pago - Sistema de Reservas
 * My Suite In Cartagena
 */

session_start();

// Verificar si el usuario está logueado como admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../../../app/controllers/auth/login.php');
    exit;
}

$reserva_id = $_GET['id'] ?? '';
$comprobante = $_GET['file'] ?? '';

if (empty($reserva_id) || empty($comprobante)) {
    die('Error: Parámetros no válidos');
}

// Verificar que el archivo existe
$file_path = '../uploads/comprobantes/' . $comprobante;

if (!file_exists($file_path)) {
    die('Error: Archivo no encontrado');
}

// Obtener información del archivo
$file_info = pathinfo($file_path);
$extension = strtolower($file_info['extension']);

// Determinar el tipo de contenido
$content_type = 'application/octet-stream';
if (in_array($extension, ['jpg', 'jpeg'])) {
    $content_type = 'image/jpeg';
} elseif ($extension === 'png') {
    $content_type = 'image/png';
} elseif ($extension === 'pdf') {
    $content_type = 'application/pdf';
}

// Enviar el archivo
header('Content-Type: ' . $content_type);
header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

readfile($file_path);
exit;
?>

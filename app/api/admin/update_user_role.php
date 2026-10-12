<?php
/**
 * API: Actualizar Rol de Usuario
 * My Suite In Cartagena
 */

require_once __DIR__ . '/../../../includes/auth_helper.php';
AuthHelper::requireModule('permisos');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

$id_usuario = isset($data['id_usuario']) ? (int)$data['id_usuario'] : 0;
$nuevo_rol = isset($data['rol']) ? trim(strtolower($data['rol'])) : '';

$roles_permitidos = ['admin', 'operador', 'cliente'];

if ($id_usuario <= 0 || !in_array($nuevo_rol, $roles_permitidos, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit;
}

// Prevenir que un admin se descienda a sí mismo si es el usuario logueado
$currentUserEmail = $_SESSION['user_correo'] ?? $_SESSION['admin_correo'] ?? '';
$currentUserId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("Error al conectar a la base de datos.");
    }

    if ($id_usuario === (int)$currentUserId && $nuevo_rol !== 'admin') {
        echo json_encode([
            'success' => false,
            'message' => 'Por seguridad, no puedes quitarte el rol de Administrador a ti mismo.'
        ]);
        exit;
    }

    $stmt = $db->prepare("UPDATE usuarios SET rol = ? WHERE id_usuario = ?");
    $stmt->execute([$nuevo_rol, $id_usuario]);

    echo json_encode([
        'success' => true,
        'message' => 'Rol de usuario actualizado con éxito a: ' . strtoupper($nuevo_rol)
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar rol: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

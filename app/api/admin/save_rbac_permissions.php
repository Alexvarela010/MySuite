<?php
/**
 * API: Guardar Matriz de Permisos RBAC
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

if (!$data || !isset($data['permisos']) || !is_array($data['permisos'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos inválidos proporcionados']);
    exit;
}

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("Error al conectar a la base de datos.");
    }

    $db->beginTransaction();

    $stmt = $db->prepare("
        INSERT INTO rol_permisos (rol_codigo, modulo_codigo, permitido)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE permitido = VALUES(permitido), actualizado_en = CURRENT_TIMESTAMP
    ");

    foreach ($data['permisos'] as $rolCodigo => $modulosPermisos) {
        // Por seguridad, admin siempre tiene acceso total (1)
        if ($rolCodigo === 'admin') {
            continue;
        }

        foreach ($modulosPermisos as $moduloCodigo => $permitido) {
            $stmt->execute([
                $rolCodigo,
                $moduloCodigo,
                $permitido ? 1 : 0
            ]);
        }
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Matriz de permisos actualizada correctamente.'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar permisos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

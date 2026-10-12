<?php
/**
 * API: Obtener datos RBAC (Roles, Módulos, Permisos y Usuarios)
 * My Suite In Cartagena
 */

require_once __DIR__ . '/../../../includes/auth_helper.php';
AuthHelper::requireModule('permisos');

header('Content-Type: application/json; charset=utf-8');

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("Error al conectar a la base de datos.");
    }

    // 1. Obtener Roles
    $stmtRoles = $db->query("SELECT * FROM roles ORDER BY id_rol ASC");
    $roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

    // 2. Obtener Módulos
    $stmtModulos = $db->query("SELECT * FROM modulos WHERE activo = 1 ORDER BY orden ASC");
    $modulos = $stmtModulos->fetchAll(PDO::FETCH_ASSOC);

    // 3. Obtener Matriz de Permisos
    $stmtPermisos = $db->query("SELECT rol_codigo, modulo_codigo, permitido FROM rol_permisos");
    $rawPermisos = $stmtPermisos->fetchAll(PDO::FETCH_ASSOC);

    $matriz = [];
    foreach ($rawPermisos as $p) {
        $matriz[$p['rol_codigo']][$p['modulo_codigo']] = (int)$p['permitido'];
    }

    // Asegurar que admin tenga 1 en todos
    foreach ($modulos as $m) {
        $matriz['admin'][$m['codigo']] = 1;
        if (!isset($matriz['operador'][$m['codigo']])) {
            $matriz['operador'][$m['codigo']] = 0;
        }
    }

    // 4. Obtener Usuarios para gestión de roles (todos los staff y últimos clientes registrados)
    $stmtUsers = $db->query("
        SELECT id_usuario, nombre, apellido, correo, telefono, rol, creado_en
        FROM usuarios 
        ORDER BY 
            CASE 
                WHEN rol = 'admin' THEN 1 
                WHEN rol = 'operador' THEN 2 
                ELSE 3 
            END ASC,
            creado_en DESC
    ");
    $usuarios = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'roles' => $roles,
        'modulos' => $modulos,
        'permisos' => $matriz,
        'usuarios' => $usuarios
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al cargar datos RBAC: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

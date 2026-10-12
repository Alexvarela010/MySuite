<?php
/**
 * API: Obtener movimientos de contabilidad con filtros, paginación y totales
 * My Suite In Cartagena
 */

require_once __DIR__ . '/../../../includes/auth_helper.php';
AuthHelper::requireModule('contabilidad');

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/contabilidad_helper.php';

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
ob_start();

header('Content-Type: application/json; charset=utf-8');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Error de conexión a la base de datos.");
    }

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

    $filters = [
        'mes' => isset($_GET['mes']) && $_GET['mes'] !== '' ? (int)$_GET['mes'] : null,
        'anio' => isset($_GET['anio']) && $_GET['anio'] !== '' ? (int)$_GET['anio'] : null,
        'fecha_desde' => isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : null,
        'fecha_hasta' => isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : null,
        'tipo' => isset($_GET['tipo']) && !empty($_GET['tipo']) ? trim($_GET['tipo']) : null,
        'categoria' => isset($_GET['categoria']) && !empty($_GET['categoria']) ? trim($_GET['categoria']) : null,
        'metodo_pago' => isset($_GET['metodo_pago']) && !empty($_GET['metodo_pago']) ? trim($_GET['metodo_pago']) : null,
        'responsable' => isset($_GET['responsable']) && !empty($_GET['responsable']) ? trim($_GET['responsable']) : null,
        'search' => isset($_GET['search']) && !empty($_GET['search']) ? trim($_GET['search']) : null
    ];

    $data = ContabilidadHelper::getMovimientos($db, $filters, $page, $limit);
    $totales = ContabilidadHelper::getTotales($db, $filters);
    $categorias = ContabilidadHelper::getCategorias($db);

    if (ob_get_length()) {
        ob_clean();
    }

    echo json_encode([
        'success' => true,
        'movimientos' => $data['data'],
        'pagination' => $data['pagination'],
        'totales' => $totales,
        'catalogo' => [
            'categorias' => $categorias,
            'metodos_pago' => ContabilidadHelper::$default_metodos_pago,
            'responsables' => ContabilidadHelper::$default_responsables
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

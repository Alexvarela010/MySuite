<?php
/**
 * API: Exportar movimientos de contabilidad a Excel nativo (.xlsx)
 * My Suite In Cartagena
 * 100% compatible con Microsoft Excel sin advertencias de formato o extensión.
 */

require_once __DIR__ . '/../../../includes/auth_helper.php';
AuthHelper::requireModule('contabilidad');

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/contabilidad_helper.php';
require_once __DIR__ . '/../../../includes/SimpleXlsxWriter.php';

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Error de conexión a la base de datos.");
    }

    // Seguridad: La exportación a Excel está reservada exclusivamente para administradores
    if (!AuthHelper::isAdmin()) {
        http_response_code(403);
        echo "<h3>Acceso denegado</h3><p>La exportación de contabilidad a Excel está permitida únicamente para administradores.</p>";
        exit;
    }

    $filters = [
        'mes' => isset($_GET['mes']) && $_GET['mes'] !== '' ? (int)$_GET['mes'] : null,
        'anio' => isset($_GET['anio']) && $_GET['anio'] !== '' ? (int)$_GET['anio'] : null,
        'fecha_desde' => isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : null,
        'fecha_hasta' => isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : null,
        'tipo' => isset($_GET['tipo']) && !empty($_GET['tipo']) ? trim($_GET['tipo']) : null,
        'caja' => isset($_GET['caja']) && !empty($_GET['caja']) ? trim($_GET['caja']) : null,
        'categoria' => isset($_GET['categoria']) && !empty($_GET['categoria']) ? trim($_GET['categoria']) : null,
        'metodo_pago' => isset($_GET['metodo_pago']) && !empty($_GET['metodo_pago']) ? trim($_GET['metodo_pago']) : null,
        'responsable' => isset($_GET['responsable']) && !empty($_GET['responsable']) ? trim($_GET['responsable']) : null,
        'search' => isset($_GET['search']) && !empty($_GET['search']) ? trim($_GET['search']) : null
    ];

    // Obtener todos los registros que coinciden con los filtros (sin límite de paginación)
    $res = ContabilidadHelper::getMovimientos($db, $filters, 1, 100000);
    $movimientos = $res['data'];
    $totales = ContabilidadHelper::getTotales($db, $filters);

    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];

    // Construir nombre de archivo descriptivo .xlsx
    $filenameParts = ['Contabilidad_MySuite'];
    if (!empty($filters['anio'])) {
        $filenameParts[] = $filters['anio'];
    }
    if (!empty($filters['mes']) && isset($meses[$filters['mes']])) {
        $filenameParts[] = $meses[$filters['mes']];
    }
    if (!empty($filters['tipo'])) {
        $filenameParts[] = ucfirst($filters['tipo']);
    }
    if (!empty($filters['caja'])) {
        $filenameParts[] = 'Caja_' . ucfirst(strtolower($filters['caja']));
    }
    $filename = implode('_', $filenameParts) . '.xlsx';

    // Exportar archivo .xlsx nativo
    SimpleXlsxWriter::outputContabilidad($movimientos, $totales, $filters, $filename);

} catch (\Throwable $e) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<h3>Error al exportar contabilidad:</h3><p>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

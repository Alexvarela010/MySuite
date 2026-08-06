<?php
/**
 * Procesar archivo Excel y cargar tarifas a la base de datos
 * Sistema de Reservas - My Suite In Cartagena
 */

session_start();

// Verificar si el usuario está logueado como admin
if (!((isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) ||
    (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && 
     isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin'))) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

// Verificar extensiones PHP necesarias
if (!extension_loaded('zip')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'La extensión ZIP de PHP no está habilitada. Por favor, habilite la extensión zip en su archivo php.ini (busque y descomente la línea: extension=zip) y reinicie su servidor web.'
    ]);
    exit;
}

if (!extension_loaded('xml')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'La extensión XML de PHP no está habilitada. Por favor, habilite la extensión xml en su archivo php.ini.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar que se haya subido un archivo
if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
    $error_message = 'No se ha subido ningún archivo o hubo un error en la subida.';
    if (isset($_FILES['excel_file']['error'])) {
        switch ($_FILES['excel_file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_message = 'El archivo es demasiado grande.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message = 'El archivo se subió parcialmente.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message = 'No se seleccionó ningún archivo.';
                break;
        }
    }
    echo json_encode(['success' => false, 'message' => $error_message]);
    exit;
}

$file = $_FILES['excel_file'];
$apartamento_id = $_POST['apartamento_id'] ?? 1;

// Validar extensión del archivo
$allowed_extensions = ['xls', 'xlsx', 'csv'];
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($file_extension, $allowed_extensions)) {
    echo json_encode(['success' => false, 'message' => 'Formato de archivo no válido. Solo se permiten archivos Excel (.xls, .xlsx) o CSV.']);
    exit;
}

try {
    // Mapeo de meses en español a números
    $meses = [
        'ENERO' => '01', 'FEBRERO' => '02', 'MARZO' => '03', 'ABRIL' => '04',
        'MAYO' => '05', 'JUNIO' => '06', 'JULIO' => '07', 'AGOSTO' => '08',
        'SEPTIEMBRE' => '09', 'OCTUBRE' => '10', 'NOVIEMBRE' => '11', 'DICIEMBRE' => '12'
    ];

    // Cargar el archivo Excel
    // Para archivos CSV, usar un método diferente si es necesario
    if ($file_extension === 'csv') {
        // Para CSV, usar un método más simple
        $data = [];
        if (($handle = fopen($file['tmp_name'], 'r')) !== false) {
            $headers = fgetcsv($handle); // Primera fila como headers
            if ($headers) {
                // Limpiar headers
                $headers = array_map(function($header) {
                    return trim(str_replace(["\n", "\r"], ' ', $header));
                }, $headers);
                
                while (($row = fgetcsv($handle)) !== false) {
                    if (count($row) === count($headers)) {
                        $data[] = array_combine($headers, $row);
                    }
                }
                fclose($handle);
            }
        }
        
        if (empty($data)) {
            throw new Exception('El archivo CSV está vacío o no tiene datos válidos.');
        }
        
        // Convertir a formato similar al de Excel
        $excel_data = [];
        $excel_data[] = $headers; // Primera fila con headers
        foreach ($data as $row) {
            $excel_data[] = array_values($row); // Convertir de array asociativo a numérico
        }
        $data = $excel_data;
    } else {
        // Para archivos Excel (.xls, .xlsx)
        if (!class_exists('ZipArchive')) {
            throw new Exception('La clase ZipArchive no está disponible. Por favor, habilite la extensión zip de PHP.');
        }
        
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = $worksheet->toArray();
    }

    if (empty($data) || count($data) < 2) {
        throw new Exception('El archivo está vacío o no tiene datos válidos.');
    }

    // Obtener la primera fila como encabezados
    $headers = array_map(function($header) {
        return trim(str_replace(["\n", "\r"], ' ', $header));
    }, array_shift($data));

    // Buscar las columnas necesarias
    $col_anio_index = null;
    $col_mes_index = null;
    $col_dia_index = null;
    $col_precio_index = null;

    foreach ($headers as $index => $header) {
        $header_upper = strtoupper($header);
        if (strpos($header_upper, 'AÑO') !== false || strpos($header_upper, 'ANO') !== false) {
            $col_anio_index = $index;
        }
        if (strpos($header_upper, 'MES') !== false) {
            $col_mes_index = $index;
        }
        if (strpos($header_upper, 'DIA') !== false || strpos($header_upper, 'DÍA') !== false) {
            $col_dia_index = $index;
        }
        if (strpos($header_upper, 'T CREDITO') !== false || strpos($header_upper, 'T CRÉDITO') !== false || 
            strpos($header_upper, 'PRECIO') !== false || strpos($header_upper, 'PRICE') !== false) {
            $col_precio_index = $index;
        }
    }

    // Validar que se encontraron todas las columnas necesarias
    if ($col_anio_index === null || $col_mes_index === null || $col_dia_index === null || $col_precio_index === null) {
        throw new Exception('No se encontraron todas las columnas necesarias en el Excel. Se requieren: AÑO, MES, DIA, T credito 7% (o similar).');
    }

    // Conectar a la base de datos
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Error de conexión a la base de datos");
    }

    // Preparar sentencia para insertar/actualizar tarifas usando ON DUPLICATE KEY UPDATE
    $insertQuery = "
        INSERT INTO tarifas (id_apartamento, fecha, precio, temporada) 
        VALUES (:apartamento_id, :fecha, :precio, 'baja')
        ON DUPLICATE KEY UPDATE precio = VALUES(precio)
    ";

    $insertStmt = $db->prepare($insertQuery);

    // Contadores para estadísticas
    $procesadas = 0;
    $actualizadas = 0;
    $creadas = 0;
    $errores = 0;
    $errores_detalle = [];

    // Iniciar transacción
    $db->beginTransaction();

    try {
        // Procesar cada fila
        foreach ($data as $rowIndex => $row) {
            // Saltar filas vacías
            if (empty(array_filter($row))) {
                continue;
            }

            // Obtener valores de las columnas
            $anio = isset($row[$col_anio_index]) ? trim($row[$col_anio_index]) : null;
            $mes_nombre = isset($row[$col_mes_index]) ? trim(strtoupper($row[$col_mes_index])) : null;
            $dia = isset($row[$col_dia_index]) ? trim($row[$col_dia_index]) : null;
            $precio = isset($row[$col_precio_index]) ? trim($row[$col_precio_index]) : null;

            // Validar que todos los campos tengan valores
            if (empty($anio) || empty($mes_nombre) || empty($dia) || empty($precio)) {
                $errores++;
                $errores_detalle[] = "Fila " . ($rowIndex + 2) . ": Faltan datos requeridos";
                continue;
            }

            // Obtener el número del mes
            $mes_num = $meses[$mes_nombre] ?? null;

            if (!$mes_num) {
                $errores++;
                $errores_detalle[] = "Fila " . ($rowIndex + 2) . ": Mes inválido '$mes_nombre'";
                continue;
            }

            // Validar y convertir valores
            $anio = (int)$anio;
            $dia = (int)$dia;
            $precio = (float)$precio;

            if ($anio < 2000 || $anio > 2100) {
                $errores++;
                $errores_detalle[] = "Fila " . ($rowIndex + 2) . ": Año inválido '$anio'";
                continue;
            }

            if ($dia < 1 || $dia > 31) {
                $errores++;
                $errores_detalle[] = "Fila " . ($rowIndex + 2) . ": Día inválido '$dia'";
                continue;
            }

            if ($precio < 0) {
                $errores++;
                $errores_detalle[] = "Fila " . ($rowIndex + 2) . ": Precio inválido '$precio'";
                continue;
            }

            // Formatear la fecha como YYYY-MM-DD
            $fecha_formateada = sprintf("%04d-%02d-%02d", $anio, $mes_num, $dia);

            // Validar que la fecha sea válida
            $date_check = DateTime::createFromFormat('Y-m-d', $fecha_formateada);
            if (!$date_check || $date_check->format('Y-m-d') !== $fecha_formateada) {
                $errores++;
                $errores_detalle[] = "Fila " . ($rowIndex + 2) . ": Fecha inválida '$fecha_formateada'";
                continue;
            }

            // Verificar si ya existe la tarifa para estadísticas
            $checkQuery = "
                SELECT id_tarifa 
                FROM tarifas 
                WHERE id_apartamento = :apartamento_id 
                AND fecha = :fecha
            ";
            
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':apartamento_id', $apartamento_id);
            $checkStmt->bindParam(':fecha', $fecha_formateada);
            $checkStmt->execute();
            
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            // Usar INSERT ... ON DUPLICATE KEY UPDATE para insertar o actualizar
            $insertStmt->bindParam(':apartamento_id', $apartamento_id);
            $insertStmt->bindParam(':fecha', $fecha_formateada);
            $insertStmt->bindParam(':precio', $precio);
            $insertStmt->execute();

            if ($existing) {
                $actualizadas++;
            } else {
                $creadas++;
            }

            $procesadas++;
        }

        // Confirmar transacción
        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Tarifas procesadas exitosamente',
            'stats' => [
                'procesadas' => $procesadas,
                'creadas' => $creadas,
                'actualizadas' => $actualizadas,
                'errores' => $errores
            ],
            'errores_detalle' => $errores_detalle
        ]);

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar el archivo: ' . $e->getMessage()
    ]);
}
?>


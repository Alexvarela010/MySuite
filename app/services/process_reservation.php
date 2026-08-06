<?php
/**
 * Procesar Reserva - Sistema de Reservas
 * My Suite In Cartagena
 * Versión ULTRA SIMPLIFICADA con captura total de errores
 */

// Capturar TODOS los errores posibles
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Iniciar buffer de salida
while (ob_get_level() > 0) {
    @ob_end_clean();
}
ob_start();

// Función para enviar respuesta JSON
function enviarJSON($success, $message, $data = null, $http_code = 200) {
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    
    http_response_code($http_code);
    header('Content-Type: application/json; charset=utf-8');
    
    $respuesta = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $respuesta['data'] = $data;
    }
    
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

// Capturar errores fatales
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Error fatal: ' . $error['message'] . ' en ' . $error['file'] . ' línea ' . $error['line']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
});

try {
    // Incluir archivos
    $db_path = __DIR__ . '/../../config/database.php';
    if (!file_exists($db_path)) {
        enviarJSON(false, 'Archivo database.php no encontrado en: ' . $db_path, null, 500);
    }
    require_once $db_path;
    
    $functions_path = __DIR__ . '/../../includes/functions.php';
    if (!file_exists($functions_path)) {
        enviarJSON(false, 'Archivo functions.php no encontrado en: ' . $functions_path, null, 500);
    }
    require_once $functions_path;
    
    // Validar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        enviarJSON(false, 'Método no permitido', null, 405);
    }
    
    // Obtener datos
    $raw_input = file_get_contents('php://input');
    if (empty($raw_input)) {
        enviarJSON(false, 'No se recibieron datos', null, 400);
    }
    
    $input = json_decode($raw_input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        enviarJSON(false, 'JSON inválido: ' . json_last_error_msg(), null, 400);
    }
    
    if (!$input || !is_array($input)) {
        enviarJSON(false, 'Datos inválidos', null, 400);
    }
    
    // Validar campos requeridos
    $campos_requeridos = ['nombre', 'apellido', 'correo', 'telefono', 'fecha_entrada', 'fecha_salida', 'num_adultos', 'metodo_pago'];
    foreach ($campos_requeridos as $campo) {
        if (empty($input[$campo])) {
            enviarJSON(false, "Campo requerido: $campo", null, 400);
        }
    }
    
    // Validar fechas
    try {
        $fecha_entrada = new DateTime($input['fecha_entrada']);
        $fecha_salida = new DateTime($input['fecha_salida']);
        
        if ($fecha_entrada >= $fecha_salida) {
            enviarJSON(false, 'La fecha de salida debe ser posterior a la fecha de entrada', null, 400);
        }
    } catch (Exception $e) {
        enviarJSON(false, 'Formato de fecha inválido: ' . $e->getMessage(), null, 400);
    }
    
    // Preparar datos
    $datos_reserva = [
        'id_apartamento' => isset($input['id_apartamento']) ? (int)$input['id_apartamento'] : 1,
        'id_usuario' => isset($input['id_usuario']) && !empty($input['id_usuario']) ? (int)$input['id_usuario'] : null,
        'nombre' => trim($input['nombre']),
        'apellido' => trim($input['apellido']),
        'correo' => trim($input['correo']),
        'telefono' => trim($input['telefono']),
        'fecha_nacimiento' => isset($input['fecha_nacimiento']) && !empty($input['fecha_nacimiento']) ? $input['fecha_nacimiento'] : null,
        'fecha_entrada' => $input['fecha_entrada'],
        'fecha_salida' => $input['fecha_salida'],
        'num_adultos' => (int)$input['num_adultos'],
        'num_ninos' => isset($input['num_ninos']) ? (int)$input['num_ninos'] : 0,
        'vive_palmira' => isset($input['vive_palmira']) && $input['vive_palmira'] ? 1 : 0,
        'metodo_pago' => $input['metodo_pago'],
        'costo_base' => isset($input['costo_base']) ? (float)$input['costo_base'] : 0,
        'descuento_fidelizacion' => isset($input['descuento_fidelizacion']) ? (int)round($input['descuento_fidelizacion']) : 0,
        'descuento_cumpleanios' => isset($input['descuento_cumpleanios']) ? (float)$input['descuento_cumpleanios'] : 0,
        'descuento_promocional' => isset($input['descuento_promocional']) ? (float)$input['descuento_promocional'] : 0,
        'total' => isset($input['total']) ? (float)$input['total'] : 0
    ];
    
    // Validar que guardarReserva existe
    if (!function_exists('guardarReserva')) {
        enviarJSON(false, 'Función guardarReserva no encontrada', null, 500);
    }
    
    // Guardar reserva
    $reserva_id = guardarReserva($datos_reserva);
    
    if ($reserva_id && $reserva_id > 0) {
        enviarJSON(true, 'Reserva enviada exitosamente', ['reserva_id' => $reserva_id], 200);
    } else {
        enviarJSON(false, 'Error al guardar la reserva. Verifica los logs del servidor.', null, 500);
    }
    
} catch (Throwable $e) {
    error_log("ERROR FATAL en process_reservation.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    enviarJSON(false, 'Error: ' . $e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine(), null, 500);
}
?>

<?php
/**
 * Sistema de Reservas - My Suite In Cartagena
 * Integrado con base de datos MySQL
 * Internationalized version
 */

// Suprimir warnings que puedan aparecer antes del JSON
$old_error_reporting = error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
$old_display_errors = ini_get('display_errors');
ini_set('display_errors', 0);

session_start();

date_default_timezone_set('America/Bogota'); 

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/i18n/i18n.php';

// Detectar idioma desde parámetro GET, ruta o sesión
$lang = 'es'; // Por defecto
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'es', 'it'])) {
    $lang = $_GET['lang'];
    // Guardar en sesión para mantenerlo
    $_SESSION['user_lang'] = $lang;
} elseif (isset($_SESSION['user_lang']) && in_array($_SESSION['user_lang'], ['en', 'es', 'it'])) {
    // Usar idioma guardado en sesión
    $lang = $_SESSION['user_lang'];
} else {
    // Detectar desde la ruta
    $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($scriptPath, '/es/') !== false) {
        $lang = 'es';
    } elseif (strpos($scriptPath, '/it/') !== false) {
        $lang = 'it';
    } elseif (strpos($scriptPath, '/en/') !== false) {
        $lang = 'en';
    }
    // Guardar en sesión
    $_SESSION['user_lang'] = $lang;
}

// Inicializar sistema de internacionalización
I18n::init($lang);

// Las funciones getFechasOcupadas, getTarifaPorFecha, guardarReserva, etc.
// ahora están en includes/functions.php y se cargan automáticamente

// Obtener fechas ocupadas y precio base desde la base de datos
$occupied_dates = getFechasOcupadas();
$checkin_checkout = getCheckinCheckoutDates(1);
$checkin_dates    = $checkin_checkout['checkin'];   // días de entrada de reservas existentes
$checkout_dates   = $checkin_checkout['checkout'];  // días de salida de reservas existentes
$base_price = 200000;

// Obtener descuentos desde la base de datos
$descuentos = [];
try {
    $database = new Database();
    $db = $database->getConnection();
    $query = "SELECT tipo_descuento, porcentaje, activo FROM descuentos_config";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $descuentos_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($descuentos_db as $descuento) {
        $descuentos[$descuento['tipo_descuento']] = [
            'porcentaje' => floatval($descuento['porcentaje']),
            'activo' => (bool)$descuento['activo']
        ];
    }
} catch (Exception $e) {
    // Valores por defecto en caso de error
    $descuentos = [
        'fidelidad' => ['porcentaje' => 5.0, 'activo' => false],
        'cumpleanos' => ['porcentaje' => 30.0, 'activo' => false],
        'promocional' => ['porcentaje' => 3.0, 'activo' => true]
    ];
}

// Asegurar que $descuentos siempre esté definido y sea un array
if (!isset($descuentos) || !is_array($descuentos)) {
    $descuentos = [
        'fidelidad' => ['porcentaje' => 5.0, 'activo' => false],
        'cumpleanos' => ['porcentaje' => 30.0, 'activo' => false],
        'promocional' => ['porcentaje' => 3.0, 'activo' => true]
    ];
}

// Verificar si el usuario está logueado
$user_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
$user_name = $user_logged_in ? $_SESSION['user_nombre'] : '';
$user_role = $user_logged_in ? $_SESSION['user_rol'] : '';

// Obtener datos completos del usuario logueado para prellenar formulario
$user_data = null;
if ($user_logged_in && isset($_SESSION['user_id'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $query = "SELECT nombre, apellido, correo, telefono, fecha_nacimiento FROM usuarios WHERE id_usuario = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // En caso de error, continuar sin datos del usuario
        $user_data = null;
    }
}

// Asegurar que $user_data siempre esté definido como null si no existe
if (!isset($user_data)) {
    $user_data = null;
}

// Obtener estadísticas de usuarios
$total_usuarios = 0;
$ultimo_usuario_id = null;
try {
    $database = new Database();
    $db = $database->getConnection();
    // Contar usuarios registrados (clientes)
    $query_count = "SELECT COUNT(*) as total FROM usuarios WHERE rol = 'cliente'";
    $stmt_count = $db->prepare($query_count);
    $stmt_count->execute();
    $result_count = $stmt_count->fetch(PDO::FETCH_ASSOC);
    $total_usuarios = $result_count['total'] ?? 0;
    
    // Obtener ID del último usuario logueado (si hay uno)
    if ($user_logged_in && isset($_SESSION['user_id'])) {
        $ultimo_usuario_id = $_SESSION['user_id'];
    }
} catch (Exception $e) {
    // En caso de error, usar valores por defecto
    $total_usuarios = 0;
    $ultimo_usuario_id = null;
}

// Incluir el archivo compartido con todo el HTML/JS
require_once __DIR__ . '/includes/index_shared.php';


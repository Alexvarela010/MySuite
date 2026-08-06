<?php
/**
 * Gestión de Reservas - Panel de Administración
 * My Suite In Cartagena
 */

// Habilitar reporte de errores para debugging (solo en desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en pantalla (seguridad)
ini_set('log_errors', 1); // Log errores al archivo

session_start();

// Verificar si el usuario está logueado como admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../../../app/controllers/auth/login.php');
    exit;
}

// Verificar y cargar archivos requeridos
$database_path = __DIR__ . '/../../../config/database.php';
$functions_path = __DIR__ . '/../../../includes/functions.php';
$gmail_sender_path = __DIR__ . '/../../../includes/GmailSender.php';

if (!file_exists($database_path)) {
    error_log("Error: No se encontró database.php en: $database_path");
    http_response_code(500);
    die(json_encode(['error' => 'Error de configuración del servidor']));
}

if (!file_exists($functions_path)) {
    error_log("Error: No se encontró functions.php en: $functions_path");
    http_response_code(500);
    die(json_encode(['error' => 'Error de configuración del servidor']));
}

if (!file_exists($gmail_sender_path)) {
    error_log("Error: No se encontró GmailSender.php en: $gmail_sender_path");
    http_response_code(500);
    die(json_encode(['error' => 'Error de configuración del servidor']));
}

require_once $database_path;
require_once $functions_path;
require_once $gmail_sender_path;

/**
 * Enviar email de aprobación al cliente usando plantillas nuevas
 */
function enviarEmailAprobacion($reserva) {
    try {
        if (!class_exists('GmailSender')) {
            error_log("Error: La clase GmailSender no está disponible");
            return false;
        }
        $emailSender = new GmailSender();
        
        // Determinar el método de pago y usar la plantilla correspondiente
        $metodo_pago = strtolower(trim($reserva['metodo_pago'] ?? 'transferencia'));
        
        // Usar plantilla correspondiente según método de pago
        // El parámetro false asegura que NO se envíe CC a gerencia en el correo del cliente
        if ($metodo_pago === 'tarjeta' || $metodo_pago === 'card' || $metodo_pago === 'tarjeta_credito') {
            return $emailSender->sendReservaAprobadaTarjeta20($reserva, false);
        } else {
            // Por defecto transferencia/efectivo
            return $emailSender->sendReservaAprobadaTransferencia20($reserva, false);
        }
    } catch (Exception $e) {
        error_log("Error enviando email de aprobación: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return false;
    } catch (Error $e) {
        error_log("Error fatal enviando email de aprobación: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return false;
    }
}

$mensaje = '';
$tipo_mensaje = '';

// Procesar acciones
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    // Validar ID
    if ($id <= 0) {
        $mensaje = 'Error: ID de reserva inválido';
        $tipo_mensaje = 'danger';
    } else {
        try {
            if (!class_exists('Database')) {
                throw new Exception("La clase Database no está disponible");
            }
            
            $database = new Database();
            $db = $database->getConnection();
            
            if (!$db) {
                throw new Exception("Error de conexión a la base de datos");
            }
            
            switch ($action) {
            case 'approve':
                // Iniciar transacción
                $db->beginTransaction();
                
                try {
                    // Obtener datos de la reserva antes de aprobar
                    $query_reserva = "SELECT *, email_enviado FROM reservas WHERE id_reserva = ?";
                    $stmt_reserva = $db->prepare($query_reserva);
                    $stmt_reserva->execute([$id]);
                    $reserva = $stmt_reserva->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$reserva) {
                        throw new Exception('Reserva no encontrada');
                    }
                    
                    // Buscar reservas pendientes que se solapen con las fechas de esta reserva
                    // Dos rangos se solapan si: fecha_entrada_reserva <= fecha_salida_aprobada AND fecha_salida_reserva >= fecha_entrada_aprobada
                    $query_solapadas = "
                        SELECT id_reserva, nombre, apellido, correo, fecha_entrada, fecha_salida 
                        FROM reservas 
                        WHERE id_reserva != ? 
                        AND estado = 'pendiente'
                        AND id_apartamento = ?
                        AND fecha_entrada <= ?
                        AND fecha_salida >= ?
                    ";
                    
                    $stmt_solapadas = $db->prepare($query_solapadas);
                    $fecha_entrada = $reserva['fecha_entrada'];
                    $fecha_salida = $reserva['fecha_salida'];
                    $id_apartamento = $reserva['id_apartamento'] ?? 1;
                    
                    $stmt_solapadas->execute([
                        $id,
                        $id_apartamento,
                        $fecha_salida,  // fecha_entrada_reserva <= fecha_salida_aprobada
                        $fecha_entrada  // fecha_salida_reserva >= fecha_entrada_aprobada
                    ]);
                    
                    $reservas_solapadas = $stmt_solapadas->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Rechazar automáticamente las reservas solapadas
                    $reservas_rechazadas = 0;
                    $emails_enviados = 0;
                    
                    foreach ($reservas_solapadas as $reserva_solapada) {
                        // Rechazar la reserva solapada
                        $query_rechazar = "UPDATE reservas SET estado = 'rechazada' WHERE id_reserva = ?";
                        $stmt_rechazar = $db->prepare($query_rechazar);
                        $stmt_rechazar->execute([$reserva_solapada['id_reserva']]);
                        $reservas_rechazadas++;
                        
                        // Enviar email de rechazo al cliente
                        try {
                            if (class_exists('GmailSender')) {
                                $emailSender = new GmailSender();
                                $email_enviado = $emailSender->sendReservaRechazada($reserva_solapada, $reserva);
                                if ($email_enviado) {
                                    $emails_enviados++;
                                }
                            } else {
                                error_log("Advertencia: GmailSender no disponible para enviar email de rechazo");
                            }
                        } catch (Exception $e) {
                            error_log("Error enviando email de rechazo: " . $e->getMessage());
                            error_log("Stack trace: " . $e->getTraceAsString());
                        } catch (Error $e) {
                            error_log("Error fatal enviando email de rechazo: " . $e->getMessage());
                            error_log("Stack trace: " . $e->getTraceAsString());
                        }
                    }
                    
                    // Aprobar la reserva seleccionada o pasar a abonada
                    $nuevo_estado = 'aprobada';
                    $email_aprobacion_enviado = false;
                    
                    if ($reserva['estado'] == 'aprobada') {
                        // Si ya está aprobada, pasar a abonada
                        $nuevo_estado = 'abonada';
                    } else {
                        // Si está pendiente, aprobar y enviar correo
                        $nuevo_estado = 'aprobada';
                        if (!isset($reserva['email_enviado']) || !$reserva['email_enviado']) {
                            $email_aprobacion_enviado = enviarEmailAprobacion($reserva);
                            
                            if ($email_aprobacion_enviado) {
                                $update_email = "UPDATE reservas SET email_enviado = TRUE WHERE id_reserva = ?";
                                $stmt_email = $db->prepare($update_email);
                                $stmt_email->execute([$id]);
                            }
                        }
                    }
                    
                    $query = "UPDATE reservas SET estado = ? WHERE id_reserva = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$nuevo_estado, $id]);
                    
                    // Confirmar transacción
                    $db->commit();
                    
                    // Construir mensaje
                    if ($nuevo_estado == 'abonada') {
                        $mensaje = 'Reserva marcada como ABONADA exitosamente.';
                    } else {
                        $mensaje = 'Reserva aprobada exitosamente.';
                        if ($reservas_rechazadas > 0) {
                            $mensaje .= " Se rechazaron automáticamente $reservas_rechazadas reserva(s) solapada(s).";
                            if ($emails_enviados > 0) {
                                $mensaje .= " Se enviaron $emails_enviados correo(s) de notificación.";
                            }
                        }
                        if ($email_aprobacion_enviado) {
                            $mensaje .= ' Se envió email de aprobación al cliente.';
                        } elseif (isset($reserva['email_enviado']) && $reserva['email_enviado']) {
                            $mensaje .= ' Email de aprobación ya fue enviado anteriormente.';
                        }
                    }
                    
                    $tipo_mensaje = 'success';
                    
                } catch (Exception $e) {
                    $db->rollback();
                    throw $e;
                }
                break;
                
            case 'reject':
                // Obtener datos de la reserva antes de rechazar
                $query_reserva = "SELECT * FROM reservas WHERE id_reserva = ?";
                $stmt_reserva = $db->prepare($query_reserva);
                $stmt_reserva->execute([$id]);
                $reserva = $stmt_reserva->fetch(PDO::FETCH_ASSOC);
                
                if (!$reserva) {
                    throw new Exception('Reserva no encontrada');
                }
                
                // Actualizar estado
                $query = "UPDATE reservas SET estado = 'rechazada' WHERE id_reserva = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$id]);
                
                // Enviar email de rechazo
                $email_enviado = false;
                try {
                    if (class_exists('GmailSender')) {
                        $emailSender = new GmailSender();
                        $email_enviado = $emailSender->sendReservaRechazadaManual($reserva);
                    } else {
                        error_log("Advertencia: GmailSender no disponible para enviar email de rechazo");
                    }
                } catch (Exception $e) {
                    error_log("Error enviando email de rechazo: " . $e->getMessage());
                    error_log("Stack trace: " . $e->getTraceAsString());
                } catch (Error $e) {
                    error_log("Error fatal enviando email de rechazo: " . $e->getMessage());
                    error_log("Stack trace: " . $e->getTraceAsString());
                }
                
                $mensaje = 'Reserva rechazada';
                if ($email_enviado) {
                    $mensaje .= '. Se envió correo de notificación al cliente.';
                }
                $tipo_mensaje = 'warning';
                break;
                
            case 'cancel':
                // Obtener datos de la reserva antes de cancelar
                $query_reserva = "SELECT * FROM reservas WHERE id_reserva = ?";
                $stmt_reserva = $db->prepare($query_reserva);
                $stmt_reserva->execute([$id]);
                $reserva = $stmt_reserva->fetch(PDO::FETCH_ASSOC);
                
                if (!$reserva) {
                    throw new Exception('Reserva no encontrada');
                }
                
                // Actualizar estado
                $query = "UPDATE reservas SET estado = 'cancelada' WHERE id_reserva = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$id]);
                
                // Enviar email de cancelación
                $email_enviado = false;
                try {
                    if (class_exists('GmailSender')) {
                        $emailSender = new GmailSender();
                        $email_enviado = $emailSender->sendReservaCancelada($reserva);
                    } else {
                        error_log("Advertencia: GmailSender no disponible para enviar email de cancelación");
                    }
                } catch (Exception $e) {
                    error_log("Error enviando email de cancelación: " . $e->getMessage());
                    error_log("Stack trace: " . $e->getTraceAsString());
                } catch (Error $e) {
                    error_log("Error fatal enviando email de cancelación: " . $e->getMessage());
                    error_log("Stack trace: " . $e->getTraceAsString());
                }
                
                $mensaje = 'Reserva cancelada';
                if ($email_enviado) {
                    $mensaje .= '. Se envió correo de notificación al cliente.';
                }
                $tipo_mensaje = 'info';
                break;
        }
        
        } catch (Exception $e) {
            error_log("Error en procesamiento de acción: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $mensaje = 'Error: ' . $e->getMessage();
            $tipo_mensaje = 'danger';
        } catch (Error $e) {
            error_log("Error fatal en procesamiento de acción: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $mensaje = 'Error fatal del sistema. Por favor contacte al administrador.';
            $tipo_mensaje = 'danger';
        }
    }
}

// Obtener filtros
$estado_filtro = $_GET['estado'] ?? '';
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';
$palmira_filtro = $_GET['palmira'] ?? '';

// Construir consulta
$where_conditions = [];
$params = [];

if ($estado_filtro) {
    if ($estado_filtro === 'pagada') {  
        // Para "pagada" necesitamos filtrar por estado = 'aprobada' AND estado_pago = 'pagada'
        $where_conditions[] = "r.estado = ? AND r.estado_pago = ?";
        $params[] = 'aprobada';
        $params[] = 'pagada';
    } elseif ($estado_filtro === 'aprobada') {
        // Para "aprobada" solo mostrar las aprobadas pero NO pagadas y NO abonadas
        $where_conditions[] = "r.estado = ? AND r.estado_pago = ?";
        $params[] = 'aprobada';
        $params[] = 'pendiente';
    } elseif ($estado_filtro === 'abonada') {
        // Para "abonada" mostrar solo las abonadas
        $where_conditions[] = "r.estado = ?";
        $params[] = 'abonada';
    } else {
        $where_conditions[] = "r.estado = ?";
        $params[] = $estado_filtro;
    }
}

if ($fecha_desde) {
    $where_conditions[] = "r.fecha_entrada >= ?";
    $params[] = $fecha_desde;
}

if ($fecha_hasta) {
    $where_conditions[] = "r.fecha_entrada <= ?";
    $params[] = $fecha_hasta;
}

if ($palmira_filtro === '1' || $palmira_filtro === 'true') {
    $where_conditions[] = "r.vive_palmira = 1";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Obtener reservas (usar GROUP BY para evitar duplicados)
$query = "SELECT r.*, a.nombre as apartamento_nombre 
          FROM reservas r 
          LEFT JOIN apartamentos a ON r.id_apartamento = a.id_apartamento 
          $where_clause 
          GROUP BY r.id_reserva
          ORDER BY r.creado_en DESC";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Error de conexión a la base de datos");
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $reservas = [];
    $mensaje = 'Error al cargar reservas: ' . $e->getMessage();
    $tipo_mensaje = 'danger';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reservas - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oxygen:wght@300;400;700&family=Crimson+Text:wght@400;600;700&family=Abril+Fatface&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        * {
            font-family: 'Oxygen', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Crimson Text', serif;
            color: #333;
        }
        body {
            background-color: #fff;
            font-size: .875rem;
        }
        
        /* Navbar superior como en el dashboard */
        .navbar-top {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            background-color: #212529;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .navbar-brand {
            padding-top: .75rem;
            padding-bottom: .75rem;
            font-size: 1rem;
            background-color: rgba(0, 0, 0, .25);
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
        }
        
        .form-control-dark {
            color: #fff;
            background-color: rgba(255, 255, 255, .1);
            border-color: rgba(255, 255, 255, .1);
        }
        
        .form-control-dark:focus {
            border-color: transparent;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, .25);
        }
        
        .sidebar {
            position: fixed;
            top: 48px;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #f8f9fa;
        }
        
        @media (max-width: 767.98px) {
            .sidebar {
                top: 5rem;
            }
        }
        
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        .sidebar .nav-link {
            font-weight: 500;
            color: #333;
            border-radius: 0;
            padding: 0.5rem 1rem;
            font-size: 1rem;
        }
        
        .sidebar .nav-link:hover {
            color: #2470dc;
            background-color: rgba(0, 0, 0, .05);
        }
        
        .sidebar .nav-link.active {
            color: #2470dc;
            background-color: rgba(0, 0, 0, .05);
        }
        
        .sidebar h4 {
            color: #333;
            font-family: 'Abril Fatface', serif;
            font-weight: 400;
            padding: 1rem;
            margin-bottom: 0;
            font-size: 1.25rem;
        }
        
        .sidebar-heading {
            font-size: .875rem;
            text-transform: uppercase;
            color: #6c757d;
            padding: 0.5rem 1rem;
        }
        
        .sidebar .nav {
            padding: 0;
        }
        
        .sidebar .nav-item {
            margin: 0;
        }
        
        .sidebar hr {
            margin: 0.5rem 0;
            border-color: rgba(0, 0, 0, .1);
        }
        
        .main-content {
            background: #fff;
            min-height: 100vh;
            margin-top: 48px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            background: #fff;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            color: #333;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
        }
        .table thead {
            background: #f8f9fa;
            color: #333;
        }
        .badge {
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        .filter-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .btn-primary {
            background-color: #2470dc;
            border-color: #2470dc;
            color: #fff;
        }
        .btn-primary:hover {
            background-color: #1e5bb8;
            border-color: #1e5bb8;
            color: #fff;
        }
        .text-muted {
            color: #666 !important;
        }
        hr {
            border-color: rgba(0, 0, 0, .1);
        }
        
        /* Zoom aplicado a toda la página - 75% pero ocupando todo el ancho */
        html {
            zoom: 0.75 !important;
            width: 100% !important;
            height: 100% !important;
            overflow-x: hidden !important;
        }
        
        body {
            zoom: 1 !important;
            position: relative !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            min-width: 100% !important;
            max-width: 100% !important;
            min-height: 100% !important;
            overflow-x: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Asegurar que todos los contenedores principales ocupen todo el ancho */
        .container-fluid {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Quitar el backdrop (fondo oscuro) de todos los modales */
        .modal-backdrop {
            background-color: transparent !important;
            opacity: 0 !important;
        }
        
        .modal-backdrop.show {
            background-color: transparent !important;
            opacity: 0 !important;
        }
        
        body.modal-open .modal-backdrop {
            background-color: transparent !important;
            opacity: 0 !important;
        }
    </style>
</head>
<body>
    <!-- Navbar superior -->
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow navbar-top">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="index.php">My Suite In Cartagena</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <input class="form-control form-control-dark w-100" type="text" placeholder="Search" aria-label="Search">
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3" href="/app/controllers/auth/logout.php">Sign out</a>
            </div>
        </div>
    </header>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3 sidebar-sticky">
                    <h4 class="sidebar-heading">
                        <i class="fas fa-plane me-2"></i>
                        Admin Panel
                    </h4>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="reservas.php">
                                <i class="fas fa-calendar-check me-2"></i> Reservas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="calendario.php">
                                <i class="fas fa-calendar-alt me-2"></i> Calendario
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="usuarios.php">
                                <i class="fas fa-users me-2"></i> Usuarios Registrados
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="tarifas.php">
                                <i class="fas fa-dollar-sign me-2"></i> Tarifas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="descuentos.php">
                                <i class="fas fa-percentage me-2"></i> Descuentos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/manillas.php">
                                <i class="fas fa-ring me-2"></i> Manillas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/servicios_adicionales.php">
                                <i class="fas fa-plus me-2"></i> Servicios adicionales
                            </a>
                        </li>
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1">
                            <span>Enlaces</span>
                        </h6>
                        <li class="nav-item">
                            <a class="nav-link" href="/index.php?lang=en" target="_blank">
                                <i class="fas fa-external-link-alt me-2"></i> Ver Sitio Web
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-calendar-check me-2"></i> Gestión de Reservas</h1>
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-outline-primary" onclick="location.reload()" title="Refrescar tabla">
                                <i class="fas fa-sync-alt me-1"></i>Refresh
                            </button>
                            <div class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo date('d/m/Y H:i'); ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php echo htmlspecialchars($mensaje); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Filtros -->
                    <div class="card filter-card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-filter me-2"></i>Filtros
                            </h5>
                            <form method="GET" class="row g-3">
                                <div class="col-md-2">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="">Todos los estados</option>
                                        <option value="pendiente" <?php echo $estado_filtro === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="aprobada" <?php echo $estado_filtro === 'aprobada' ? 'selected' : ''; ?>>Aprobada</option>
                                        <option value="abonada" <?php echo $estado_filtro === 'abonada' ? 'selected' : ''; ?>>Abonada</option>
                                        <option value="pagada" <?php echo $estado_filtro === 'pagada' ? 'selected' : ''; ?>>Pagada</option>
                                        <option value="rechazada" <?php echo $estado_filtro === 'rechazada' ? 'selected' : ''; ?>>Rechazada</option>
                                        <option value="cancelada" <?php echo $estado_filtro === 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="palmira" class="form-label">Ciudad</label>
                                    <select class="form-select" id="palmira" name="palmira">
                                        <option value="">Todas las ciudades</option>
                                        <option value="1" <?php echo $palmira_filtro === '1' || $palmira_filtro === 'true' ? 'selected' : ''; ?>>
                                            <i class="fas fa-map-marker-alt me-1"></i> Solo Palmira
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="fecha_desde" class="form-label">Fecha Desde</label>
                                    <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" value="<?php echo htmlspecialchars($fecha_desde); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                                    <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" value="<?php echo htmlspecialchars($fecha_hasta); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-2"></i>Filtrar
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <a href="reservas.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Limpiar
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Lista de Reservas -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i> Lista de Reservas
                                <span class="badge bg-primary ms-2"><?php echo count($reservas); ?> reservas</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Contacto</th>
                                            <th>Fechas</th>
                                            <th>Huéspedes</th>
                                            <th>Estado</th>
                                            <th>Total</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="reservasTableBody">
                                        <?php if (!empty($reservas)): ?>
                                            <?php foreach ($reservas as $reserva): ?>
                                                <tr>
                                                    <td>#<?php echo $reserva['id_reserva']; ?></td>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($reserva['nombre'] . ' ' . $reserva['apellido']); ?></strong>
                                                            <?php if ($reserva['vive_palmira']): ?>
                                                                <span class="badge bg-info ms-1">Palmira</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <i class="fas fa-envelope me-1"></i>
                                                            <small><?php echo htmlspecialchars($reserva['correo']); ?></small>
                                                            <br>
                                                            <i class="fas fa-phone me-1"></i>
                                                            <small><?php echo htmlspecialchars($reserva['telefono']); ?></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong>Entrada:</strong> <?php 
                                                                $fecha_entrada = DateTime::createFromFormat('Y-m-d', $reserva['fecha_entrada']);
                                                                if (!$fecha_entrada) {
                                                                    $fecha_entrada = new DateTime($reserva['fecha_entrada']);
                                                                }
                                                                echo $fecha_entrada->format('d/m/Y'); 
                                                            ?>
                                                            <br>
                                                            <strong>Salida:</strong> <?php 
                                                                $fecha_salida = DateTime::createFromFormat('Y-m-d', $reserva['fecha_salida']);
                                                                if (!$fecha_salida) {
                                                                    $fecha_salida = new DateTime($reserva['fecha_salida']);
                                                                }
                                                                echo $fecha_salida->format('d/m/Y'); 
                                                            ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <i class="fas fa-user me-1"></i>
                                                            <?php echo $reserva['num_adultos']; ?> adultos
                                                            <?php if ($reserva['num_ninos'] > 0): ?>
                                                                <br>
                                                                <i class="fas fa-child me-1"></i>
                                                                <?php echo $reserva['num_ninos']; ?> niños
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        // Lógica simplificada: solo mostrar un estado
                                                        $estado_class = '';
                                                        $estado_text = '';
                                                        $estado_icon = '';
                                                        
                                                        if ($reserva['estado'] == 'aprobada' && $reserva['estado_pago'] == 'pagada') {
                                                            // Si está aprobada Y pagada, mostrar "Pagada"
                                                            $estado_class = 'bg-success';
                                                            $estado_text = 'Pagada';
                                                            $estado_icon = 'fas fa-check-circle';
                                                        } elseif ($reserva['estado'] == 'abonada') {
                                                            // Si está abonada, mostrar "ABONADA" en naranja
                                                            $estado_class = 'badge';
                                                            $estado_text = 'ABONADA';
                                                            $estado_icon = 'fas fa-money-bill-wave';
                                                            $estado_style = 'background-color: #ff8c00; color: #000000;';
                                                        } elseif ($reserva['estado'] == 'aprobada' && $reserva['estado_pago'] == 'pendiente') {
                                                            // Si está aprobada pero pendiente de pago, mostrar "Aprobada"
                                                            $estado_class = 'bg-primary';
                                                            $estado_text = 'Aprobada';
                                                            $estado_icon = 'fas fa-check';
                                                        } else {
                                                            // Para otros estados, mostrar el estado de la reserva
                                                            switch ($reserva['estado']) {
                                                                case 'pendiente':
                                                                    $estado_class = 'bg-warning';
                                                                    $estado_text = 'Pendiente';
                                                                    $estado_icon = 'fas fa-clock';
                                                                    break;
                                                                case 'rechazada':
                                                                    $estado_class = 'bg-danger';
                                                                    $estado_text = 'Rechazada';
                                                                    $estado_icon = 'fas fa-times';
                                                                    break;
                                                                case 'cancelada':
                                                                    $estado_class = 'bg-secondary';
                                                                    $estado_text = 'Cancelada';
                                                                    $estado_icon = 'fas fa-ban';
                                                                    break;
                                                            }
                                                        }
                                                        ?>
                                                        <span class="badge <?php echo $estado_class; ?>" <?php if (isset($estado_style)) echo 'style="' . $estado_style . '"'; ?>>
                                                            <i class="<?php echo $estado_icon; ?> me-1"></i><?php echo $estado_text; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong>$<?php echo number_format($reserva['total'], 0, ',', '.'); ?> COP</strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                <?php 
                                                                $metodo = trim(strtolower($reserva['metodo_pago'] ?? ''));
                                                                // Debug temporal
                                                                error_log("DEBUG metodo_pago: valor='" . ($reserva['metodo_pago'] ?? 'NULL') . "', normalizado='" . $metodo . "'");
                                                                
                                                                if ($metodo === 'efectivo' || $metodo === 'transferencia') {
                                                                    echo 'Transferencia';
                                                                } elseif ($metodo === 'tarjeta_credito') {
                                                                    echo 'Tarjeta';
                                                                } else {
                                                                    // Mostrar el valor real para debug
                                                                    echo htmlspecialchars($reserva['metodo_pago'] ?? 'N/A');
                                                                }
                                                                ?>
                                                            </small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-outline-info btn-action" 
                                                                    onclick="verDetalle(<?php echo $reserva['id_reserva']; ?>)" title="Ver Detalle">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <?php if (($reserva['estado'] == 'aprobada' && $reserva['estado_pago'] == 'pendiente') || $reserva['estado'] == 'abonada'): ?>
                                                                <button type="button" class="btn btn-outline-success btn-action" 
                                                                        onclick="marcarComprobante(<?php echo $reserva['id_reserva']; ?>)" 
                                                                        title="<?php echo $reserva['estado'] == 'abonada' ? 'Marcar como Pagada' : 'Marcar como Abonada'; ?>">
                                                                    <i class="fas fa-dollar-sign"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                            <?php if ($reserva['estado'] == 'pendiente'): ?>
                                                                <a href="?action=approve&id=<?php echo $reserva['id_reserva']; ?>" 
                                                                   class="btn btn-outline-success btn-action" title="Aprobar"
                                                                   onclick="if(confirm('¿Aprobar esta reserva?')) { event.preventDefault(); realizarAccion('approve', <?php echo $reserva['id_reserva']; ?>); return false; }">
                                                                    <i class="fas fa-check"></i>
                                                                </a>
                                                                <a href="?action=reject&id=<?php echo $reserva['id_reserva']; ?>" 
                                                                   class="btn btn-outline-danger btn-action" title="Rechazar"
                                                                   onclick="if(confirm('¿Rechazar esta reserva?')) { event.preventDefault(); realizarAccion('reject', <?php echo $reserva['id_reserva']; ?>); return false; }">
                                                                    <i class="fas fa-times"></i>
                                                                </a>
                                                            <?php elseif ($reserva['estado'] == 'aprobada' || $reserva['estado'] == 'abonada'): ?>
                                                                <a href="?action=cancel&id=<?php echo $reserva['id_reserva']; ?>" 
                                                                   class="btn btn-outline-warning btn-action" title="Cancelar"
                                                                   onclick="if(confirm('¿Cancelar esta reserva?')) { event.preventDefault(); realizarAccion('cancel', <?php echo $reserva['id_reserva']; ?>); return false; }">
                                                                    <i class="fas fa-ban"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <br>No se encontraron reservas
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal de Detalle de Reserva -->
    <div class="modal fade" id="detalleReservaModal" tabindex="-1" aria-labelledby="detalleReservaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detalleReservaModalLabel">
                        <i class="fas fa-calendar-check me-2"></i>Detalle de Reserva
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="detalleReservaContent">
                        <!-- El contenido se carga dinámicamente -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btnEditarReserva" onclick="activarEdicionReserva()">
                        <i class="fas fa-edit me-1"></i> Editar
                    </button>
                    <button type="button" class="btn btn-success d-none" id="btnGuardarReserva" onclick="guardarCambiosReserva()">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                    <button type="button" class="btn btn-secondary d-none" id="btnCancelarEdicion" onclick="cancelarEdicionReserva()">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Pago -->
    <div class="modal fade" id="comprobanteModal" tabindex="-1" aria-labelledby="comprobanteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="comprobanteModalLabel">
                        <i class="fas fa-dollar-sign me-2"></i>Confirmar Pago
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="reservaIdComprobante" name="reserva_id">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Instrucciones:</strong> El cliente debe enviar el comprobante de pago al correo: <strong>gerencia@mysuiteincartagena.com.co</strong>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notasPago" class="form-label">
                            <i class="fas fa-sticky-note me-2"></i>Notas sobre el Pago (Opcional)
                        </label>
                        <textarea class="form-control" id="notasPago" name="notas" rows="3" placeholder="Información adicional sobre el pago (método, fecha, etc.)..."></textarea>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Importante:</strong> Al confirmar el pago, la reserva se marcará como PAGADA y se enviará un email de confirmación al cliente.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnConfirmarPago" onclick="confirmarPago()">
                        <i class="fas fa-check-circle me-2"></i>Confirmar Pago
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verDetalle(id) {
            // Mostrar loading
            document.getElementById('detalleReservaContent').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando detalles de la reserva...</p>
                </div>
            `;
            
            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('detalleReservaModal'));
            modal.show();
            
            // Cargar datos de la reserva
            fetch(`../../../app/api/admin/get_reserva_detalle.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarDetalleReserva(data.reserva);
                    } else {
                        document.getElementById('detalleReservaContent').innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error al cargar los detalles: ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    document.getElementById('detalleReservaContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error de conexión: ${error.message}
                        </div>
                    `;
                });
        }
        
        let reservaActual = null; // Variable global para almacenar los datos de la reserva
        let modoEdicion = false; // Variable para controlar el modo de edición
        
        function mostrarDetalleReserva(reserva) {
            // Guardar los datos originales de la reserva
            reservaActual = reserva;
            modoEdicion = false;
            
            // Resetear botones
            document.getElementById('btnEditarReserva').classList.remove('d-none');
            document.getElementById('btnGuardarReserva').classList.add('d-none');
            document.getElementById('btnCancelarEdicion').classList.add('d-none');
            
            let estadoClass = 'bg-secondary';
            let estadoStyle = '';
            if (reserva.estado === 'abonada') {
                estadoClass = 'badge';
                estadoStyle = 'background-color: #ff8c00; color: #000000;';
            } else {
                const estadoClasses = {
                    'pendiente': 'bg-warning',
                    'aprobada': 'bg-success',
                    'rechazada': 'bg-danger',
                    'cancelada': 'bg-secondary'
                };
                estadoClass = estadoClasses[reserva.estado] || 'bg-secondary';
            }
            
            // Debug temporal
            console.log('DEBUG metodo_pago recibido:', reserva.metodo_pago, 'tipo:', typeof reserva.metodo_pago);
            
            // Normalizar el valor: convertir a string, lowercase, y eliminar espacios
            const metodoPagoValor = String(reserva.metodo_pago || '').toLowerCase().trim().replace(/\s+/g, '');
            console.log('DEBUG metodoPagoValor normalizado:', metodoPagoValor, 'longitud:', metodoPagoValor.length);
            
            // Comparar con diferentes posibles valores
            let metodoPago = 'Tarjeta de Crédito'; // Por defecto
            if (metodoPagoValor === 'efectivo' || 
                metodoPagoValor === 'transferencia' ||
                metodoPagoValor.includes('efectivo') ||
                metodoPagoValor.includes('transferencia')) {
                metodoPago = 'Transferencia';
            }
            
            console.log('DEBUG metodoPago final:', metodoPago);
            const vivePalmira = reserva.vive_palmira == 1 ? 'Sí' : 'No';
            
            renderizarDetalleReserva(reserva, estadoClass, estadoStyle, metodoPago, vivePalmira);
        }
        
        function renderizarDetalleReserva(reserva, estadoClass, estadoStyle, metodoPago, vivePalmira) {
            // Separar nombre y apellido si vienen juntos
            let nombre = reserva.nombre || '';
            let apellido = reserva.apellido || '';
            
            // Si no hay apellido separado, intentar separar del nombre completo
            if (!apellido && nombre.includes(' ')) {
                const partes = nombre.split(' ');
                nombre = partes[0];
                apellido = partes.slice(1).join(' ');
            }
            
            const nombreDisplay = modoEdicion 
                ? `<input type="text" class="form-control form-control-sm" id="editNombre" value="${nombre}" required>`
                : `${nombre}`;
            const apellidoDisplay = modoEdicion 
                ? `<input type="text" class="form-control form-control-sm" id="editApellido" value="${apellido}" required>`
                : `${apellido}`;
            const correoDisplay = modoEdicion 
                ? `<input type="email" class="form-control form-control-sm" id="editCorreo" value="${reserva.correo}" required>`
                : `${reserva.correo}`;
            const telefonoDisplay = modoEdicion 
                ? `<input type="text" class="form-control form-control-sm" id="editTelefono" value="${reserva.telefono}" required>`
                : `${reserva.telefono}`;
            const totalDisplay = modoEdicion 
                ? `<input type="number" class="form-control form-control-sm" id="editTotal" value="${reserva.total}" min="0" step="0.01" required>`
                : `$${parseFloat(reserva.total).toLocaleString('es-CO')} COP`;
            
            document.getElementById('detalleReservaContent').innerHTML = `
                <div class="row">
                    <!-- Información General -->
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-user me-2"></i>Información del Cliente
                        </h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Nombre:</strong></td>
                                <td>${nombreDisplay}</td>
                            </tr>
                            <tr>
                                <td><strong>Apellido:</strong></td>
                                <td>${apellidoDisplay}</td>
                            </tr>
                            <tr>
                                <td><strong>Correo:</strong></td>
                                <td>${correoDisplay}</td>
                            </tr>
                            <tr>
                                <td><strong>Teléfono:</strong></td>
                                <td>${telefonoDisplay}</td>
                            </tr>
                            <tr>
                                <td><strong>Fecha de Nacimiento:</strong></td>
                                <td>${reserva.fecha_nacimiento || 'No especificada'}</td>
                            </tr>
                            <tr>
                                <td><strong>Vive en Palmira:</strong></td>
                                <td>
                                    ${vivePalmira}
                                    ${reserva.vive_palmira == 1 ? '<span class="badge bg-info ms-2">Transporte gratis</span>' : ''}
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Información de la Reserva -->
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-calendar me-2"></i>Detalles de la Reserva
                        </h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>ID Reserva:</strong></td>
                                <td>#${reserva.id_reserva}</td>
                            </tr>
                            <tr>
                                <td><strong>Estado:</strong></td>
                                <td><span class="badge ${estadoClass}" ${estadoStyle ? `style="${estadoStyle}"` : ''}>${reserva.estado === 'abonada' ? 'Abonada' : reserva.estado.charAt(0).toUpperCase() + reserva.estado.slice(1)}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Fecha de Entrada:</strong></td>
                                <td>${reserva.fecha_entrada_formatted || reserva.fecha_entrada}</td>
                            </tr>
                            <tr>
                                <td><strong>Fecha de Salida:</strong></td>
                                <td>${reserva.fecha_salida_formatted || reserva.fecha_salida}</td>
                            </tr>
                            <tr>
                                <td><strong>Adultos:</strong></td>
                                <td>${reserva.num_adultos}</td>
                            </tr>
                            <tr>
                                <td><strong>Niños:</strong></td>
                                <td>${reserva.num_ninos}</td>
                            </tr>
                            <tr>
                                <td><strong>Método de Pago:</strong></td>
                                <td>${metodoPago}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <hr>
                
                <!-- Información Financiera -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-dollar-sign me-2"></i>Información Financiera
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Costo Base:</strong></td>
                                        <td>$${parseFloat(reserva.costo_base).toLocaleString('es-CO')} COP</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Descuento Fidelización:</strong></td>
                                        <td>$${parseFloat(reserva.descuento_fidelizacion || 0).toLocaleString('es-CO')} COP</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Descuento Cumpleaños:</strong></td>
                                        <td>$${parseFloat(reserva.descuento_cumpleanios || 0).toLocaleString('es-CO')} COP</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Descuento Promocional:</strong></td>
                                        <td>$${parseFloat(reserva.descuento_promocional || 0).toLocaleString('es-CO')} COP</td>
                                    </tr>
                                    <tr class="table-success">
                                        <td><strong>Total:</strong></td>
                                        <td><strong>${totalDisplay}</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                ${reserva.comentario ? `
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-comment me-2"></i>Comentarios
                        </h6>
                        <div class="alert alert-light">
                            ${reserva.comentario}
                        </div>
                    </div>
                </div>
                ` : ''}
                
                ${reserva.comprobante_pago ? `
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-dollar-sign me-2"></i>Comprobante de Pago
                        </h6>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Pago Confirmado:</strong> ${reserva.fecha_pago_confirmado ? new Date(reserva.fecha_pago_confirmado).toLocaleString('es-CO') : 'Fecha no disponible'}
                        </div>
                        <div class="text-center">
                            <a href="view_comprobante.php?id=${reserva.id_reserva}&file=${reserva.comprobante_pago}" target="_blank" class="btn btn-outline-primary">
                                <i class="fas fa-eye me-2"></i>Ver Comprobante
                            </a>
                        </div>
                    </div>
                </div>
                ` : ''}
                
                <hr>
                
                <!-- Información del Sistema -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-info-circle me-2"></i>Información del Sistema
                        </h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Fecha de Creación:</strong></td>
                                <td>${new Date(reserva.creado_en).toLocaleString('es-CO')}</td>
                            </tr>
                            <tr>
                                <td><strong>Última Actualización:</strong></td>
                                <td>${new Date(reserva.actualizado_en).toLocaleString('es-CO')}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            `;
        }
        
        // Función para activar modo de edición
        function activarEdicionReserva() {
            modoEdicion = true;
            
            // Ocultar botón editar, mostrar guardar y cancelar
            document.getElementById('btnEditarReserva').classList.add('d-none');
            document.getElementById('btnGuardarReserva').classList.remove('d-none');
            document.getElementById('btnCancelarEdicion').classList.remove('d-none');
            
            // Re-renderizar con campos editables
            if (reservaActual) {
                let estadoClass = 'bg-secondary';
                let estadoStyle = '';
                if (reservaActual.estado === 'abonada') {
                    estadoClass = 'badge';
                    estadoStyle = 'background-color: #ff8c00; color: #000000;';
                } else {
                    const estadoClasses = {
                        'pendiente': 'bg-warning',
                        'aprobada': 'bg-success',
                        'rechazada': 'bg-danger',
                        'cancelada': 'bg-secondary'
                    };
                    estadoClass = estadoClasses[reservaActual.estado] || 'bg-secondary';
                }
                
                const metodoPagoValor = String(reservaActual.metodo_pago || '').toLowerCase().trim().replace(/\s+/g, '');
                let metodoPago = 'Tarjeta de Crédito';
                if (metodoPagoValor === 'efectivo' || metodoPagoValor === 'transferencia' ||
                    metodoPagoValor.includes('efectivo') || metodoPagoValor.includes('transferencia')) {
                    metodoPago = 'Transferencia';
                }
                
                const vivePalmira = reservaActual.vive_palmira == 1 ? 'Sí' : 'No';
                renderizarDetalleReserva(reservaActual, estadoClass, estadoStyle, metodoPago, vivePalmira);
            }
        }
        
        // Función para cancelar edición
        function cancelarEdicionReserva() {
            modoEdicion = false;
            
            // Mostrar botón editar, ocultar guardar y cancelar
            document.getElementById('btnEditarReserva').classList.remove('d-none');
            document.getElementById('btnGuardarReserva').classList.add('d-none');
            document.getElementById('btnCancelarEdicion').classList.add('d-none');
            
            // Re-renderizar con valores originales
            if (reservaActual) {
                mostrarDetalleReserva(reservaActual);
            }
        }
        
        // Función para guardar cambios
        function guardarCambiosReserva() {
            if (!reservaActual) {
                alert('Error: No hay datos de reserva cargados');
                return;
            }
            
            // Obtener valores de los campos
            const nombre = document.getElementById('editNombre').value.trim();
            const apellido = document.getElementById('editApellido').value.trim();
            const correo = document.getElementById('editCorreo').value.trim();
            const telefono = document.getElementById('editTelefono').value.trim();
            const total = parseFloat(document.getElementById('editTotal').value);
            
            // Validaciones
            if (!nombre || !apellido) {
                alert('El nombre y apellido son obligatorios');
                return;
            }
            
            if (!correo || !correo.includes('@')) {
                alert('Por favor ingrese un correo electrónico válido');
                return;
            }
            
            if (!telefono) {
                alert('El teléfono es obligatorio');
                return;
            }
            
            if (isNaN(total) || total < 0) {
                alert('Por favor ingrese un total válido (mayor o igual a 0)');
                return;
            }
            
            // Crear FormData para enviar
            const formData = new FormData();
            formData.append('id_reserva', reservaActual.id_reserva);
            formData.append('nombre', nombre);
            formData.append('apellido', apellido);
            formData.append('correo', correo);
            formData.append('telefono', telefono);
            formData.append('total', total);
            
            // Mostrar indicador de carga
            const btnGuardar = document.getElementById('btnGuardarReserva');
            const textoOriginal = btnGuardar.innerHTML;
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
            
            // Enviar datos al servidor
            fetch('../../../app/api/admin/update_reserva_datos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = textoOriginal;
                
                if (data.success) {
                    // Actualizar los datos locales
                    reservaActual.nombre = nombre;
                    reservaActual.apellido = apellido;
                    reservaActual.correo = correo;
                    reservaActual.telefono = telefono;
                    reservaActual.total = total;
                    reservaActual.actualizado_en = new Date().toISOString();
                    
                    // Mostrar mensaje de éxito
                    alert('Datos actualizados exitosamente');
                    
                    // Salir del modo edición y refrescar la vista
                    cancelarEdicionReserva();
                    
                    // Recargar la tabla de reservas para reflejar los cambios
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    alert('Error al actualizar: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = textoOriginal;
                console.error('Error:', error);
                alert('Error de conexión al guardar los cambios');
            });
        }
        
        // Función para realizar acciones (aprobar, rechazar, cancelar) sin recargar
        function realizarAccion(accion, id) {
            // Hacer la petición (la confirmación ya se hizo en el onclick)
            fetch(`reservas.php?action=${accion}&id=${id}`, {
                method: 'GET',
                credentials: 'same-origin'
            })
                .then(response => {
                    // Recargar toda la página para mostrar los mensajes del servidor
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Recargar la página de todas formas para mostrar cualquier mensaje de error
                    window.location.reload();
                });
        }
        
        // Función para recargar la tabla de reservas
        function recargarTablaReservas() {
            // Obtener los parámetros actuales de la URL
            const urlParams = new URLSearchParams(window.location.search);
            const estado = urlParams.get('estado') || '';
            const fecha_desde = urlParams.get('fecha_desde') || '';
            const fecha_hasta = urlParams.get('fecha_hasta') || '';
            const palmira = urlParams.get('palmira') || '';
            
            // Construir URL con los mismos filtros
            let url = 'reservas.php?';
            if (estado) url += 'estado=' + encodeURIComponent(estado) + '&';
            if (fecha_desde) url += 'fecha_desde=' + encodeURIComponent(fecha_desde) + '&';
            if (fecha_hasta) url += 'fecha_hasta=' + encodeURIComponent(fecha_hasta) + '&';
            if (palmira) url += 'palmira=' + encodeURIComponent(palmira) + '&';
            
            // Hacer fetch para obtener solo el contenido de la tabla
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    // Crear un elemento temporal para parsear el HTML
                    const temp = document.createElement('div');
                    temp.innerHTML = html;
                    
                    // Extraer el tbody de la tabla
                    const newTbody = temp.querySelector('#reservasTableBody');
                    const newBadge = temp.querySelector('.card-header .badge');
                    
                    if (newTbody) {
                        // Reemplazar el tbody actual
                        const currentTbody = document.getElementById('reservasTableBody');
                        if (currentTbody && currentTbody.parentNode) {
                            currentTbody.parentNode.replaceChild(newTbody, currentTbody);
                        }
                    }
                    
                    // Actualizar el contador de reservas
                    if (newBadge) {
                        const currentBadge = document.querySelector('.card-header .badge');
                        if (currentBadge) {
                            currentBadge.textContent = newBadge.textContent;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error al recargar tabla:', error);
                    // Si falla, recargar toda la página como fallback
                    location.reload();
                });
        }
        
        function marcarComprobante(id) {
            document.getElementById('reservaIdComprobante').value = id;
            document.getElementById('notasPago').value = '';
            
            const modal = new bootstrap.Modal(document.getElementById('comprobanteModal'));
            modal.show();
        }
        
        function confirmarPago() {
            const reservaId = document.getElementById('reservaIdComprobante').value;
            const notas = document.getElementById('notasPago').value;
            
            if (!reservaId) {
                alert('Error: ID de reserva no encontrado');
                return;
            }
            
            // Mostrar loading
            const submitBtn = document.getElementById('btnConfirmarPago');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
            submitBtn.disabled = true;
            
            // Crear datos para enviar
            const formData = new FormData();
            formData.append('reserva_id', reservaId);
            formData.append('notas', notas);
            formData.append('marcar_pagada', '1');
            
            fetch('../../../app/api/admin/marcar_pagada.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('comprobanteModal'));
                    if (modal) {
                        modal.hide();
                    }
                    // Recargar solo la tabla en lugar de toda la página
                    recargarTablaReservas();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión: ' + error.message);
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }
    </script>

    <!-- Modales para nuevas funcionalidades -->
    
    <!-- Modal Clientes Palmira -->
    <div class="modal fade" id="palmiraModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        Clientes que viven en Palmira
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="palmiraTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Fecha Registro</th>
                                    <th>Reservas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargarán via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bloqueo de Fechas -->
    <div class="modal fade" id="dateBlockingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-times me-2"></i>
                        Bloquear Fechas Manualmente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="dateBlockingForm">
                        <div class="mb-3">
                            <label for="blockStartDate" class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control" id="blockStartDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="blockEndDate" class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" id="blockEndDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="blockReason" class="form-label">Motivo del Bloqueo</label>
                            <select class="form-control" id="blockReason" required>
                                <option value="">Seleccionar motivo...</option>
                                <option value="mantenimiento">Mantenimiento</option>
                                <option value="uso_interno">Uso Interno</option>
                                <option value="evento_especial">Evento Especial</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="blockDescription" class="form-label">Descripción (opcional)</label>
                            <textarea class="form-control" id="blockDescription" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="blockDates()">
                        <i class="fas fa-lock me-2"></i>Bloquear Fechas
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Gestión de Clientes -->
    <div class="modal fade" id="clientManagementModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-users me-2"></i>
                        Usuarios Registrados
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="clientSearch" placeholder="Buscar cliente por nombre o email...">
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-primary" onclick="searchClients()">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="clientsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Fecha Nacimiento</th>
                                    <th>Reservas</th>
                                    <th>Descuentos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargarán via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript para las nuevas funcionalidades -->
    <script>
        // Esperar a que el DOM esté listo
        $(document).ready(function() {
            console.log('DOM cargado, funciones disponibles');
        });
        
        // Función eliminada - el filtro de Palmira ahora está en los filtros de reservas

        // Mostrar modal de bloqueo de fechas
        function showDateBlocking() {
            console.log('Ejecutando showDateBlocking');
            $('#dateBlockingModal').modal('show');
        }

        // Mostrar modal de gestión de clientes
        function showClientManagement() {
            console.log('Ejecutando showClientManagement');
            $('#clientManagementModal').modal('show');
            loadAllClients();
        }

        // Cargar clientes de Palmira
        function loadPalmiraClients() {
            fetch('../../../app/api/admin/get_palmira_clients.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.querySelector('#palmiraTable tbody');
                    tbody.innerHTML = '';
                    
                    if (data.success && data.clients.length > 0) {
                        data.clients.forEach(client => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${client.id_usuario}</td>
                                <td>${client.nombre} ${client.apellido}</td>
                                <td>${client.correo}</td>
                                <td>${client.telefono}</td>
                                <td>${client.fecha_registro}</td>
                                <td>${client.total_reservas}</td>
                            `;
                            tbody.appendChild(row);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No hay clientes registrados en Palmira</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.querySelector('#palmiraTable tbody').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error al cargar los datos</td></tr>';
                });
        }

        // Cargar todos los clientes
        function loadAllClients() {
            fetch('../../../app/api/admin/get_all_clients.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.querySelector('#clientsTable tbody');
                    tbody.innerHTML = '';
                    
                    if (data.success && data.clients.length > 0) {
                        data.clients.forEach(client => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${client.id_usuario}</td>
                                <td>${client.nombre} ${client.apellido}</td>
                                <td>${client.correo}</td>
                                <td>${client.telefono}</td>
                                <td>${client.fecha_nacimiento || 'No registrada'}</td>
                                <td>${client.total_reservas}</td>
                                <td>
                                    <span class="badge bg-success">Fidelidad: 5%</span>
                                    <span class="badge bg-warning">Cumpleaños: 30%</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="viewClientDetails(${client.id_usuario})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No hay clientes registrados</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.querySelector('#clientsTable tbody').innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error al cargar los datos</td></tr>';
                });
        }

        // Bloquear fechas
        function blockDates() {
            const startDate = document.getElementById('blockStartDate').value;
            const endDate = document.getElementById('blockEndDate').value;
            const reason = document.getElementById('blockReason').value;
            const description = document.getElementById('blockDescription').value;

            if (!startDate || !endDate || !reason) {
                alert('Por favor complete todos los campos obligatorios');
                return;
            }

            const formData = new FormData();
            formData.append('start_date', startDate);
            formData.append('end_date', endDate);
            formData.append('reason', reason);
            formData.append('description', description);

            fetch('../../../app/api/admin/block_dates.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Fechas bloqueadas exitosamente');
                    $('#dateBlockingModal').modal('hide');
                    document.getElementById('dateBlockingForm').reset();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al bloquear las fechas');
            });
        }

        // Buscar clientes
        function searchClients() {
            const searchTerm = document.getElementById('clientSearch').value;
            if (!searchTerm.trim()) {
                loadAllClients();
                return;
            }

            fetch(`search_clients.php?q=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.querySelector('#clientsTable tbody');
                    tbody.innerHTML = '';
                    
                    if (data.success && data.clients.length > 0) {
                        data.clients.forEach(client => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${client.id_usuario}</td>
                                <td>${client.nombre} ${client.apellido}</td>
                                <td>${client.correo}</td>
                                <td>${client.telefono}</td>
                                <td>${client.fecha_nacimiento || 'No registrada'}</td>
                                <td>${client.total_reservas}</td>
                                <td>
                                    <span class="badge bg-success">Fidelidad: 5%</span>
                                    <span class="badge bg-warning">Cumpleaños: 30%</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="viewClientDetails(${client.id_usuario})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No se encontraron clientes</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.querySelector('#clientsTable tbody').innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error al buscar clientes</td></tr>';
                });
        }

        // Ver detalles del cliente
        function viewClientDetails(clientId) {
            console.log('Cargando detalles del cliente ID:', clientId);
            
            // Crear modal dinámico
            const modalHtml = `
                <div class="modal fade" id="clientDetailsModal" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-user me-2"></i>
                                    Detalles del Cliente
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" id="clientDetailsContent">
                                <div class="text-center">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="mt-2">Cargando detalles del cliente...</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remover modal anterior si existe
            const existingModal = document.getElementById('clientDetailsModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Agregar modal al DOM
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('clientDetailsModal'));
            modal.show();
            
            // Cargar detalles del cliente
            loadClientDetails(clientId);
        }
        
        // Cargar detalles del cliente
        function loadClientDetails(clientId) {
            fetch(`get_client_details.php?id=${clientId}`)
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('clientDetailsContent');
                    
                    if (data.success) {
                        const client = data.client;
                        const reservations = data.reservations || [];
                        
                        content.innerHTML = `
                            <div class="row mb-4">
                                <div class="col-lg-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>Información Personal</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td class="fw-bold" style="width: 40%;">ID:</td>
                                                    <td><span class="badge bg-secondary">${client.id_usuario}</span></td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Nombre:</td>
                                                    <td>${client.nombre} ${client.apellido}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Email:</td>
                                                    <td><a href="mailto:${client.correo}" class="text-decoration-none">${client.correo}</a></td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Teléfono:</td>
                                                    <td>${client.telefono || '<span class="text-muted">No registrado</span>'}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Ciudad:</td>
                                                    <td><span class="badge bg-info">${client.ciudad || 'No registrada'}</span></td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Fecha Nacimiento:</td>
                                                    <td>${client.fecha_nacimiento || '<span class="text-muted">No registrada</span>'}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Fecha Registro:</td>
                                                    <td>${client.fecha_registro}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Estadísticas</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row text-center mb-3">
                                                <div class="col-4">
                                                    <div class="border rounded p-2">
                                                        <h4 class="text-primary mb-0">${client.total_reservas}</h4>
                                                        <small class="text-muted">Total Reservas</small>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="border rounded p-2">
                                                        <h4 class="text-success mb-0">${client.reservas_confirmadas || 0}</h4>
                                                        <small class="text-muted">Confirmadas</small>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="border rounded p-2">
                                                        <h4 class="text-warning mb-0">${client.reservas_pendientes || 0}</h4>
                                                        <small class="text-muted">Pendientes</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                            <h6 class="fw-bold mb-3">Descuentos Aplicables:</h6>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <span class="badge bg-success fs-6">Fidelidad: 5%</span>
                                                <span class="badge bg-warning fs-6">Cumpleaños: 30%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Historial de Reservas</h6>
                                        </div>
                                        <div class="card-body">
                                            ${reservations.length > 0 ? `
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>ID Reserva</th>
                                                                <th>Fecha Entrada</th>
                                                                <th>Fecha Salida</th>
                                                                <th>Estado</th>
                                                                <th>Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            ${reservations.map(res => `
                                                                <tr>
                                                                    <td><span class="badge bg-secondary">${res.id_reserva}</span></td>
                                                                    <td>${res.fecha_entrada}</td>
                                                                    <td>${res.fecha_salida}</td>
                                                                    <td><span class="badge ${res.estado === 'confirmada' ? 'bg-success' : res.estado === 'pendiente' ? 'bg-warning' : 'bg-secondary'}">${res.estado}</span></td>
                                                                    <td class="fw-bold">$${parseFloat(res.total).toLocaleString('es-CO')} COP</td>
                                                                </tr>
                                                            `).join('')}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            ` : '<div class="text-center py-4"><i class="fas fa-calendar-times fa-3x text-muted mb-3"></i><p class="text-muted fs-5">No hay reservas registradas</p></div>'}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        content.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error al cargar los detalles: ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('clientDetailsContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error al cargar los detalles del cliente
                        </div>
                    `;
                });
        }
    </script>
</body>
</html>

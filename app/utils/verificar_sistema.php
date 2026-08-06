<?php
/**
 * Verificación del Sistema - My Suite In Cartagena
 * Verifica que todos los componentes estén funcionando
 */

session_start();

// Verificar si el usuario está logueado como admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../../app/controllers/auth/login.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

$errores = [];
$exitos = [];

// Verificar conexión a base de datos
try {
    $db = (new Database())->getConnection();
    $exitos[] = "✅ Conexión a base de datos exitosa";
} catch (Exception $e) {
    $errores[] = "❌ Error de conexión a BD: " . $e->getMessage();
}

// Verificar columnas de comprobantes
try {
    $query = "SHOW COLUMNS FROM reservas LIKE 'comprobante_pago'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $column_exists = $stmt->fetch();
    
    if ($column_exists) {
        $exitos[] = "✅ Columna 'comprobante_pago' existe";
    } else {
        $errores[] = "❌ Columna 'comprobante_pago' no existe";
    }
} catch (Exception $e) {
    $errores[] = "❌ Error verificando columnas: " . $e->getMessage();
}

// Verificar directorio de uploads
$upload_dir = '../uploads/comprobantes/';
if (is_dir($upload_dir)) {
    $exitos[] = "✅ Directorio de uploads existe";
    
    if (is_writable($upload_dir)) {
        $exitos[] = "✅ Directorio de uploads es escribible";
    } else {
        $errores[] = "❌ Directorio de uploads no es escribible";
    }
} else {
    $errores[] = "❌ Directorio de uploads no existe";
}

// Verificar archivo .htaccess
$htaccess_file = '../uploads/.htaccess';
if (file_exists($htaccess_file)) {
    $exitos[] = "✅ Archivo .htaccess de protección existe";
} else {
    $errores[] = "❌ Archivo .htaccess de protección no existe";
}

// Verificar PHPMailer
if (file_exists('../vendor/autoload.php')) {
    $exitos[] = "✅ PHPMailer instalado";
} else {
    $errores[] = "❌ PHPMailer no está instalado";
}

// Verificar archivos principales
$archivos_principales = [
    '../../index.php' => 'Página principal del calendario',
    '../../app/services/process_reservation.php' => 'Procesamiento de reservas',
    'index.php' => 'Dashboard admin',
    '../../app/controllers/auth/login.php' => 'Login principal',
    'reservas.php' => 'Gestión de reservas',
    'upload_comprobante.php' => 'Subida de comprobantes',
    'view_comprobante.php' => 'Visor de comprobantes',
    '../includes/functions.php' => 'Funciones del sistema',
    '../includes/GmailSender.php' => 'Envío de emails'
];

foreach ($archivos_principales as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        $exitos[] = "✅ $descripcion";
    } else {
        $errores[] = "❌ $descripcion no encontrado";
    }
}

// Verificar estadísticas de reservas
try {
    $stats = getEstadisticasReservas();
    $exitos[] = "✅ Estadísticas de reservas funcionando";
} catch (Exception $e) {
    $errores[] = "❌ Error obteniendo estadísticas: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación del Sistema - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 1000px;
            margin: 0 auto;
        }
        .status-card {
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .success-card {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error-card {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">
            <i class="fas fa-check-circle me-2"></i>Verificación del Sistema
        </h2>
        
        <div class="row">
            <div class="col-md-6">
                <h4 class="text-success">
                    <i class="fas fa-check me-2"></i>Componentes Funcionando
                </h4>
                <?php foreach ($exitos as $exito): ?>
                    <div class="status-card success-card">
                        <?php echo $exito; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="col-md-6">
                <h4 class="text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Problemas Detectados
                </h4>
                <?php if (empty($errores)): ?>
                    <div class="status-card success-card">
                        <i class="fas fa-check-circle me-2"></i>¡No se detectaron problemas!
                    </div>
                <?php else: ?>
                    <?php foreach ($errores as $error): ?>
                        <div class="status-card error-card">
                            <?php echo $error; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <hr class="my-4">
        
        <div class="row">
            <div class="col-12">
                <h4>
                    <i class="fas fa-info-circle me-2"></i>Resumen del Sistema
                </h4>
                <div class="alert alert-info">
                    <h5>🎯 Sistema de Reservas - My Suite In Cartagena</h5>
                    <p><strong>Estado:</strong> 
                        <?php if (empty($errores)): ?>
                            <span class="text-success">✅ COMPLETAMENTE FUNCIONAL</span>
                        <?php else: ?>
                            <span class="text-warning">⚠️ CON PROBLEMAS</span>
                        <?php endif; ?>
                    </p>
                    <p><strong>Componentes:</strong> Calendario, Formulario, Emails, Panel Admin, Comprobantes</p>
                    <p><strong>Base de datos:</strong> MySQL (puerto 3307)</p>
                    <p><strong>Emails:</strong> Gmail SMTP con PHPMailer</p>
                </div>
            </div>
        </div>
        
        <div class="d-grid gap-2">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-tachometer-alt me-2"></i>Ir al Dashboard
            </a>
            <a href="reservas.php" class="btn btn-outline-primary">
                <i class="fas fa-calendar-check me-2"></i>Gestionar Reservas
            </a>
            <a href="../../index.php?lang=en" class="btn btn-outline-success">
                <i class="fas fa-calendar me-2"></i>Ver Calendario Público
            </a>
        </div>
    </div>
</body>
</html>

<?php
session_start();

// Verificar si el usuario está logueado como admin
if (!((isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) ||
    (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && 
     isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin'))) {
    header('Location: ../../../app/controllers/auth/login.php');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

$database = new Database();
$pdo = $database->getConnection();

// Obtener descuentos actuales (excluyendo el descuento por vendedor)
$descuentos = [];
if ($pdo) {
    $stmt = $pdo->query("SELECT * FROM descuentos_config WHERE tipo_descuento != 'vendedor' ORDER BY tipo_descuento");
    $descuentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Descuentos - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oxygen:wght@300;400;700&family=Crimson+Text:wght@400;600;700&family=Abril+Fatface&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
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
                            <a class="nav-link" href="reservas.php">
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
                            <a class="nav-link" href="/admin/manillas.php">
                                <i class="fas fa-ring me-2"></i> Manillas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/servicios_adicionales.php">
                                <i class="fas fa-plus me-2"></i> Servicios adicionales
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="descuentos.php">
                                <i class="fas fa-percentage me-2"></i> Descuentos
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

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-percentage me-2"></i>
                        Gestión de Descuentos
                    </h1>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-sliders-h me-2"></i>
                                    Configuración de Descuentos
                                </h5>
                            </div>
                            <div class="card-body">
                                <form id="descuentosForm">
                                    <?php foreach ($descuentos as $descuento): 
                                        // Saltar el descuento por vendedor si existe
                                        if (isset($descuento['tipo_descuento']) && $descuento['tipo_descuento'] === 'vendedor') {
                                            continue;
                                        }
                                    ?>
                                        <div class="row mb-4">
                                            <div class="col-md-4">
                                                <div class="card h-100">
                                                    <div class="card-header bg-<?php echo $descuento['tipo_descuento'] === 'fidelidad' ? 'success' : ($descuento['tipo_descuento'] === 'cumpleanos' ? 'warning' : 'primary'); ?> text-white">
                                                        <h6 class="mb-0">
                                                            <i class="fas fa-<?php echo $descuento['tipo_descuento'] === 'fidelidad' ? 'heart' : ($descuento['tipo_descuento'] === 'cumpleanos' ? 'birthday-cake' : 'gift'); ?> me-2"></i>
                                                            <?php 
                                                            $nombres = [
                                                                'fidelidad' => 'Descuento por Fidelidad',
                                                                'cumpleanos' => 'Descuento por Cumpleaños',
                                                                'promocional' => 'Descuento por Pago en Efectivo'
                                                            ];
                                                            $tipo = isset($descuento['tipo_descuento']) ? $descuento['tipo_descuento'] : 'promocional';
                                                            echo isset($nombres[$tipo]) ? $nombres[$tipo] : 'Descuento';
                                                            ?>
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="mb-3">
                                                            <label for="<?php echo $descuento['tipo_descuento']; ?>_porcentaje" class="form-label">Porcentaje (%)</label>
                                                            <div class="input-group">
                                                                <input type="number" 
                                                                       class="form-control" 
                                                                       id="<?php echo $descuento['tipo_descuento']; ?>_porcentaje" 
                                                                       name="<?php echo $descuento['tipo_descuento']; ?>_porcentaje"
                                                                       value="<?php echo $descuento['porcentaje']; ?>"
                                                                       min="0" 
                                                                       max="100" 
                                                                       step="0.01">
                                                                <span class="input-group-text">%</span>
                                                            </div>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" 
                                                                   type="checkbox" 
                                                                   id="<?php echo $descuento['tipo_descuento']; ?>_activo"
                                                                   name="<?php echo $descuento['tipo_descuento']; ?>_activo"
                                                                   <?php echo $descuento['activo'] ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="<?php echo $descuento['tipo_descuento']; ?>_activo">
                                                                Activo
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="card h-100">
                                                    <div class="card-header bg-light">
                                                        <h6 class="mb-0">
                                                            <i class="fas fa-info-circle me-2"></i>
                                                            Descripción
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php if ($descuento['tipo_descuento'] === 'fidelidad'): ?>
                                                            <p class="mb-2"><strong>Descuento por Fidelidad:</strong></p>
                                                            <p class="text-muted small">Se aplica automáticamente a usuarios registrados que han hecho reservas anteriores. Este descuento se suma al total de la reserva.</p>
                                                            <div class="alert alert-info small">
                                                                <i class="fas fa-lightbulb me-1"></i>
                                                                <strong>Ejemplo:</strong> Si el total es $300,000 y el descuento es 5%, se aplicarán $15,000 de descuento.
                                                            </div>
                                                        <?php elseif ($descuento['tipo_descuento'] === 'cumpleanos'): ?>
                                                            <p class="mb-2"><strong>Descuento por Cumpleaños:</strong></p>
                                                            <p class="text-muted small">Se aplica automáticamente cuando el cumpleaños del usuario cae dentro del rango de fechas de la reserva. Este descuento se suma al total de la reserva.</p>
                                                            <div class="alert alert-warning small">
                                                                <i class="fas fa-lightbulb me-1"></i>
                                                                <strong>Ejemplo:</strong> Si el total es $300,000 y el descuento es 30%, se aplicarán $90,000 de descuento.
                                                            </div>
                                                        <?php else: ?>
                                                            <p class="mb-2"><strong>Descuento por Pago en Efectivo:</strong></p>
                                                            <p class="text-muted small">Se aplica automáticamente cuando el cliente selecciona "Efectivo" como método de pago. Este descuento se suma al total de la reserva.</p>
                                                            <div class="alert alert-primary small">
                                                                <i class="fas fa-lightbulb me-1"></i>
                                                                <strong>Ejemplo:</strong> Si el total es $300,000 y el descuento es 3%, se aplicarán $9,000 de descuento.
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                    <?php endforeach; ?>
                                    
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                            <i class="fas fa-undo me-2"></i>
                                            Restaurar Valores
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>
                                            Guardar Cambios
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Información Importante
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>¡Atención!</strong>
                                    <p class="mb-0 mt-2">Los cambios en los descuentos se aplicarán inmediatamente a todas las nuevas reservas.</p>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    <strong>Tip:</strong>
                                    <p class="mb-0 mt-2">Los descuentos se pueden combinar. Un usuario puede recibir descuento por fidelidad Y por cumpleaños si cumple ambas condiciones.</p>
                                </div>

                                <div class="mb-3">
                                    <h6><i class="fas fa-chart-line me-2"></i>Estadísticas Actuales</h6>
                                    <div class="row text-center">
                                        <?php 
                                        // Buscar cada tipo de descuento en el array
                                        $fidelidad = null;
                                        $cumpleanos = null;
                                        $promocional = null;
                                        
                                        foreach ($descuentos as $desc) {
                                            if ($desc['tipo_descuento'] === 'fidelidad') {
                                                $fidelidad = $desc;
                                            } elseif ($desc['tipo_descuento'] === 'cumpleanos') {
                                                $cumpleanos = $desc;
                                            } elseif ($desc['tipo_descuento'] === 'promocional') {
                                                $promocional = $desc;
                                            }
                                        }
                                        ?>
                                        <div class="col-4">
                                            <div class="border rounded p-2">
                                                <h5 class="text-success mb-0"><?php echo $fidelidad ? $fidelidad['porcentaje'] : '0'; ?>%</h5>
                                                <small class="text-muted">Fidelidad</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="border rounded p-2">
                                                <h5 class="text-warning mb-0"><?php echo $cumpleanos ? $cumpleanos['porcentaje'] : '0'; ?>%</h5>
                                                <small class="text-muted">Cumpleaños</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="border rounded p-2">
                                                <h5 class="text-primary mb-0"><?php echo $promocional ? $promocional['porcentaje'] : '0'; ?>%</h5>
                                                <small class="text-muted">Efectivo</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-history me-2"></i>
                                    Historial de Cambios
                            </div>
                            <div class="card-body">
                                <div class="text-center text-muted">
                                    <i class="fas fa-clock fa-2x mb-2"></i>
                                    <p>No hay cambios registrados</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="palmiraModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        Clientes de Palmira
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="palmiraContent">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando clientes de Palmira...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="dateBlockingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-times me-2"></i>
                        Bloquear Fechas
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="blockDatesForm">
                        <div class="mb-3">
                            <label for="startDate" class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control" id="startDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="endDate" class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" id="endDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="reason" class="form-label">Motivo</label>
                            <select class="form-select" id="reason" required>
                                <option value="">Seleccionar motivo</option>
                                <option value="mantenimiento">Mantenimiento</option>
                                <option value="uso_interno">Uso Interno</option>
                                <option value="evento_especial">Evento Especial</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción (Opcional)</label>
                            <textarea class="form-control" id="description" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-lock me-2"></i>
                            Bloquear Fechas
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="clientManagementModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-users me-2"></i>
                        Gestión de Clientes
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchInput" placeholder="Buscar por nombre, email o teléfono...">
                            <button class="btn btn-outline-secondary" type="button" onclick="searchClients()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div id="clientsContent">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando clientes...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Funciones del sidebar
        // Funciones eliminadas - Clientes Palmira y Bloquear Fechas ahora están integrados en otras páginas

        function showClientManagement() {
            const modal = new bootstrap.Modal(document.getElementById('clientManagementModal'));
            modal.show();
            loadAllClients();
        }

        function loadPalmiraClients() {
            fetch('../../../app/api/admin/get_palmira_clients.php')
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('palmiraContent');
                    if (data.success) {
                        content.innerHTML = `
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Teléfono</th>
                                            <th>Total Reservas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.clients.map(client => `
                                            <tr>
                                                <td>${client.id_usuario}</td>
                                                <td>${client.nombre} ${client.apellido}</td>
                                                <td>${client.correo}</td>
                                                <td>${client.telefono || 'N/A'}</td>
                                                <td><span class="badge bg-primary">${client.total_reservas}</span></td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        `;
                    } else {
                        content.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                    }
                })
                .catch(error => {
                    document.getElementById('palmiraContent').innerHTML = '<div class="alert alert-danger">Error al cargar los datos</div>';
                });
        }

        function loadAllClients() {
            fetch('../../../app/api/admin/get_all_clients.php')
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('clientsContent');
                    if (data.success) {
                        content.innerHTML = `
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Teléfono</th>
                                            <th>Ciudad</th>
                                            <th>Total Reservas</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.clients.map(client => `
                                            <tr>
                                                <td>${client.id_usuario}</td>
                                                <td>${client.nombre} ${client.apellido}</td>
                                                <td>${client.correo}</td>
                                                <td>${client.telefono || 'N/A'}</td>
                                                <td><span class="badge bg-info">${client.ciudad || 'N/A'}</span></td>
                                                <td><span class="badge bg-primary">${client.total_reservas}</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" onclick="viewClientDetails(${client.id_usuario})">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        `;
                    } else {
                        content.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                    }
                })
                .catch(error => {
                    document.getElementById('clientsContent').innerHTML = '<div class="alert alert-danger">Error al cargar los datos</div>';
                });
        }

        function searchClients() {
            const searchTerm = document.getElementById('searchInput').value;
            if (searchTerm.trim() === '') {
                loadAllClients();
                return;
            }

            fetch(`search_clients.php?q=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('clientsContent');
                    if (data.success) {
                        content.innerHTML = `
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Teléfono</th>
                                            <th>Ciudad</th>
                                            <th>Total Reservas</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.clients.map(client => `
                                            <tr>
                                                <td>${client.id_usuario}</td>
                                                <td>${client.nombre} ${client.apellido}</td>
                                                <td>${client.correo}</td>
                                                <td>${client.telefono || 'N/A'}</td>
                                                <td><span class="badge bg-info">${client.ciudad || 'N/A'}</span></td>
                                                <td><span class="badge bg-primary">${client.total_reservas}</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" onclick="viewClientDetails(${client.id_usuario})">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        `;
                    } else {
                        content.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                    }
                })
                .catch(error => {
                    document.getElementById('clientsContent').innerHTML = '<div class="alert alert-danger">Error al buscar clientes</div>';
                });
        }

        function blockDates() {
            const formData = new FormData();
            formData.append('start_date', document.getElementById('startDate').value);
            formData.append('end_date', document.getElementById('endDate').value);
            formData.append('reason', document.getElementById('reason').value);
            formData.append('description', document.getElementById('description').value);

            fetch('../../../app/api/admin/block_dates.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Fechas bloqueadas exitosamente');
                    document.getElementById('blockDatesForm').reset();
                    bootstrap.Modal.getInstance(document.getElementById('dateBlockingModal')).hide();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error al bloquear las fechas');
            });
        }

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

        // Funciones de descuentos
        function resetForm() {
            if (confirm('¿Estás seguro de que quieres restaurar los valores originales?')) {
                location.reload();
            }
        }

        // Event listeners
        document.getElementById('descuentosForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../../../app/api/admin/update_descuentos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Descuentos actualizados exitosamente');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error al actualizar los descuentos');
            });
        });

        document.getElementById('blockDatesForm').addEventListener('submit', function(e) {
            e.preventDefault();
            blockDates();
        });

        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchClients();
            }
        });
    </script>
</body>
</html>

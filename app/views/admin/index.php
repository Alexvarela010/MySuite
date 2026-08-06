<?php
/**
 * Panel de Administración - Sistema de Reservas
 * My Suite In Cartagena
 */

session_start();

// Verificar si el usuario está logueado como admin
if ((isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) ||
    (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && 
     isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin')) {
    // Usuario logueado correctamente, continuar
} else {
    // No está logueado, redirigir al login principal
    header('Location: ../../../app/controllers/auth/login.php');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

// Obtener estadísticas
$estadisticas = getEstadisticasReservas();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - My Suite In Cartagena</title>
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
        .stat-card {
            background: #fff;
            color: #333;
            border: 1px solid #dee2e6;
        }
        .stat-card .card-body {
            padding: 2rem;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2470dc;
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
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
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
                            <a class="nav-link active" href="index.php">
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
                    <h1 class="h2"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-share-alt me-1"></i> Share
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download me-1"></i> Export
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                            <i class="fas fa-calendar me-1"></i>
                            This week
                        </button>
                    </div>
                </div>

                    <!-- Estadísticas -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-calendar-check fa-2x mb-2" style="color: #2470dc;"></i>
                                    <div class="stat-number"><?php echo $estadisticas['total_reservas']; ?></div>
                                    <div style="color: #6c757d;">Total Reservas</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-clock fa-2x mb-2" style="color: #ffc107;"></i>
                                    <div class="stat-number"><?php echo $estadisticas['reservas_pendientes']; ?></div>
                                    <div style="color: #6c757d;">Pendientes</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-check-circle fa-2x mb-2" style="color: #28a745;"></i>
                                    <div class="stat-number"><?php echo $estadisticas['reservas_aprobadas']; ?></div>
                                    <div style="color: #6c757d;">Aprobadas</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-dollar-sign fa-2x mb-2" style="color: #2470dc;"></i>
                                    <div class="stat-number">$<?php echo number_format($estadisticas['ingresos_totales'], 0, ',', '.'); ?></div>
                                    <div style="color: #6c757d;">Ingresos Totales</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficas y Reportes -->
                    <div class="row mb-4">
                        <!-- Gráfica de Reservas por Estado -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-pie me-2"></i> Reservas por Estado
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="reservasEstadoChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfica de Ingresos Mensuales -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-line me-2"></i> Ingresos Mensuales
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="ingresosMensualesChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reportes Adicionales -->
                    <div class="row mb-4">
                        <!-- Métodos de Pago -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-credit-card me-2"></i> Métodos de Pago
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="metodosPagoChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Reservas por Mes -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-calendar-alt me-2"></i> Reservas por Mes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="reservasMesChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Datos para las gráficas
        const estadisticas = <?php echo json_encode($estadisticas); ?>;
        
        // Gráfica de Reservas por Estado
        const ctxReservasEstado = document.getElementById('reservasEstadoChart').getContext('2d');
        new Chart(ctxReservasEstado, {
            type: 'doughnut',
            data: {
                labels: ['Pendientes', 'Aprobadas', 'Rechazadas', 'Canceladas'],
                datasets: [{
                    data: [
                        estadisticas.reservas_pendientes || 0,
                        estadisticas.reservas_aprobadas || 0,
                        estadisticas.reservas_rechazadas || 0,
                        estadisticas.reservas_canceladas || 0
                    ],
                    backgroundColor: [
                        '#ffc107',
                        '#28a745',
                        '#dc3545',
                        '#6c757d'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Gráfica de Ingresos Mensuales (últimos 6 meses)
        const ctxIngresos = document.getElementById('ingresosMensualesChart').getContext('2d');
        new Chart(ctxIngresos, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: 'Ingresos (COP)',
                    data: [0, 0, 0, 0, 0, estadisticas.ingresos_totales || 0],
                    borderColor: '#1e3c72',
                    backgroundColor: 'rgba(30, 60, 114, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString('es-CO');
                            }
                        }
                    }
                }
            }
        });

        // Gráfica de Métodos de Pago
        const ctxMetodosPago = document.getElementById('metodosPagoChart').getContext('2d');
        new Chart(ctxMetodosPago, {
            type: 'bar',
            data: {
                labels: ['Tarjeta de Crédito', 'transferencia'],
                datasets: [{
                    label: 'Cantidad de Reservas',
                    data: [estadisticas.metodo_tarjeta || 0, estadisticas.metodo_efectivo || 0],
                    backgroundColor: [
                        '#1e3c72',
                        '#2a5298'
                    ],
                    borderWidth: 1,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Gráfica de Reservas por Mes
        const ctxReservasMes = document.getElementById('reservasMesChart').getContext('2d');
        new Chart(ctxReservasMes, {
            type: 'bar',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: 'Reservas',
                    data: [0, 0, 0, 0, 0, estadisticas.total_reservas || 0],
                    backgroundColor: '#2a5298',
                    borderColor: '#1e3c72',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
            </main>
        </div>
    </div>

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
        
        // Mostrar modal de clientes Palmira
        function showPalmiraFilter() {
            console.log('Ejecutando showPalmiraFilter');
            $('#palmiraModal').modal('show');
            loadPalmiraClients();
        }

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

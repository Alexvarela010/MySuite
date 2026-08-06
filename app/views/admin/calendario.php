<?php
/**
 * Calendario - Panel de Administración
 * My Suite In Cartagena
 */

session_start();

// Verificar si el usuario está logueado como admin
if (!((isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) ||
        (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true &&
                isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin'))) {
    header('Location: ../../../app/controllers/auth/login.php');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/functions.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario - Panel de Administración</title>
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
            background-color: rgb(235, 234, 223);
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

        /* Estilos del Dashboard aplicados al Sidebar */
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

        /* Estilos del Calendario Admin */
        .calendar-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #e9ecef;
            border-radius: 8px;
            overflow: hidden;
        }

        .calendar-day {
            background: white;
            min-height: 80px;
            padding: 8px;
            border: 1px solid #e9ecef;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1;
            pointer-events: auto;
        }

        .calendar-day:hover {
            background: #f8f9fa;
            transform: scale(1.02);
        }

        .calendar-day.other-month {
            background: #f8f9fa;
            color: #adb5bd;
        }

        .calendar-day.today {
            background: #e3f2fd;
        }

        .calendar-day.past {
            background: #f8f9fa !important;
            color: #adb5bd !important;
            cursor: not-allowed !important;
            opacity: 0.6;
        }

        .calendar-day.past:hover {
            transform: none !important;
            background: #f8f9fa !important;
        }

        .calendar-day.has-reservation {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }

        .calendar-day.blocked {
            background: rgb(147, 141, 141) !important;
            color: #ffffff !important;
            border-left: 4px solid rgb(204, 194, 194) !important;
            font-weight: bold !important;
        }

        .calendar-day.blocked:hover {
            background: rgb(120, 115, 115) !important;
        }

        .calendar-day.not-available {
            position: relative;
            opacity: 0.6;
        }
        .calendar-day.not-available::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 3px;
            background: #dc3545;
            transform: translateY(-50%);
            z-index: 10;
        }

        /* Estilo mejorado para el mes */
        .month-year-display {
            font-size: 2rem;
            font-weight: 700;
            color: #2a5298;
            text-align: center;
            margin: 20px 0;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .month-year-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .blocked-info {
            color: #ffffff !important;
            font-size: 10px !important;
            text-align: center !important;
            margin-top: 2px !important;
        }

        .calendar-day.pending {
            background: #ffc107 !important;
            color: #000000 !important;
            border-left: 4px solid #ffc107 !important;
        }

        .calendar-day.approved {
            background: #007bff !important;
            color: #000000 !important;
            border-left: 4px solid #007bff !important;
        }

        .calendar-day.abonada {
            background: #ff8c00 !important;
            color: #000000 !important;
            border-left: 4px solid #ff8c00 !important;
        }

        .calendar-day.paid {
            background: #28a745 !important;
            color: #000000 !important;
            border-left: 4px solid #28a745 !important;
        }

        .day-number {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 4px;
            pointer-events: none;
        }

        .reservation-info {
            font-size: 10px;
            margin: 2px 0;
            padding: 2px 4px;
            border-radius: 3px;
            color: white;
            pointer-events: none;
        }

        .reservation-info.pending {
            background: #ffc107;
        }

        .reservation-info.approved {
            background: #007bff;
        }

        .reservation-info.abonada {
            background: #ff8c00;
        }

        .reservation-info.rejected {
            background: #dc3545;
        }

        .reservation-info.paid {
            background: #17a2b8;
        }

        .reservation-info.cancelled {
            background: #6c757d;
        }

        .blocked-info {
            font-size: 10px;
            background: #dc3545;
            color: white;
            padding: 2px 4px;
            border-radius: 3px;
            margin: 2px 0;
        }

        .calendar-legend {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .stats-card {
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-3px);
        }

        /* Estilos para el botón de refresh */
        #refreshCalendar {
            transition: all 0.3s ease;
        }

        #refreshCalendar:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        #refreshCalendar:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .fa-spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
                        <a class="nav-link active" href="calendario.php">
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
                        <a class="nav-link" href="descuentos.php">
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

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-calendar-alt me-2"></i> Calendario de Reservas</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="text-muted">
                        <i class="fas fa-calendar me-1"></i>
                        <?php echo date('d/m/Y H:i'); ?>
                    </div>
                </div>
            </div>

            <!-- Contenido del calendario -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-calendar-alt me-2"></i> Calendario de Reservas
                            </h5>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-success" id="refreshCalendar" title="Actualizar calendario">
                                    <i class="fas fa-sync-alt"></i> Actualizar
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Mes y Año - Display mejorado -->
                            <div class="month-year-container">
                                <div class="month-year-display" id="monthYearDisplay">
                                    <!-- El mes se mostrará aquí -->
                                </div>
                            </div>

                            <!-- Navegación del mes -->
                            <div class="d-flex justify-content-center align-items-center gap-3 mb-4">
                                <button type="button" class="btn btn-outline-primary btn-lg" id="prevMonth" title="Mes anterior">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-lg" id="nextMonth" title="Mes siguiente">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>

                            <!-- Calendario -->
                            <div id="adminCalendar" class="calendar-container">
                                <!-- El calendario se generará dinámicamente -->
                            </div>

                            <!-- Leyenda -->
                            <div class="calendar-legend mt-3">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h6>Estado de Reservas:</h6>
                                        <div class="d-flex flex-wrap gap-3">
                                            <span class="badge" style="background-color:rgba(118, 112, 112, 0.69); color: #ffffff;">Bloqueado</span>
                                            <span class="badge" style="background-color: #ffc107; color: #000000;">Pendiente</span>
                                            <span class="badge" style="background-color:rgb(0, 122, 251); color: #000000;">Aprobada</span>
                                            <span class="badge" style="background-color: #ff8c00; color: #000000;">Abonada</span>
                                            <span class="badge" style="background-color: #28a745; color: #000000;">Pagada</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas del mes -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Reservas del Mes</h6>
                                    <h3 id="monthReservations">0</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calendar-check fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Noches Ocupadas</h6>
                                    <h3 id="monthNights">0</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-bed fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Fechas Bloqueadas</h6>
                                    <h3 id="monthBlocked">0</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-ban fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Ocupación %</h6>
                                    <h3 id="monthOccupancy">0%</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-pie fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
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
                    <!-- Tipo de bloqueo -->
                    <div class="mb-3">
                        <label class="form-label">Tipo de Bloqueo</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="blockType" id="blockSingle" value="single" checked>
                            <label class="form-check-label" for="blockSingle">
                                <i class="fas fa-calendar-day me-2"></i>Bloquear un solo día
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="blockType" id="blockRange" value="range">
                            <label class="form-check-label" for="blockRange">
                                <i class="fas fa-calendar-week me-2"></i>Bloquear rango de fechas
                            </label>
                        </div>
                    </div>

                    <!-- Fecha única -->
                    <div class="mb-3" id="singleDateGroup">
                        <label for="blockSingleDate" class="form-label">Fecha a Bloquear</label>
                        <input type="date" class="form-control" id="blockSingleDate">
                    </div>

                    <!-- Rango de fechas -->
                    <div class="mb-3" id="rangeDateGroup" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="blockStartDate" class="form-label">Fecha de Inicio</label>
                                <input type="date" class="form-control" id="blockStartDate">
                            </div>
                            <div class="col-md-6">
                                <label for="blockEndDate" class="form-label">Fecha de Fin</label>
                                <input type="date" class="form-control" id="blockEndDate">
                            </div>
                        </div>
                    </div>

                    <!-- Motivo del bloqueo -->
                    <div class="mb-3">
                        <label for="blockReason" class="form-label">Motivo del Bloqueo</label>
                        <select class="form-control" id="blockReason" required>
                            <option value="">Seleccionar motivo...</option>
                            <option value="mantenimiento">🔧 Mantenimiento</option>
                            <option value="uso_interno">🏢 Uso Interno</option>
                            <option value="evento_especial">🎉 Evento Especial</option>
                            <option value="limpieza">🧹 Limpieza Profunda</option>
                            <option value="reparacion">🔨 Reparaciones</option>
                            <option value="otro">📝 Otro</option>
                        </select>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="blockDescription" class="form-label">Descripción (opcional)</label>
                        <textarea class="form-control" id="blockDescription" rows="3"
                                  placeholder="Detalles adicionales sobre el bloqueo..."></textarea>
                    </div>

                    <!-- Información adicional -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Información:</strong> No se pueden bloquear fechas que ya tienen reservas aprobadas.
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

    // Función eliminada - el bloqueo ahora se hace desde el click en días disponibles

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

    // Manejar cambio de tipo de bloqueo
    function handleBlockTypeChange() {
        const blockType = document.querySelector('input[name="blockType"]:checked').value;
        const singleDateGroup = document.getElementById('singleDateGroup');
        const rangeDateGroup = document.getElementById('rangeDateGroup');

        if (blockType === 'single') {
            singleDateGroup.style.display = 'block';
            rangeDateGroup.style.display = 'none';
            document.getElementById('blockSingleDate').required = true;
            document.getElementById('blockStartDate').required = false;
            document.getElementById('blockEndDate').required = false;
        } else {
            singleDateGroup.style.display = 'none';
            rangeDateGroup.style.display = 'block';
            document.getElementById('blockSingleDate').required = false;
            document.getElementById('blockStartDate').required = true;
            document.getElementById('blockEndDate').required = true;
        }
    }

    // Bloquear fechas
    function blockDates() {
        const blockType = document.querySelector('input[name="blockType"]:checked').value;
        const reason = document.getElementById('blockReason').value;
        const description = document.getElementById('blockDescription').value;

        let startDate, endDate;

        if (blockType === 'single') {
            startDate = document.getElementById('blockSingleDate').value;
            endDate = startDate; // Para un solo día, usar la misma fecha
        } else {
            startDate = document.getElementById('blockStartDate').value;
            endDate = document.getElementById('blockEndDate').value;
        }

        // Validaciones
        if (!startDate || !reason) {
            alert('Por favor complete todos los campos obligatorios');
            return;
        }

        if (blockType === 'range' && !endDate) {
            alert('Para bloquear un rango debe proporcionar fecha de fin');
            return;
        }

        if (blockType === 'range' && new Date(startDate) > new Date(endDate)) {
            alert('La fecha de inicio debe ser anterior a la fecha de fin');
            return;
        }

        if (new Date(startDate) < new Date()) {
            alert('No se pueden bloquear fechas pasadas');
            return;
        }

        const formData = new FormData();
        formData.append('start_date', startDate);
        formData.append('end_date', endDate);
        formData.append('reason', reason);
        formData.append('description', description);
        formData.append('block_type', blockType);

        // Mostrar loading
        const submitBtn = document.querySelector('button[onclick="blockDates()"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Bloqueando...';
        submitBtn.disabled = true;

        fetch('../../../app/api/admin/block_dates.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar mensaje de éxito con más detalles
                    const message = blockType === 'single'
                        ? `✅ Fecha bloqueada exitosamente: ${new Date(startDate).toLocaleDateString('es-CO')}`
                        : `✅ Rango bloqueado exitosamente: ${new Date(startDate).toLocaleDateString('es-CO')} - ${new Date(endDate).toLocaleDateString('es-CO')}`;

                    alert(message);
                    $('#dateBlockingModal').modal('hide');
                    document.getElementById('dateBlockingForm').reset();

                    // Recargar calendario si está en la página del calendario
                    if (typeof loadCalendarData === 'function') {
                        loadCalendarData();
                    }
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error al bloquear las fechas');
            })
            .finally(() => {
                // Restaurar botón
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
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

    // JavaScript del Calendario Admin
        let currentDate = new Date(); // Mes actual
        currentDate.setDate(1); // Primer día del mes
        let reservations = [];
        let blockedDates = [];

        // Cache en cliente por mes para evitar llamadas repetidas al servidor
        // Key: `${year}-${month}-${apartamento_id}` (month 0-11)
        const calendarDataCache = {};
        // Detectar apartment id si está disponible en la vista, si no, usar 1
        const apartamentoId = (typeof window.apartamentoId !== 'undefined') ? window.apartamentoId : 1;

    // Inicializar calendario
    document.addEventListener('DOMContentLoaded', function() {
        loadCalendarData();
        setupCalendarEvents();
        setupBlockDateEvents();
    });

    // Configurar eventos para bloqueo de fechas
    function setupBlockDateEvents() {
        // Event listeners para cambio de tipo de bloqueo
        const blockTypeRadios = document.querySelectorAll('input[name="blockType"]');
        blockTypeRadios.forEach(radio => {
            radio.addEventListener('change', handleBlockTypeChange);
        });
    }

    // Cargar datos del calendario
        function loadCalendarData() {
            const key = `${currentDate.getFullYear()}-${currentDate.getMonth()}-${apartamentoId}`;
            if (calendarDataCache[key]) {
                const data = calendarDataCache[key];
                reservations = data.reservations || [];
                blockedDates = data.blocked_dates || [];
                renderCalendar();
                updateStats();
                // Prefetch siguiente mes en background
                prefetchMonth(currentDate.getFullYear(), currentDate.getMonth() + 1);
                return Promise.resolve(data);
            }

            const url = `../../../app/api/admin/get_calendar_data.php?month=${currentDate.getMonth()}&year=${currentDate.getFullYear()}&apartamento_id=${apartamentoId}`;
            return fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        reservations = data.reservations || [];
                        blockedDates = data.blocked_dates || [];
                        // Guardar en cache de sesión
                        calendarDataCache[key] = data;

                        // Debug: verificar reservas abonadas
                        console.log('Total de reservas cargadas:', reservations.length);
                        const abonadas = reservations.filter(res => {
                            const estado = (res.estado || '').toLowerCase();
                            return estado === 'abonada';
                        });
                        if (abonadas.length > 0) {
                            console.log('Reservas abonadas encontradas:', abonadas);
                        } else {
                            console.log('No se encontraron reservas abonadas. Estados encontrados:', 
                                reservations.map(r => ({ id: r.id_reserva, estado: r.estado })));
                        }

                        renderCalendar();
                        updateStats();

                        // Prefetch siguiente mes en background
                        prefetchMonth(currentDate.getFullYear(), currentDate.getMonth() + 1);
                    }
                    return data;
                })
                .catch(error => {
                    console.error('Error al cargar datos del calendario:', error);
                    throw error;
                });
        }

        function prefetchMonth(year, month0Based) {
            // Evitar prefetch fuera de rango razonable
            const m = (month0Based + 12) % 12;
            const y = month0Based < 0 ? year - 1 : (month0Based > 11 ? year + 1 : year);
            const key = `${y}-${m}-${apartamentoId}`;
            if (calendarDataCache[key]) return;
            const url = `../../../app/api/admin/get_calendar_data.php?month=${m}&year=${y}&apartamento_id=${apartamentoId}`;
            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if (data && data.success) {
                        calendarDataCache[key] = data;
                    }
                })
                .catch(e => console.warn('Prefetch month failed:', e));
        }

    // Renderizar calendario
    function renderCalendar() {
        const calendar = document.getElementById('adminCalendar');
        const monthYearDisplay = document.getElementById('monthYearDisplay');

        // Actualizar título del mes con estilo mejorado
        const monthNames = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
        const monthName = monthNames[currentDate.getMonth()];
        const year = currentDate.getFullYear();
        monthYearDisplay.textContent = `${monthName} ${year}`;

        // Limpiar calendario
        calendar.innerHTML = '';

        // Crear grid del calendario
        const calendarGrid = document.createElement('div');
        calendarGrid.className = 'calendar-grid';

        // Días de la semana
        const dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        dayNames.forEach(day => {
            const dayHeader = document.createElement('div');
            dayHeader.className = 'calendar-day text-center fw-bold bg-light';
            dayHeader.textContent = day;
            dayHeader.style.cursor = 'default';
            calendarGrid.appendChild(dayHeader);
        });

        // Obtener primer día del mes
        const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
        const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
        const startingDayOfWeek = firstDay.getDay();
        const daysInMonth = lastDay.getDate();

        // Días del mes anterior
        const prevMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 0);
        for (let i = startingDayOfWeek - 1; i >= 0; i--) {
            const day = document.createElement('div');
            day.className = 'calendar-day other-month';
            day.textContent = prevMonth.getDate() - i;
            calendarGrid.appendChild(day);
        }

        // Días del mes actual
        for (let day = 1; day <= daysInMonth; day++) {
            const dayElement = document.createElement('div');
            const date = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
            const dateString = date.toISOString().split('T')[0];

            dayElement.className = 'calendar-day';

            // Número del día
            const dayNumber = document.createElement('div');
            dayNumber.className = 'day-number';
            dayNumber.textContent = day;
            dayElement.appendChild(dayNumber);

            // Verificar si es hoy
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const dateOnly = new Date(date);
            dateOnly.setHours(0, 0, 0, 0);

            if (dateOnly.toDateString() === today.toDateString()) {
                dayElement.classList.add('today');
            }

            // Deshabilitar días pasados
            if (dateOnly < today) {
                dayElement.classList.add('past');
                dayElement.style.pointerEvents = 'none';
                dayElement.style.cursor = 'not-allowed';
                dayElement.style.opacity = '0.6';
            }

            // Quitar bloqueo global hardcodeado; disponibilidad se determina por bloqueos y reservas

            // VERIFICAR SI ESTÁ BLOQUEADO
            let isBlocked = false;
            let blockedReason = '';

            for (let i = 0; i < blockedDates.length; i++) {
                const blocked = blockedDates[i];
                const blockedStart = new Date(blocked.fecha_inicio);
                const blockedEnd = new Date(blocked.fecha_fin);
                const selectedDate = new Date(dateString);
                blockedStart.setHours(0, 0, 0, 0);
                blockedEnd.setHours(0, 0, 0, 0);
                selectedDate.setHours(0, 0, 0, 0);

                // Verificar si la fecha está dentro del rango bloqueado y está activo
                if (selectedDate >= blockedStart && selectedDate <= blockedEnd && blocked.activo !== 0) {
                    isBlocked = true;
                    blockedReason = blocked.origen === 'ical' ? 'Reserva externa (iCal)' : blocked.motivo;
                    break;
                }
            }

            // Verificar si hay reservas (siempre definir dayReservations)
            // Regla: la fecha de salida es exclusiva, así que el día checkout queda libre para una nueva reserva.
            const dayReservations = reservations.filter(res => {
                const startDate = new Date(res.fecha_entrada);
                const endDate = new Date(res.fecha_salida);
                return date >= startDate && date < endDate;
            });

            if (isBlocked) {
                // APLICAR ESTILOS DE BLOQUEO
                dayElement.style.backgroundColor = 'rgb(147, 141, 141)';
                dayElement.style.color = '#ffffff';
                dayElement.style.borderLeft = '4px solid rgb(204, 194, 194)';
                dayElement.style.fontWeight = 'bold';

                // AGREGAR TEXTO DE BLOQUEO
                const blockedText = document.createElement('div');
                blockedText.style.color = '#ffffff';
                blockedText.style.fontSize = '12px';
                blockedText.style.textAlign = 'center';
                blockedText.style.fontWeight = 'bold';
                blockedText.style.marginTop = '5px';
                blockedText.textContent = 'BLOQUEADO';
                dayElement.appendChild(blockedText);

                // AGREGAR MOTIVO
                const reasonText = document.createElement('div');
                reasonText.style.color = '#ffffff';
                reasonText.style.fontSize = '10px';
                reasonText.style.textAlign = 'center';
                reasonText.style.marginTop = '2px';
                reasonText.textContent = blockedReason;
                dayElement.appendChild(reasonText);
            } else if (dayReservations.length > 0) {
                // Determinar el estado principal del día - PRIORIDAD: PAGADO > ABONADO > APROBADO > PENDIENTE
                const hasPaid = dayReservations.some(res => res.estado_pago === 'pagada');
                const hasAbonada = dayReservations.some(res => {
                    const estado = (res.estado || '').toLowerCase();
                    return estado === 'abonada';
                });
                const hasApproved = dayReservations.some(res => {
                    const estado = (res.estado || '').toLowerCase();
                    return estado === 'aprobada' && res.estado_pago !== 'pagada';
                });
                const hasPending = dayReservations.some(res => {
                    const estado = (res.estado || '').toLowerCase();
                    return estado === 'pendiente';
                });

                if (hasPaid) {
                    dayElement.classList.add('paid');
                    dayElement.style.backgroundColor = '#28a745';
                    dayElement.style.color = '#000000';
                    dayElement.style.borderLeft = '4px solid #28a745';
                } else if (hasAbonada) {
                    dayElement.classList.add('abonada');
                    dayElement.style.backgroundColor = '#ff8c00';
                    dayElement.style.color = '#000000';
                    dayElement.style.borderLeft = '4px solid #ff8c00';
                } else if (hasApproved) {
                    dayElement.classList.add('approved');
                    dayElement.style.backgroundColor = '#007bff';
                    dayElement.style.color = '#000000';
                    dayElement.style.borderLeft = '4px solid #007bff';
                } else if (hasPending) {
                    dayElement.classList.add('pending');
                    dayElement.style.backgroundColor = '#ffc107';
                    dayElement.style.color = '#000000';
                    dayElement.style.borderLeft = '4px solid #ffc107';
                }

                dayReservations.forEach(res => {
                    const resInfo = document.createElement('div');
                    // Normalizar el estado a minúsculas para la clase CSS
                    const estadoNormalizado = (res.estado || '').toLowerCase();
                    resInfo.className = `reservation-info ${estadoNormalizado}`;

                    // Mostrar estado de pago si está pagado, sino mostrar estado normal
                    let estadoTexto = '';
                    if (res.estado_pago === 'pagada') {
                        estadoTexto = 'PAGADA';
                    } else if (estadoNormalizado === 'abonada') {
                        estadoTexto = 'ABONADA';
                    } else {
                        estadoTexto = (res.estado || '').toUpperCase();
                    }
                    resInfo.textContent = `${res.nombre} (${estadoTexto})`;
                    resInfo.title = `Reserva #${res.id_reserva} - ${res.nombre} ${res.apellido}`;
                    dayElement.appendChild(resInfo);
                });
            }

            // Event listener para mostrar detalles
            // Solo permitir click si no es día pasado
            if (!dayElement.classList.contains('past')) {
                dayElement.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    showDayDetails(date, dayReservations, isBlocked, dateString);
                });
            }

            calendarGrid.appendChild(dayElement);
        }

        // Días del mes siguiente
        const nextMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1);
        const remainingDays = 42 - (startingDayOfWeek + daysInMonth);
        for (let day = 1; day <= remainingDays; day++) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day other-month';
            dayElement.textContent = day;
            calendarGrid.appendChild(dayElement);
        }

        calendar.appendChild(calendarGrid);
    }

    // Configurar eventos del calendario
    function setupCalendarEvents() {
        document.getElementById('prevMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            loadCalendarData();
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            loadCalendarData();
        });

        // Botón de actualizar calendario
        document.getElementById('refreshCalendar').addEventListener('click', async () => {
            const refreshBtn = document.getElementById('refreshCalendar');
            const originalText = refreshBtn.innerHTML;

            // Mostrar estado de carga
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
            refreshBtn.disabled = true;

            try {
                // Recargar datos del calendario
                loadCalendarData();

                // Mostrar mensaje de éxito
                refreshBtn.innerHTML = '<i class="fas fa-check"></i> Actualizado';
                refreshBtn.classList.remove('btn-outline-success');
                refreshBtn.classList.add('btn-success');

                // Restaurar botón después de 2 segundos
                setTimeout(() => {
                    refreshBtn.innerHTML = originalText;
                    refreshBtn.classList.remove('btn-success');
                    refreshBtn.classList.add('btn-outline-success');
                    refreshBtn.disabled = false;
                }, 2000);

            } catch (error) {
                console.error('Error actualizando calendario:', error);

                // Mostrar error
                refreshBtn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error';
                refreshBtn.classList.remove('btn-outline-success');
                refreshBtn.classList.add('btn-danger');

                // Restaurar botón después de 3 segundos
                setTimeout(() => {
                    refreshBtn.innerHTML = originalText;
                    refreshBtn.classList.remove('btn-danger');
                    refreshBtn.classList.add('btn-outline-success');
                    refreshBtn.disabled = false;
                }, 3000);
            }
        });
    }

    // Mostrar detalles del día
    function showDayDetails(date, reservations, isBlocked, dateStringISO) {
        const dateString = date.toLocaleDateString('es-CO');
        let content = `<h5><i class="fas fa-calendar-day me-2"></i>${dateString}</h5>`;

        if (isBlocked) {
            // Buscar información del bloqueo
            const blockedInfo = blockedDates.find(blocked => {
                const blockedStart = new Date(blocked.fecha_inicio);
                const blockedEnd = new Date(blocked.fecha_fin);
                const selectedDate = new Date(dateStringISO);
                return selectedDate >= blockedStart && selectedDate <= blockedEnd && blocked.activo;
            });
            const isExternalIcal = blockedInfo && blockedInfo.origen === 'ical';
            const blockReasonText = isExternalIcal ? 'Reserva externa sincronizada (iCal)' : (blockedInfo ? blockedInfo.motivo : 'No especificado');
            const blockDescriptionText = blockedInfo ? (blockedInfo.descripcion || 'Sin descripción') : 'Sin información';

            content += `
                    <div class="alert alert-danger">
                        <i class="fas fa-ban me-2"></i><strong>Fecha Bloqueada</strong>
                        <hr>
                        <p class="mb-1"><strong>Motivo:</strong> ${blockReasonText}</p>
                        <p class="mb-1"><strong>Descripción:</strong> ${blockDescriptionText}</p>
                        <p class="mb-3"><strong>Bloqueado desde:</strong> ${blockedInfo ? new Date(blockedInfo.fecha_inicio).toLocaleDateString('es-CO') : 'N/A'}</p>
                        ${blockedInfo && blockedInfo.fecha_fin ? `<p class="mb-3"><strong>Hasta:</strong> ${new Date(blockedInfo.fecha_fin).toLocaleDateString('es-CO')}</p>` : ''}
                        ${isExternalIcal
                ? '<div class="alert alert-warning mb-0"><i class="fas fa-sync-alt me-2"></i>Este bloqueo proviene de iCal (Airbnb/Booking). Debe liberarse en la plataforma de origen.</div>'
                : `<button class="btn btn-success btn-sm w-100" onclick="unblockDate('${dateStringISO}')"><i class="fas fa-unlock me-2"></i>Desbloquear Fecha</button>`}
                    </div>
                `;
        }

        if (reservations.length > 0) {
            content += '<h6><i class="fas fa-calendar-check me-2"></i>Reservas:</h6><ul class="list-group">';
            reservations.forEach(res => {
                const estado = (res.estado || '').toLowerCase();
                const estadoColor = res.estado_pago === 'pagada' ? 'success' :
                    estado === 'abonada' ? 'warning' :
                        estado === 'aprobada' ? 'primary' :
                            estado === 'pendiente' ? 'warning' : 'secondary';

                const estadoTexto = res.estado_pago === 'pagada' ? 'PAGADA' :
                    estado === 'abonada' ? 'ABONADA' :
                        estado === 'aprobada' ? 'APROBADA' :
                            estado === 'pendiente' ? 'PENDIENTE' : (res.estado || '').toUpperCase();

                content += `
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>${res.nombre} ${res.apellido}</strong><br>
                                    <small class="text-muted">ID: ${res.id_reserva}</small><br>
                                    <small class="text-muted">Email: ${res.correo || 'No disponible'}</small><br>
                                    <small class="text-muted">Teléfono: ${res.telefono || 'No disponible'}</small><br>
                                    <small class="text-muted">Fechas: ${res.fecha_entrada} - ${res.fecha_salida}</small><br>
                                    <small class="text-muted">Adultos: ${res.num_adultos || 1} | Niños: ${res.num_ninos || 0}</small><br>
                                    <small class="text-muted">Método de pago: ${res.metodo_pago}</small><br>
                                    <small class="text-muted">Total: $${parseFloat(res.total).toLocaleString('es-CO')} COP</small>
                                    ${res.comentario ? `<br><small class="text-muted"><strong>Comentario:</strong> ${res.comentario}</small>` : ''}
                                </div>
                                <span class="badge bg-${estadoColor}">${estadoTexto}</span>
                            </div>
                        </li>
                    `;
            });
            content += '</ul>';
        } else if (!isBlocked) {
            // Fecha disponible - mostrar formulario para bloquear
            const dateISO = dateStringISO || date.toISOString().split('T')[0];
            content += `
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-check me-2"></i><strong>Fecha disponible para reservas</strong>
                    </div>
                    <hr>
                    <h6 class="mb-3"><i class="fas fa-calendar-times me-2"></i>Bloquear esta fecha</h6>
                    <form id="blockDateFormInModal">
                        <!-- Tipo de bloqueo -->
                        <div class="mb-3">
                            <label class="form-label">Tipo de Bloqueo</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="blockTypeInModal" id="blockSingleInModal" value="single" checked>
                                <label class="form-check-label" for="blockSingleInModal">
                                    <i class="fas fa-calendar-day me-2"></i>Bloquear solo este día
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="blockTypeInModal" id="blockRangeInModal" value="range">
                                <label class="form-check-label" for="blockRangeInModal">
                                    <i class="fas fa-calendar-week me-2"></i>Bloquear rango de fechas (desde este día)
                                </label>
                            </div>
                        </div>

                        <!-- Fecha única (prellenada) -->
                        <div class="mb-3" id="singleDateGroupInModal">
                            <label for="blockSingleDateInModal" class="form-label">Fecha a Bloquear</label>
                            <input type="date" class="form-control" id="blockSingleDateInModal" value="${dateISO}" readonly>
                        </div>

                        <!-- Rango de fechas -->
                        <div class="mb-3" id="rangeDateGroupInModal" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="blockStartDateInModal" class="form-label">Fecha de Inicio</label>
                                    <input type="date" class="form-control" id="blockStartDateInModal" value="${dateISO}">
                                </div>
                                <div class="col-md-6">
                                    <label for="blockEndDateInModal" class="form-label">Fecha de Fin</label>
                                    <input type="date" class="form-control" id="blockEndDateInModal">
                                </div>
                            </div>
                        </div>

                        <!-- Motivo del bloqueo -->
                        <div class="mb-3">
                            <label for="blockReasonInModal" class="form-label">Motivo del Bloqueo *</label>
                            <select class="form-control" id="blockReasonInModal" required>
                                <option value="">Seleccionar motivo...</option>
                                <option value="mantenimiento">🔧 Mantenimiento</option>
                                <option value="uso_interno">🏢 Uso Interno</option>
                                <option value="evento_especial">🎉 Evento Especial</option>
                                <option value="limpieza">🧹 Limpieza Profunda</option>
                                <option value="reparacion">🔨 Reparaciones</option>
                                <option value="otro">📝 Otro</option>
                            </select>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="blockDescriptionInModal" class="form-label">Descripción (Opcional)</label>
                            <textarea class="form-control" id="blockDescriptionInModal" rows="3" 
                                placeholder="Detalles adicionales sobre el bloqueo..."></textarea>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Información:</strong> No se pueden bloquear fechas que ya tienen reservas aprobadas.
                        </div>
                    </form>
                `;
        }

        // Crear modal bonito y estético
        const modalHtml = `
                <div class="modal fade" id="dayDetailsModal" tabindex="-1" aria-labelledby="dayDetailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="dayDetailsModalLabel">
                                    <i class="fas fa-calendar-day me-2"></i>Detalles del ${dateString}
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ${content}
                            </div>
                            <div class="modal-footer">
                                ${!isBlocked && reservations.length === 0 ? `
                                    <button type="button" class="btn btn-danger" onclick="blockDateFromModal('${dateStringISO}', this)">
                                        <i class="fas fa-lock me-2"></i>Bloquear Fecha
                                    </button>
                                ` : ''}
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i>Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

        // Remover modal anterior si existe
        const existingModal = document.getElementById('dayDetailsModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Agregar nuevo modal al body
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('dayDetailsModal'));
        modal.show();

        // Limpiar modal cuando se cierre
        document.getElementById('dayDetailsModal').addEventListener('hidden.bs.modal', function () {
            this.remove();
        });

        // Configurar eventos para el formulario de bloqueo dentro del modal
        if (!isBlocked && reservations.length === 0) {
            // Mostrar/ocultar campos según tipo de bloqueo
            setTimeout(() => {
                const blockSingleRadio = document.getElementById('blockSingleInModal');
                const blockRangeRadio = document.getElementById('blockRangeInModal');
                const singleDateGroup = document.getElementById('singleDateGroupInModal');
                const rangeDateGroup = document.getElementById('rangeDateGroupInModal');

                if (blockSingleRadio && blockRangeRadio) {
                    blockSingleRadio.addEventListener('change', function() {
                        if (this.checked) {
                            singleDateGroup.style.display = 'block';
                            rangeDateGroup.style.display = 'none';
                        }
                    });

                    blockRangeRadio.addEventListener('change', function() {
                        if (this.checked) {
                            singleDateGroup.style.display = 'none';
                            rangeDateGroup.style.display = 'block';
                        }
                    });
                }
            }, 100);
        }
    }

    // Función para bloquear fecha desde el modal de detalles
    function blockDateFromModal(defaultDate, buttonElement) {
        const blockType = document.querySelector('input[name="blockTypeInModal"]:checked')?.value || 'single';
        const reason = document.getElementById('blockReasonInModal')?.value;
        const description = document.getElementById('blockDescriptionInModal')?.value || '';

        let startDate, endDate;

        if (blockType === 'single') {
            startDate = document.getElementById('blockSingleDateInModal')?.value || defaultDate;
            endDate = startDate;
        } else {
            startDate = document.getElementById('blockStartDateInModal')?.value || defaultDate;
            endDate = document.getElementById('blockEndDateInModal')?.value;
        }

        // Validaciones
        if (!startDate || !reason) {
            alert('Por favor complete todos los campos obligatorios');
            return;
        }

        if (blockType === 'range' && !endDate) {
            alert('Para bloquear un rango debe proporcionar fecha de fin');
            return;
        }

        if (blockType === 'range' && new Date(startDate) > new Date(endDate)) {
            alert('La fecha de inicio debe ser anterior a la fecha de fin');
            return;
        }

        if (new Date(startDate) < new Date()) {
            alert('No se pueden bloquear fechas pasadas');
            return;
        }

        const formData = new FormData();
        formData.append('start_date', startDate);
        formData.append('end_date', endDate);
        formData.append('reason', reason);
        formData.append('description', description);
        formData.append('block_type', blockType);

        // Mostrar loading
        const submitBtn = buttonElement;
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Bloqueando...';
        submitBtn.disabled = true;

        fetch('../../../app/api/admin/block_dates.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const message = blockType === 'single'
                        ? `✅ Fecha bloqueada exitosamente: ${new Date(startDate).toLocaleDateString('es-CO')}`
                        : `✅ Rango bloqueado exitosamente: ${new Date(startDate).toLocaleDateString('es-CO')} - ${new Date(endDate).toLocaleDateString('es-CO')}`;

                    alert(message);

                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('dayDetailsModal'));
                    if (modal) {
                        modal.hide();
                    }

                    // Recargar calendario
                    loadCalendarData();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error al bloquear las fechas');
            })
            .finally(() => {
                // Restaurar botón
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    }

    // Función para desbloquear una fecha
    function unblockDate(dateString) {
        if (!confirm(`¿Estás seguro de que deseas desbloquear la fecha ${new Date(dateString).toLocaleDateString('es-CO')}?`)) {
            return;
        }

        const formData = new FormData();
        formData.append('date', dateString);

        fetch('../../../app/api/admin/unblock_dates.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('dayDetailsModal'));
                    if (modal) {
                        modal.hide();
                    }
                    // Recargar calendario
                    loadCalendarData();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error al desbloquear la fecha');
            });
    }

    // Actualizar estadísticas
    function updateStats() {
        // Obtener rango del mes visible
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const firstDayOfMonth = new Date(year, month, 1);
        const lastDayOfMonth = new Date(year, month + 1, 0);

        // Filtrar reservas que están en el mes visible
        const monthReservations = reservations.filter(res => {
            const resStart = new Date(res.fecha_entrada);
            const resEnd = new Date(res.fecha_salida);
            // Reserva está en el mes si comienza, termina o cruza el mes
            return (resStart <= lastDayOfMonth && resEnd >= firstDayOfMonth);
        });

        // Calcular noches ocupadas solo para el mes visible
        const monthNights = monthReservations.reduce((total, res) => {
            const start = new Date(res.fecha_entrada);
            const end = new Date(res.fecha_salida);

            // Ajustar fechas al rango del mes
            const adjustedStart = start < firstDayOfMonth ? firstDayOfMonth : start;
            const adjustedEnd = end > lastDayOfMonth ? lastDayOfMonth : end;

            const nights = Math.ceil((adjustedEnd - adjustedStart) / (1000 * 60 * 60 * 24));
            return total + (nights > 0 ? nights : 0);
        }, 0);

        // Contar solo registros únicos de bloqueos activos que están en el mes visible
        // El backend ya filtra por activo = 1, así que solo verificamos que esté en el mes
        const monthBlocked = blockedDates.filter(blocked => {
            // Verificar que el bloqueo esté activo (el backend ya filtra, pero por seguridad)
            const activoValue = blocked.activo;

            // Solo contar si está explícitamente activo
            if (activoValue !== 1 && activoValue !== '1' && activoValue !== true && activoValue !== 'true') {
                return false;
            }

            try {
                const blockedStart = new Date(blocked.fecha_inicio);
                const blockedEnd = new Date(blocked.fecha_fin);

                // Validar que las fechas sean válidas
                if (isNaN(blockedStart.getTime()) || isNaN(blockedEnd.getTime())) {
                    return false;
                }

                // Normalizar horas para comparación precisa
                blockedStart.setHours(0, 0, 0, 0);
                blockedEnd.setHours(0, 0, 0, 0);
                const firstDay = new Date(firstDayOfMonth);
                firstDay.setHours(0, 0, 0, 0);
                const lastDay = new Date(lastDayOfMonth);
                lastDay.setHours(23, 59, 59, 999);

                // Bloqueo está en el mes si comienza, termina o cruza el mes
                return (blockedStart <= lastDay && blockedEnd >= firstDay);
            } catch (e) {
                return false;
            }
        }).length;

        // Calcular ocupación (días del mes)
        const daysInMonth = lastDayOfMonth.getDate();
        const monthOccupancy = daysInMonth > 0 ? Math.round((monthNights / daysInMonth) * 100) : 0;

        document.getElementById('monthReservations').textContent = monthReservations.length;
        document.getElementById('monthNights').textContent = monthNights;
        document.getElementById('monthBlocked').textContent = monthBlocked;
        document.getElementById('monthOccupancy').textContent = monthOccupancy + '%';
    }
</script>
</body>
</html>

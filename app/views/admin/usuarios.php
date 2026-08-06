<?php
/**
 * Gestión de Usuarios - Panel de Administración
 * My Suite In Cartagena
 */

session_start();

// Verificar si el usuario está logueado como admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../../../app/controllers/auth/login.php');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

$mensaje = '';
$tipo_mensaje = '';

// Obtener filtros
$busqueda = $_GET['busqueda'] ?? '';

// Construir consulta
$where_conditions = [];
$params = [];

if ($busqueda) {
    $where_conditions[] = "(u.nombre LIKE ? OR u.apellido LIKE ? OR u.correo LIKE ? OR u.telefono LIKE ?)";
    $search_term = "%{$busqueda}%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Obtener clientes
$query = "SELECT 
            u.id_usuario,
            u.nombre,
            u.apellido,
            u.correo,
            u.telefono,
            u.fecha_nacimiento,
            u.creado_en as fecha_registro,
            u.ciudad,
            COUNT(r.id_reserva) as total_reservas,
            SUM(CASE WHEN r.estado = 'aprobada' THEN 1 ELSE 0 END) as reservas_aprobadas,
            SUM(CASE WHEN r.estado = 'pendiente' THEN 1 ELSE 0 END) as reservas_pendientes
          FROM usuarios u
          LEFT JOIN reservas r ON u.id_usuario = r.id_usuario
          WHERE u.rol = 'cliente'
          " . ($where_clause ? "AND " . str_replace('WHERE', '', $where_clause) : "") . "
          GROUP BY u.id_usuario
          ORDER BY u.creado_en DESC";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Error de conexión a la base de datos");
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $clientes = [];
    $mensaje = 'Error al cargar clientes: ' . $e->getMessage();
    $tipo_mensaje = 'danger';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios Registrados - Panel de Administración</title>
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
                            <a class="nav-link active" href="usuarios.php">
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
                    <h1 class="h2"><i class="fas fa-users me-2"></i> Usuarios Registrados</h1>
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
                            <div class="col-md-10">
                                <label for="busqueda" class="form-label">Buscar Cliente</label>
                                <input type="text" class="form-control" id="busqueda" name="busqueda" 
                                       placeholder="Buscar por nombre, apellido, email o teléfono..." 
                                       value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Buscar
                                    </button>
                                </div>
                            </div>
                            <?php if ($busqueda): ?>
                            <div class="col-md-12">
                                <a href="usuarios.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Limpiar Filtros
                                </a>
                            </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Lista de Clientes -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i> Lista de Clientes
                            <span class="badge bg-primary ms-2"><?php echo count($clientes); ?> clientes</span>
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
                                        <th>Fecha Nacimiento</th>
                                        <th>Ciudad</th>
                                        <th>Total Reservas</th>
                                        <th>Aprobadas</th>
                                        <th>Pendientes</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($clientes)): ?>
                                        <?php foreach ($clientes as $cliente): ?>
                                            <tr>
                                                <td>#<?php echo $cliente['id_usuario']; ?></td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?></strong>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <i class="fas fa-envelope me-1"></i>
                                                        <small><?php echo htmlspecialchars($cliente['correo']); ?></small>
                                                        <br>
                                                        <i class="fas fa-phone me-1"></i>
                                                        <small><?php echo htmlspecialchars($cliente['telefono'] ?? 'No registrado'); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php echo $cliente['fecha_nacimiento'] ? date('d/m/Y', strtotime($cliente['fecha_nacimiento'])) : '<span class="text-muted">No registrada</span>'; ?>
                                                </td>
                                                <td>
                                                    <?php if ($cliente['ciudad']): ?>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($cliente['ciudad']); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo $cliente['total_reservas'] ?? 0; ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success"><?php echo $cliente['reservas_aprobadas'] ?? 0; ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-warning"><?php echo $cliente['reservas_pendientes'] ?? 0; ?></span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-outline-info btn-action" 
                                                                onclick="verDetalle(<?php echo $cliente['id_usuario']; ?>)" title="Ver Detalle">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <br>No se encontraron clientes
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal de Detalle de Cliente -->
    <div class="modal fade" id="detalleClienteModal" tabindex="-1" aria-labelledby="detalleClienteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detalleClienteModalLabel">
                        <i class="fas fa-user me-2"></i>Detalle de Cliente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="detalleClienteContent">
                        <!-- El contenido se carga dinámicamente -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verDetalle(id) {
            // Mostrar loading
            document.getElementById('detalleClienteContent').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando detalles del cliente...</p>
                </div>
            `;
            
            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('detalleClienteModal'));
            modal.show();
            
            // Cargar datos del cliente
            fetch(`../../../app/api/admin/get_client_details.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarDetalleCliente(data.client, data.reservations || []);
                    } else {
                        document.getElementById('detalleClienteContent').innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error al cargar los detalles: ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    document.getElementById('detalleClienteContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error de conexión: ${error.message}
                        </div>
                    `;
                });
        }
        
        function mostrarDetalleCliente(cliente, reservas) {
            document.getElementById('detalleClienteContent').innerHTML = `
                <div class="row">
                    <!-- Información General -->
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-user me-2"></i>Información Personal
                        </h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>ID:</strong></td>
                                <td><span class="badge bg-secondary">#${cliente.id_usuario}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Nombre:</strong></td>
                                <td>${cliente.nombre} ${cliente.apellido}</td>
                            </tr>
                            <tr>
                                <td><strong>Correo:</strong></td>
                                <td><a href="mailto:${cliente.correo}" class="text-decoration-none">${cliente.correo}</a></td>
                            </tr>
                            <tr>
                                <td><strong>Teléfono:</strong></td>
                                <td>${cliente.telefono || '<span class="text-muted">No registrado</span>'}</td>
                            </tr>
                            <tr>
                                <td><strong>Fecha de Nacimiento:</strong></td>
                                <td>${cliente.fecha_nacimiento || '<span class="text-muted">No registrada</span>'}</td>
                            </tr>
                            <tr>
                                <td><strong>Ciudad:</strong></td>
                                <td>${cliente.ciudad ? '<span class="badge bg-info">' + cliente.ciudad + '</span>' : '<span class="text-muted">No registrada</span>'}</td>
                            </tr>
                            <tr>
                                <td><strong>Fecha Registro:</strong></td>
                                <td>${cliente.fecha_registro || cliente.creado_en || 'N/A'}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Estadísticas -->
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-chart-bar me-2"></i>Estadísticas
                        </h6>
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <h4 class="text-primary mb-0">${cliente.total_reservas || 0}</h4>
                                    <small class="text-muted">Total Reservas</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <h4 class="text-success mb-0">${cliente.reservas_aprobadas || cliente.reservas_confirmadas || 0}</h4>
                                    <small class="text-muted">Aprobadas</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <h4 class="text-warning mb-0">${cliente.reservas_pendientes || 0}</h4>
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
                
                <hr>
                
                <!-- Historial de Reservas -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-calendar-check me-2"></i>Historial de Reservas
                        </h6>
                        ${reservas.length > 0 ? `
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
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
                                        ${reservas.map(res => `
                                            <tr>
                                                <td><span class="badge bg-secondary">#${res.id_reserva}</span></td>
                                                <td>${new Date(res.fecha_entrada).toLocaleDateString('es-CO')}</td>
                                                <td>${new Date(res.fecha_salida).toLocaleDateString('es-CO')}</td>
                                                <td><span class="badge ${res.estado === 'aprobada' ? 'bg-success' : res.estado === 'pendiente' ? 'bg-warning' : res.estado === 'rechazada' ? 'bg-danger' : 'bg-secondary'}">${res.estado}</span></td>
                                                <td class="fw-bold">$${parseFloat(res.total).toLocaleString('es-CO')} COP</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        ` : '<div class="text-center py-4"><i class="fas fa-calendar-times fa-3x text-muted mb-3"></i><p class="text-muted fs-5">No hay reservas registradas</p></div>'}
                    </div>
                </div>
            `;
        }
    </script>
</body>
</html>

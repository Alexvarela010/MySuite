<?php
/**
 * Panel de Administración - Gestión de Tarifas de Manillas
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
    header('Location: ../../app/controllers/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Manillas - My Suite In Cartagena</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oxygen:wght@300;400;700&family=Crimson+Text:wght@400;600;700&family=Abril+Fatface&display=swap" rel="stylesheet">
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
        
        /* Navbar superior */
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
        
        .container-fluid {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
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
        
        .form-section {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
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
                            <a class="nav-link" href="descuentos.php">
                                <i class="fas fa-percentage me-2"></i> Descuentos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="manillas.php">
                                <i class="fas fa-bracelet me-2"></i> Manillas
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
                    <h1 class="h2"><i class="fas fa-bracelet me-2"></i> Gestión de Tarifas de Manillas</h1>
                    <button class="btn btn-primary" onclick="mostrarFormularioNuevo()">
                        <i class="fas fa-plus me-2"></i>Nueva Tarifa
                    </button>
                </div>

                <!-- Formulario de entrada -->
                <div class="card mb-4" id="formularioSection" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0" id="formularioTitulo">Nueva Tarifa de Manilla</h5>
                    </div>
                    <div class="card-body">
                        <form id="formularioManillas">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="personasDesde" class="form-label">Personas Desde <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="personasDesde" min="1" required>
                                    <small class="text-muted">Cantidad mínima de personas</small>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="personasHasta" class="form-label">Personas Hasta</label>
                                    <input type="number" class="form-control" id="personasHasta" min="1">
                                    <small class="text-muted">Dejar vacío para "en adelante"</small>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="precio" class="form-label">Precio (COP) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="precio" step="1000" min="0" required>
                                    <small class="text-muted">Ejemplo: 70000</small>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="activo" class="form-label">Estado</label>
                                    <select class="form-control" id="activo">
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="alert alert-info" role="alert">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Ejemplo:</strong> Si estableces "Personas Desde: 1" y "Personas Hasta: 7", el precio de $70.000 se aplicará a grupos de 1 a 7 personas. Si estableces "Personas Desde: 8" y dejas "Personas Hasta" vacío, el precio de $90.000 se aplicará para 8 personas o más.
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-success" onclick="guardarTarifa()">
                                        <i class="fas fa-save me-2"></i>Guardar Tarifa
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="cancelarFormulario()">
                                        <i class="fas fa-times me-2"></i>Cancelar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de tarifas -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Tarifas Registradas</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tablaTarifas">
                                <thead>
                                    <tr>
                                        <th>Personas Desde</th>
                                        <th>Personas Hasta</th>
                                        <th>Precio (COP)</th>
                                        <th>Estado</th>
                                        <th>Creado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tBody">
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="spinner-border" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                            <p class="mt-2">Cargando tarifas...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let tarifaEditando = null;

        // Cargar tarifas al iniciar
        $(document).ready(function() {
            cargarTarifas();
        });

        // Cargar tarifas desde la API
        function cargarTarifas() {
            fetch('/app/api/admin/get_manillas_tarifas.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('tBody');
                    
                    if (data.success && data.tarifas.length > 0) {
                        tbody.innerHTML = '';
                        
                        data.tarifas.forEach(tarifa => {
                            const row = document.createElement('tr');
                            const personasHasta = tarifa.personas_hasta 
                                ? tarifa.personas_hasta 
                                : '<span class="badge bg-info">En adelante</span>';
                            
                            const estado = tarifa.activo 
                                ? '<span class="badge bg-success">Activo</span>'
                                : '<span class="badge bg-secondary">Inactivo</span>';
                            
                            row.innerHTML = `
                                <td><span class="badge bg-primary">${tarifa.personas_desde}</span></td>
                                <td>${personasHasta}</td>
                                <td><strong>$${parseFloat(tarifa.precio).toLocaleString('es-CO')}</strong></td>
                                <td>${estado}</td>
                                <td><small class="text-muted">${new Date(tarifa.creado_en).toLocaleDateString('es-CO')}</small></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editarTarifa(${tarifa.id_tarifa}, ${tarifa.personas_desde}, ${tarifa.personas_hasta || 'null'}, ${tarifa.precio}, ${tarifa.activo})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="eliminarTarifa(${tarifa.id_tarifa})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No hay tarifas registradas</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('tBody').innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Error al cargar las tarifas</td></tr>';
                });
        }

        // Mostrar formulario para nueva tarifa
        function mostrarFormularioNuevo() {
            tarifaEditando = null;
            document.getElementById('formularioTitulo').textContent = 'Nueva Tarifa de Manilla';
            document.getElementById('formularioManillas').reset();
            document.getElementById('formularioSection').style.display = 'block';
            document.getElementById('formularioSection').scrollIntoView({ behavior: 'smooth' });
        }

        // Editar tarifa
        function editarTarifa(id, desde, hasta, precio, activo) {
            tarifaEditando = id;
            document.getElementById('formularioTitulo').textContent = 'Editar Tarifa de Manilla';
            document.getElementById('personasDesde').value = desde;
            document.getElementById('personasHasta').value = hasta || '';
            document.getElementById('precio').value = precio;
            document.getElementById('activo').value = activo;
            document.getElementById('formularioSection').style.display = 'block';
            document.getElementById('formularioSection').scrollIntoView({ behavior: 'smooth' });
        }

        // Guardar tarifa
        function guardarTarifa() {
            const personasDesde = document.getElementById('personasDesde').value;
            const personasHasta = document.getElementById('personasHasta').value;
            const precio = document.getElementById('precio').value;
            const activo = document.getElementById('activo').value;

            if (!personasDesde || !precio) {
                alert('Por favor completa todos los campos obligatorios');
                return;
            }

            const datos = {
                id_tarifa: tarifaEditando,
                personas_desde: personasDesde,
                personas_hasta: personasHasta || null,
                precio: precio,
                activo: activo
            };

            fetch('/app/api/admin/save_manillas_tarifa.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(datos)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    cancelarFormulario();
                    cargarTarifas();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar la tarifa');
            });
        }

        // Eliminar tarifa
        function eliminarTarifa(id) {
            if (confirm('¿Estás seguro de que quieres eliminar esta tarifa?')) {
                fetch('/app/api/admin/delete_manillas_tarifa.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id_tarifa: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        cargarTarifas();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar la tarifa');
                });
            }
        }

        // Cancelar formulario
        function cancelarFormulario() {
            document.getElementById('formularioSection').style.display = 'none';
            document.getElementById('formularioManillas').reset();
            tarifaEditando = null;
        }
    </script>
</body>
</html>


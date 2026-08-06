<?php
/**
 * Panel de Administración - Gestión de Servicios Adicionales
 * Early Check-in y Late Checkout
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
    <title>Gestión de Servicios Adicionales - My Suite In Cartagena</title>
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
        }
        
        .btn-primary:hover {
            background-color: #1e5bb8;
            border-color: #1e5bb8;
        }
        
        .form-section {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
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
                            <i class="fas fa-ring me-2"></i> Manillas
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
                    <h1 class="h2"><i class="fas fa-concierge-bell me-2"></i> Gestión de Servicios Adicionales</h1>
                    <button class="btn btn-primary" onclick="mostrarFormularioNuevo()">
                        <i class="fas fa-plus me-2"></i>Nuevo Servicio
                    </button>
                </div>

                <!-- Formulario de entrada -->
                <div class="card mb-4" id="formularioSection" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0" id="formularioTitulo">Nuevo Servicio Adicional</h5>
                    </div>
                    <div class="card-body">
                        <form id="formularioServicios">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre del Servicio <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre" placeholder="Ej: Early Check-in" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                                    <select class="form-control" id="tipo" required>
                                        <option value="">-- Seleccionar --</option>
                                        <option value="early_checkin">Early Check-in</option>
                                        <option value="late_checkout">Late Checkout</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="precio" class="form-label">Precio (COP) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="precio" step="1000" min="0" placeholder="Ej: 50000" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="activo" class="form-label">Estado</label>
                                    <select class="form-control" id="activo">
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" rows="3" placeholder="Describe el servicio..."></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-success" onclick="guardarServicio()">
                                        <i class="fas fa-save me-2"></i>Guardar Servicio
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="cancelarFormulario()">
                                        <i class="fas fa-times me-2"></i>Cancelar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de servicios -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Servicios Registrados</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tablaServicios">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Precio (COP)</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Creado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tBody">
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="spinner-border" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                            <p class="mt-2">Cargando servicios...</p>
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
        let servicioEditando = null;

        $(document).ready(function() {
            cargarServicios();
        });

        function cargarServicios() {
            fetch('/app/api/admin/get_servicios_adicionales.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('tBody');
                    
                    if (data.success && data.servicios.length > 0) {
                        tbody.innerHTML = '';
                        
                        data.servicios.forEach(servicio => {
                            const row = document.createElement('tr');
                            const estado = servicio.activo 
                                ? '<span class="badge bg-success">Activo</span>'
                                : '<span class="badge bg-secondary">Inactivo</span>';
                            
                            const tipo = servicio.tipo === 'early_checkin' ? 'Early Check-in' : 'Late Checkout';
                            
                            row.innerHTML = `
                                <td><strong>${servicio.nombre}</strong></td>
                                <td>${tipo}</td>
                                <td>$${parseFloat(servicio.precio).toLocaleString('es-CO')}</td>
                                <td><small>${servicio.descripcion || '-'}</small></td>
                                <td>${estado}</td>
                                <td><small class="text-muted">${new Date(servicio.creado_en).toLocaleDateString('es-CO')}</small></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editarServicio(${servicio.id_servicio}, '${servicio.nombre}', '${servicio.tipo}', ${servicio.precio}, '${servicio.descripcion || ''}', ${servicio.activo})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="eliminarServicio(${servicio.id_servicio})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No hay servicios registrados</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('tBody').innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Error al cargar los servicios</td></tr>';
                });
        }

        function mostrarFormularioNuevo() {
            servicioEditando = null;
            document.getElementById('formularioTitulo').textContent = 'Nuevo Servicio Adicional';
            document.getElementById('formularioServicios').reset();
            document.getElementById('formularioSection').style.display = 'block';
            document.getElementById('formularioSection').scrollIntoView({ behavior: 'smooth' });
        }

        function editarServicio(id, nombre, tipo, precio, descripcion, activo) {
            servicioEditando = id;
            document.getElementById('formularioTitulo').textContent = 'Editar Servicio Adicional';
            document.getElementById('nombre').value = nombre;
            document.getElementById('tipo').value = tipo;
            document.getElementById('precio').value = precio;
            document.getElementById('descripcion').value = descripcion;
            document.getElementById('activo').value = activo;
            document.getElementById('formularioSection').style.display = 'block';
            document.getElementById('formularioSection').scrollIntoView({ behavior: 'smooth' });
        }

        function guardarServicio() {
            const nombre = document.getElementById('nombre').value;
            const tipo = document.getElementById('tipo').value;
            const precio = document.getElementById('precio').value;
            const descripcion = document.getElementById('descripcion').value;
            const activo = document.getElementById('activo').value;

            if (!nombre || !tipo || !precio) {
                alert('Por favor completa todos los campos obligatorios');
                return;
            }

            const datos = {
                id_servicio: servicioEditando,
                nombre: nombre,
                tipo: tipo,
                precio: precio,
                descripcion: descripcion,
                activo: activo
            };

            fetch('/app/api/admin/save_servicio_adicional.php', {
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
                    cargarServicios();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar el servicio');
            });
        }

        function eliminarServicio(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este servicio?')) {
                fetch('/app/api/admin/delete_servicio_adicional.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id_servicio: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        cargarServicios();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el servicio');
                });
            }
        }

        function cancelarFormulario() {
            document.getElementById('formularioSection').style.display = 'none';
            document.getElementById('formularioServicios').reset();
            servicioEditando = null;
        }
    </script>
</body>
</html>


<?php
/**
 * Gestión de Tarifas por Fecha
 * Panel de Administración
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

$database = new Database();
$pdo = $database->getConnection();

// Obtener tarifas del mes actual y siguientes
$mes_actual = date('Y-m');
$fecha_inicio = date('Y-m-01');
$fecha_fin = date('Y-m-t', strtotime('+2 months'));

$tarifas = [];
if ($pdo) {
    try {
        $query = "
            SELECT fecha, precio, temporada 
            FROM tarifas 
            WHERE id_apartamento = 1 
            AND fecha BETWEEN :fecha_inicio AND :fecha_fin
            ORDER BY fecha ASC
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt->bindParam(':fecha_fin', $fecha_fin);
        $stmt->execute();
        $tarifas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $tarifas = [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tarifas - Panel de Administración</title>
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
        
        .calendar-header {
            background: #f8f9fa;
            padding: 10px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        .calendar-day-empty {
            padding: 10px;
            border: 1px solid #dee2e6;
            background: #f8f9fa;
        }
        .calendar-day-price {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            min-height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .calendar-day-price:hover {
            background: #e3f2fd !important;
            border-color: #2196f3;
        }
        .calendar-day-price.edited {
            background: #fff3cd !important;
            border-color: #ffc107;
        }
        .price-input-modal .form-control {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .calendar-day-price .price-display {
            font-size: 0.75rem;
            color: #2d5016;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.9);
            padding: 2px 6px;
            border-radius: 8px;
            border: 1px solid rgba(45, 80, 22, 0.2);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
            letter-spacing: 0.3px;
            margin-top: 4px;
        }
        .calendar-day-price.not-available {
            position: relative;
            opacity: 0.6;
        }
        .calendar-day-price.not-available::before {
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
        .calendar-day-price.not-available .price-display {
            opacity: 0.5;
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
                            <a class="nav-link active" href="tarifas.php">
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

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-dollar-sign me-2"></i>
                        Gestión de Tarifas por Fecha
                    </h1>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Calendario de Precios
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <label class="form-label mb-0">Navegar Mes:</label>
                                        <button type="button" class="btn btn-success" onclick="openUploadExcelModal()">
                                            <i class="fas fa-file-excel me-1"></i> Cargar Tarifas desde Excel
                                        </button>
                                    </div>
                                    <div class="input-group">
                                        <input type="month" id="monthSelector" class="form-control" value="<?php echo date('Y-m'); ?>">
                                        <button type="button" class="btn btn-primary" onclick="loadCalendar()" id="loadCalendarBtn">
                                            <i class="fas fa-search me-1"></i> Cargar
                                        </button>
                                    </div>
                                </div>
                                
                                <div id="priceCalendar" class="calendar-container" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px;"></div>
                                
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Haz clic en cualquier día para modificar su precio. El precio base es $200,000 COP.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para editar precio -->
    <div class="modal fade" id="editPriceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        Editar Precio
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body price-input-modal">
                    <div class="mb-3">
                        <label class="form-label">Fecha:</label>
                        <input type="text" id="editPriceDate" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio (COP):</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" id="editPriceValue" class="form-control" min="0" step="1000" required>
                            <span class="input-group-text">COP</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="savePrice()">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentEditingDate = null;
        let currentPrices = {};
        const basePrice = 200000;

        // Cargar calendario al iniciar
        $(document).ready(function() {
            console.log('Documento listo, cargando calendario...');
            
            // Verificar que jQuery esté cargado
            if (typeof $ === 'undefined') {
                console.error('jQuery no está cargado');
                alert('Error: jQuery no está disponible');
                return;
            }
            
            // Verificar que el selector existe
            if ($('#monthSelector').length === 0) {
                console.error('No se encontró el selector de mes');
                return;
            }
            
            // Agregar event listener al botón también
            $('#loadCalendarBtn').on('click', function(e) {
                e.preventDefault();
                console.log('Botón Cargar clickeado');
                loadCalendar();
            });
            
            // También cuando cambie el mes
            $('#monthSelector').on('change', function() {
                console.log('Mes cambiado:', $(this).val());
            });
            
            loadCalendar();
        });

        function loadCalendar() {
            const monthSelector = document.getElementById('monthSelector');
            
            if (!monthSelector) {
                alert('Error: No se encontró el selector de mes');
                return;
            }
            
            const month = monthSelector.value;
            
            if (!month) {
                alert('Por favor seleccione un mes');
                return;
            }
            
            const [year, monthNum] = month.split('-');
            
            if (!year || !monthNum) {
                alert('Formato de fecha inválido');
                return;
            }
            
            // Mostrar indicador de carga
            const calendar = $('#priceCalendar');
            calendar.html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Cargando...</div>');
            
            // Obtener tarifas del mes
            $.ajax({
                url: '../../../app/api/admin/get_tarifas_month.php',
                method: 'GET',
                data: {
                    year: year,
                    month: monthNum,
                    apartamento_id: 1
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        currentPrices = response.tarifas || {};
                        renderCalendar(year, parseInt(monthNum), response.tarifas || {});
                    } else {
                        alert('Error al cargar tarifas: ' + (response.message || 'Error desconocido'));
                        calendar.html('<div class="text-center p-4 text-danger">Error al cargar las tarifas</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX:', status, error);
                    console.error('Response:', xhr.responseText);
                    alert('Error al conectar con el servidor: ' + error);
                    calendar.html('<div class="text-center p-4 text-danger">Error de conexión</div>');
                }
            });
        }

        // Función para formatear precio en formato colombiano
        function formatearPrecioCOP(precio) {
            // Formato: $200.000 COP
            return '$' + precio.toLocaleString('es-CO') + ' COP';
        }
        
        function renderCalendar(year, month, prices) {
            const calendar = $('#priceCalendar');
            calendar.empty();
            
            // Crear header de días de la semana
            const dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
            dayNames.forEach(day => {
                calendar.append(`<div class="calendar-header text-center fw-bold">${day}</div>`);
            });
            
            // Obtener primer día del mes
            const firstDay = new Date(year, month - 1, 1);
            const lastDay = new Date(year, month, 0);
            const daysInMonth = lastDay.getDate();
            const startingDayOfWeek = firstDay.getDay();
            
            // Días vacíos al inicio
            for (let i = 0; i < startingDayOfWeek; i++) {
                calendar.append('<div class="calendar-day-empty"></div>');
            }
            
            // Días del mes
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            // Quitar restricción global de indisponibilidad
            
            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const price = prices[dateStr] || basePrice;
                const isToday = dateStr === new Date().toISOString().split('T')[0];
                const dateObj = new Date(dateStr);
                dateObj.setHours(0, 0, 0, 0);
                
                // Sin bloqueo global: las restricciones se gestionan por reservas/bloqueos
                
                const dayElement = $(`
                    <div class="calendar-day-price p-2 border rounded ${isToday ? 'bg-info text-white' : ''}" 
                         data-date="${dateStr}" 
                         onclick="editPrice('${dateStr}', ${price})"
                         title="Click para editar precio">
                        <div class="fw-bold">${day}</div>
                        <div class="price-display">${formatearPrecioCOP(price)}</div>
                    </div>
                `);
                
                calendar.append(dayElement);
            }
        }

        function editPrice(date, currentPrice) {
            currentEditingDate = date;
            const dateObj = new Date(date);
            const dateFormatted = dateObj.toLocaleDateString('es-CO', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            $('#editPriceDate').val(dateFormatted);
            $('#editPriceValue').val(currentPrice);
            
            const modal = new bootstrap.Modal(document.getElementById('editPriceModal'));
            modal.show();
        }

        function savePrice() {
            const price = parseFloat($('#editPriceValue').val());
            
            if (!price || price < 0) {
                alert('Por favor ingrese un precio válido');
                return;
            }
            
            if (!currentEditingDate) {
                alert('Error: No hay fecha seleccionada');
                return;
            }
            
            $.ajax({
                url: '../../../app/api/admin/update_tarifa.php',
                method: 'POST',
                data: {
                    fecha: currentEditingDate,
                    precio: price,
                    apartamento_id: 1
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Actualizar precio en el calendario
                        currentPrices[currentEditingDate] = price;
                        
                        // Recargar calendario
                        loadCalendar();
                        
                        // Cerrar modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editPriceModal'));
                        modal.hide();
                        
                        // Mostrar mensaje de éxito
                        alert('Precio actualizado exitosamente');
                    } else {
                        alert('Error al actualizar precio: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error al conectar con el servidor');
                }
            });
        }
        
        // Mostrar modal de gestión de clientes
        function showClientManagement() {
            console.log('Ejecutando showClientManagement');
            $('#clientManagementModal').modal('show');
            loadAllClients();
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
            alert('Funcionalidad de detalles del cliente en desarrollo');
        }
        
        // Abrir modal para subir Excel
        function openUploadExcelModal() {
            // Resetear formulario y resultados
            document.getElementById('uploadExcelForm').reset();
            document.getElementById('uploadResult').innerHTML = '';
            document.getElementById('uploadProgress').classList.add('d-none');
            
            const modal = new bootstrap.Modal(document.getElementById('uploadExcelModal'));
            modal.show();
        }
        
        // Subir y procesar archivo Excel
        function uploadExcelFile() {
            const fileInput = document.getElementById('excel_file');
            const form = document.getElementById('uploadExcelForm');
            const progressDiv = document.getElementById('uploadProgress');
            const resultDiv = document.getElementById('uploadResult');
            
            // Validar que se haya seleccionado un archivo
            if (!fileInput.files || !fileInput.files[0]) {
                resultDiv.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Por favor seleccione un archivo Excel.</div>';
                return;
            }
            
            // Validar tamaño del archivo (10MB)
            const maxSize = 10 * 1024 * 1024; // 10MB en bytes
            if (fileInput.files[0].size > maxSize) {
                resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>El archivo es demasiado grande. Tamaño máximo: 10MB.</div>';
                return;
            }
            
            // Mostrar progreso
            progressDiv.classList.remove('d-none');
            resultDiv.innerHTML = '';
            
            // Crear FormData
            const formData = new FormData(form);
            
            // Enviar archivo via AJAX
            $.ajax({
                url: '../../../app/api/admin/upload_tarifas_excel.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    progressDiv.classList.add('d-none');
                    
                    if (response.success) {
                        let message = '<div class="alert alert-success"><h6><i class="fas fa-check-circle me-2"></i>¡Proceso completado exitosamente!</h6>';
                        message += '<hr><strong>Estadísticas:</strong><ul class="mb-0">';
                        message += `<li>Total procesadas: ${response.stats.procesadas}</li>`;
                        message += `<li>Nuevas tarifas creadas: ${response.stats.creadas}</li>`;
                        message += `<li>Tarifas actualizadas: ${response.stats.actualizadas}</li>`;
                        message += `<li>Errores: ${response.stats.errores}</li>`;
                        message += '</ul></div>';
                        
                        if (response.errores_detalle && response.errores_detalle.length > 0) {
                            message += '<div class="alert alert-warning mt-2"><h6><i class="fas fa-exclamation-triangle me-2"></i>Errores encontrados:</h6><ul class="mb-0"><li>' + response.errores_detalle.join('</li><li>') + '</li></ul></div>';
                        }
                        
                        resultDiv.innerHTML = message;
                        
                        // Recargar calendario después de 2 segundos si hubo cambios
                        if (response.stats.procesadas > 0) {
                            setTimeout(function() {
                                loadCalendar();
                            }, 2000);
                        }
                        
                        // Cerrar modal después de 5 segundos si todo fue exitoso
                        if (response.stats.errores === 0) {
                            setTimeout(function() {
                                const modal = bootstrap.Modal.getInstance(document.getElementById('uploadExcelModal'));
                                if (modal) {
                                    modal.hide();
                                }
                            }, 5000);
                        }
                    } else {
                        resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i><strong>Error:</strong> ' + (response.message || 'Error desconocido') + '</div>';
                    }
                },
                error: function(xhr, status, error) {
                    progressDiv.classList.add('d-none');
                    console.error('Error AJAX:', status, error);
                    console.error('Response:', xhr.responseText);
                    
                    let errorMessage = 'Error al conectar con el servidor: ' + error;
                    
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        // Si no se puede parsear el JSON, usar el mensaje por defecto
                    }
                    
                    resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i><strong>Error:</strong> ' + errorMessage + '</div>';
                }
            });
        }
    </script>
    
    <!-- Modal para subir Excel -->
    <div class="modal fade" id="uploadExcelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-file-excel me-2"></i>
                        Cargar Tarifas desde Excel
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Instrucciones:</strong>
                        <ul class="mb-0 mt-2">
                            <li>El archivo Excel debe contener las columnas: <strong>AÑO</strong>, <strong>MES</strong>, <strong>DIA</strong>, y <strong>T credito 7%</strong> (o similar)</li>
                            <li>El mes debe estar en español (ENERO, FEBRERO, etc.)</li>
                            <li>Las tarifas existentes se actualizarán, las nuevas se crearán</li>
                            <li>Formato aceptado: .xls, .xlsx, .csv</li>
                        </ul>
                    </div>
                    <form id="uploadExcelForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="excel_file" class="form-label">Seleccionar archivo Excel:</label>
                            <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xls,.xlsx,.csv" required>
                            <small class="form-text text-muted">Tamaño máximo: 10MB</small>
                        </div>
                        <input type="hidden" name="apartamento_id" value="1">
                    </form>
                    <div id="uploadProgress" class="d-none mt-3">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%">
                                Procesando archivo...
                            </div>
                        </div>
                    </div>
                    <div id="uploadResult" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="uploadExcelFile()">
                        <i class="fas fa-upload me-1"></i> Subir y Procesar
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
</body>
</html>


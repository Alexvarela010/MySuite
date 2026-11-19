<?php
/**
 * Panel de Administración - Próximas Reservas
 * My Suite In Cartagena
 */

require_once __DIR__ . '/../../../includes/auth_helper.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/proximas_reservas_helper.php';

// Asegurar que la tabla y el módulo existan antes de validar permisos
$dbInit = (new Database())->getConnection();
if ($dbInit) {
    ProximasReservasHelper::ensureTablesExist($dbInit);
}

AuthHelper::requireModule('proximas_reservas');

$isAdmin = AuthHelper::isAdmin();
$anioActual = (int)date('Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Próximas Reservas - My Suite In Cartagena</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oxygen:wght@300;400;700&family=Crimson+Text:wght@400;600;700&family=Abril+Fatface&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            font-family: 'Oxygen', sans-serif;
        }
        h1, h2 {
            font-family: 'Crimson Text', serif;
            color: #333;
            line-height: 1.3;
        }
        h3, h4, h5, h6 {
            font-family: 'Oxygen', sans-serif;
            color: #1e293b;
            font-weight: 700;
            line-height: 1.4;
        }
        body {
            background-color: #f8fafc;
            font-size: .875rem;
        }
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
        .main-content {
            margin-top: 48px;
            padding: 1.5rem;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
        }
        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 700;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem 0.75rem 0 0 !important;
        }
        .stat-card {
            border-left: 4px solid #0d6efd;
            transition: transform 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .stat-reservas { border-left-color: #0d6efd; }
        .stat-total    { border-left-color: #10b981; }
        .stat-deposito { border-left-color: #6366f1; }
        .stat-balance  { border-left-color: #f59e0b; }

        .stat-label {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            font-weight: 700;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
        }
        .table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.03em;
            border-bottom: 2px solid #cbd5e1;
            vertical-align: middle;
        }
        .section-badge-proximas {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .section-badge-futuras {
            background-color: #ede9fe;
            color: #5b21b6;
        }

        /* Zoom 75% consistente con el panel */
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
            display: none !important;
            opacity: 0 !important;
            background-color: transparent !important;
        }
        .modal-backdrop.show {
            display: none !important;
            opacity: 0 !important;
            background-color: transparent !important;
        }
        body.modal-open .modal-backdrop {
            display: none !important;
            opacity: 0 !important;
            background-color: transparent !important;
        }
        .modal.fade.show {
            background-color: rgba(15, 23, 42, 0.5) !important;
            backdrop-filter: blur(2px);
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
        <input class="form-control form-control-dark w-100" type="text" id="navbarSearch" placeholder="Buscar..." aria-label="Search">
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3" href="/app/controllers/auth/logout.php">Sign out</a>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <?php AuthHelper::renderSidebar('proximas_reservas'); ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">

                <!-- Encabezado -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-3 border-bottom">
                    <div>
                        <h1 class="h2 mb-1"><i class="fas fa-calendar-check text-primary me-2"></i> Próximas Reservas</h1>
                        <p class="text-muted mb-0">Control rápido de fechas, noches, depósitos y balance por recibir</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" id="btnSyncWeb" onclick="sincronizarReservasWeb()" title="Traer automáticamente reservas aprobadas/abonadas de la página web">
                            <i class="fas fa-sync-alt me-1"></i> Sincronizar Reservas Web
                        </button>
                        <button class="btn btn-primary" onclick="abrirModalProxima()">
                            <i class="fas fa-plus me-1"></i> Nueva Próxima Reserva
                        </button>
                    </div>
                </div>

                <!-- Tarjetas de Resumen -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card stat-reservas p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Reservas / Noches</div>
                                    <div class="stat-value text-primary" id="statReservasNoches">0 / 0</div>
                                </div>
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                                    <i class="fas fa-bed fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card stat-total p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Valor Total</div>
                                    <div class="stat-value text-success" id="statTotal">$ 0</div>
                                </div>
                                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                                    <i class="fas fa-dollar-sign fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card stat-deposito p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Depósitos Recibidos</div>
                                    <div class="stat-value" style="color: #6366f1;" id="statDeposito">$ 0</div>
                                </div>
                                <div class="rounded-circle p-3 text-white" style="background-color: #6366f1;">
                                    <i class="fas fa-hand-holding-usd fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card stat-balance p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Balance x Recibir</div>
                                    <div class="stat-value text-warning" id="statBalance">$ 0</div>
                                </div>
                                <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Barra de Filtros -->
                <div class="card p-3 mb-4">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1 fw-bold">Mes</label>
                            <select class="form-select form-select-sm" id="filtroMes">
                                <option value="">Todos los meses</option>
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1 fw-bold">Año</label>
                            <select class="form-select form-select-sm" id="filtroAnio">
                                <option value="">Todos</option>
                                <?php for ($y = $anioActual + 2; $y >= 2024; $y--): ?>
                                    <option value="<?= $y ?>"><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1 fw-bold">Estado</label>
                            <select class="form-select form-select-sm" id="filtroEstado">
                                <option value="pendiente" selected>Pendientes</option>
                                <option value="completada">Completadas</option>
                                <option value="cancelada">Canceladas</option>
                                <option value="">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1 fw-bold">Buscar</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="filtroSearch" placeholder="Nombre, vendedor, fecha...">
                                <button class="btn btn-outline-secondary" type="button" id="btnLimpiarFiltros" title="Limpiar filtros">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenedor de Tablas (Próximas Reservas & Próximas Próximas Reservas) -->
                <div id="contenedorTablas">
                    <div class="card">
                        <div class="card-body text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                            Cargando próximas reservas...
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Modal Crear / Editar Próxima Reserva -->
    <div class="modal fade" id="modalProxima" tabindex="-1" aria-labelledby="modalProximaTitulo" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formProxima">
                    <input type="hidden" id="proxId" name="id_proxima" value="">

                    <div class="modal-header">
                        <h5 class="modal-title" id="modalProximaTitulo">
                            <i class="fas fa-calendar-plus me-1 text-primary"></i> Nueva Próxima Reserva
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Fecha Llegada <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="proxFechaInicio" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Fecha Salida <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="proxFechaFin" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">No. Noches <span class="text-danger">*</span></label>
                                <input type="number" min="1" class="form-control fw-bold" id="proxNoches" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Texto Fechas (Opcional, se genera solo)</label>
                                <input type="text" class="form-control" id="proxFechasTexto" placeholder="Ej: 7 al 13 de Noviembre">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Nombre / Vendedor <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="proxNombreVendedor" required placeholder="Ej: Erina Gomez (Directo) - 1 pax">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold small">$ x Noche (COP)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control" id="proxPrecioNoche" placeholder="150.000">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Depósito (COP)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control" id="proxDeposito" placeholder="0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Total (COP)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control fw-bold text-success" id="proxTotal" placeholder="900.000">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Balance x Recibir</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control fw-bold text-warning bg-light" id="proxBalance" readonly>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold small">Estado</label>
                                <select class="form-select" id="proxEstado">
                                    <option value="pendiente">Pendiente</option>
                                    <option value="completada">Completada</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold small">Notas adicionales (Opcional)</label>
                                <textarea class="form-control" id="proxNotas" rows="2" placeholder="Observaciones sobre llegada, pago, huéspedes..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4" id="btnGuardarProxima">
                            <i class="fas fa-save me-1"></i> Guardar Reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const formatoMoneda = new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            maximumFractionDigits: 0
        });

        const mesesES = [
            '', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];

        let totalManualOverride = false;

        document.addEventListener('DOMContentLoaded', function() {
            cargarReservas();

            $('#filtroMes, #filtroAnio, #filtroEstado').on('change', cargarReservas);

            let searchTimeout = null;
            $('#filtroSearch').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(cargarReservas, 300);
            });

            $('#btnLimpiarFiltros').on('click', function() {
                $('#filtroMes').val('');
                $('#filtroAnio').val('');
                $('#filtroEstado').val('pendiente');
                $('#filtroSearch').val('');
                cargarReservas();
            });

            // Auto-cálculo de noches y texto de fechas
            $('#proxFechaInicio, #proxFechaFin').on('change', function() {
                const ini = $('#proxFechaInicio').val();
                const fin = $('#proxFechaFin').val();
                if (ini && fin) {
                    const d1 = new Date(ini + 'T00:00:00');
                    const d2 = new Date(fin + 'T00:00:00');
                    const diff = Math.round((d2 - d1) / (1000 * 60 * 60 * 24));
                    if (diff > 0) {
                        $('#proxNoches').val(diff);
                        $('#proxFechasTexto').val(generarTextoFechas(ini, fin));
                        recalcularMontos();
                    }
                }
            });

            // Formateo numérico y recálculo
            $('#proxPrecioNoche, #proxDeposito').on('input', function() {
                formatearInputMoneda(this);
                totalManualOverride = false;
                recalcularMontos();
            });

            $('#proxTotal').on('input', function() {
                formatearInputMoneda(this);
                totalManualOverride = true;
                recalcularBalance();
            });

            $('#proxNoches').on('input', function() {
                totalManualOverride = false;
                recalcularMontos();
            });

            $('#formProxima').on('submit', function(e) {
                e.preventDefault();
                guardarReserva();
            });
        });

        function formatearInputMoneda(el) {
            let val = el.value.replace(/\D/g, '');
            el.value = val ? new Intl.NumberFormat('es-CO').format(parseInt(val, 10)) : '';
        }

        function parseNumero(val) {
            if (!val) return null;
            const clean = String(val).replace(/\./g, '').replace(/[^\d]/g, '');
            return clean ? parseInt(clean, 10) : null;
        }

        function recalcularMontos() {
            const noches = parseInt($('#proxNoches').val(), 10) || 0;
            const precio = parseNumero($('#proxPrecioNoche').val());

            if (!totalManualOverride) {
                if (precio !== null && noches > 0) {
                    const total = precio * noches;
                    $('#proxTotal').val(new Intl.NumberFormat('es-CO').format(total));
                } else {
                    $('#proxTotal').val('');
                }
            }
            recalcularBalance();
        }

        function recalcularBalance() {
            const total = parseNumero($('#proxTotal').val());
            const deposito = parseNumero($('#proxDeposito').val()) || 0;
            if (total !== null) {
                const bal = Math.max(0, total - deposito);
                $('#proxBalance').val(new Intl.NumberFormat('es-CO').format(bal));
            } else {
                $('#proxBalance').val('');
            }
        }

        function generarTextoFechas(ini, fin) {
            const p1 = ini.split('-');
            const p2 = fin.split('-');
            if (p1.length !== 3 || p2.length !== 3) return '';
            const d1 = parseInt(p1[2], 10), m1 = parseInt(p1[1], 10), y1 = p1[0];
            const d2 = parseInt(p2[2], 10), m2 = parseInt(p2[1], 10), y2 = p2[0];
            if (y1 === y2 && m1 === m2) {
                return `${d1} al ${d2} de ${mesesES[m1]}`;
            } else if (y1 === y2) {
                return `${d1} de ${mesesES[m1]} al ${d2} de ${mesesES[m2]}`;
            }
            return `${d1} de ${mesesES[m1]} ${y1} al ${d2} de ${mesesES[m2]} ${y2}`;
        }

        function cargarReservas() {
            const params = new URLSearchParams();
            const mes = $('#filtroMes').val();
            const anio = $('#filtroAnio').val();
            const estado = $('#filtroEstado').val();
            const search = $('#filtroSearch').val();

            if (mes) params.append('mes', mes);
            if (anio) params.append('anio', anio);
            if (estado) params.append('estado', estado);
            if (search) params.append('search', search);

            fetch(`/app/api/admin/proximas_reservas_get.php?${params.toString()}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    renderResumen(data.resumen);
                    renderSecciones(data.reservas);
                } else {
                    $('#contenedorTablas').html(`<div class="alert alert-danger">${escapeHtml(data.message || 'Error al cargar datos')}</div>`);
                }
            })
            .catch(err => {
                console.error(err);
                $('#contenedorTablas').html(`<div class="alert alert-danger">Error de conexión con el servidor.</div>`);
            });
        }

        function renderResumen(res) {
            if (!res) return;
            $('#statReservasNoches').text(`${res.total_reservas} / ${res.total_noches} noches`);
            $('#statTotal').text(formatoMoneda.format(res.suma_total || 0));
            $('#statDeposito').text(formatoMoneda.format(res.suma_deposito || 0));
            $('#statBalance').text(formatoMoneda.format(res.suma_balance || 0));
        }

        function renderSecciones(reservas) {
            const cont = $('#contenedorTablas');
            cont.empty();

            if (!reservas || reservas.length === 0) {
                cont.html(`
                    <div class="card">
                        <div class="card-body text-center py-5 text-muted">
                            <i class="fas fa-calendar-times fa-3x mb-3 opacity-50 d-block"></i>
                            <strong>No hay próximas reservas registradas</strong>
                            <p class="small mb-0">Haz clic en "+ Nueva Próxima Reserva" para agregar una.</p>
                        </div>
                    </div>
                `);
                return;
            }

            cont.append(construirCardTabla('Próximas Reservas', reservas));
        }

        function construirCardTabla(titulo, lista) {
            let rowsHtml = '';

            lista.forEach(r => {
                const precioStr = r.precio_noche !== null ? formatoMoneda.format(r.precio_noche) : '<span class="text-muted">-</span>';
                const depStr = (r.deposito !== null && parseFloat(r.deposito) > 0) ? formatoMoneda.format(r.deposito) : '<span class="text-muted">-</span>';
                const totalStr = r.total !== null ? `<strong class="text-dark">${formatoMoneda.format(r.total)}</strong>` : '<span class="text-muted">-</span>';

                let balanceStr = '<span class="text-muted">-</span>';
                if (r.balance_recibir !== null) {
                    const balNum = parseFloat(r.balance_recibir);
                    if (balNum === 0) {
                        balanceStr = `<span class="badge bg-success bg-opacity-10 text-success border border-success">$ - (Pagado)</span>`;
                    } else {
                        balanceStr = `<strong class="text-warning">${formatoMoneda.format(balNum)}</strong>`;
                    }
                }

                let estadoBadge = '';
                if (r.estado === 'completada') {
                    estadoBadge = '<span class="badge bg-success ms-1">Completada</span>';
                } else if (r.estado === 'cancelada') {
                    estadoBadge = '<span class="badge bg-secondary ms-1">Cancelada</span>';
                }

                rowsHtml += `
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">${escapeHtml(r.fechas_texto || (r.fecha_inicio + ' al ' + r.fecha_fin))}</div>
                            <small class="text-muted">${r.fecha_inicio} &rarr; ${r.fecha_fin}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border fs-6">${r.numero_noches}</span>
                        </td>
                        <td class="text-end">${precioStr}</td>
                        <td class="text-end text-primary">${depStr}</td>
                        <td class="text-end">${totalStr}</td>
                        <td class="text-end">${balanceStr}</td>
                        <td>
                            <div class="fw-bold text-dark">
                                ${escapeHtml(r.nombre_vendedor)} ${estadoBadge}
                                ${r.id_reserva ? `<span class="badge bg-info bg-opacity-10 text-info border border-info ms-1" title="Sincronizada desde Reserva Web #${r.id_reserva}">Web #${r.id_reserva}</span>` : ''}
                            </div>
                            ${r.notas ? `<small class="text-muted d-block">${escapeHtml(r.notas)}</small>` : ''}
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick='editarProxima(${JSON.stringify(r)})' title="Editar">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="eliminarProxima(${r.id_proxima}, '${escapeHtml(r.nombre_vendedor)}')" title="Eliminar">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            return `
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge section-badge-proximas me-2 px-2 py-1">${lista.length}</span>
                            <span>${titulo}</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 220px;">Fechas</th>
                                    <th class="text-center" style="width: 95px;">No. Noches</th>
                                    <th class="text-end" style="width: 130px;">$ x Noche</th>
                                    <th class="text-end" style="width: 130px;">Depósito</th>
                                    <th class="text-end" style="width: 135px;">Total</th>
                                    <th class="text-end" style="width: 150px;">Balance x Recibir</th>
                                    <th>Nombre / Vendedor</th>
                                    <th class="text-center" style="width: 95px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>${rowsHtml}</tbody>
                        </table>
                    </div>
                </div>
            `;
        }

        function abrirModalProxima() {
            $('#formProxima')[0].reset();
            $('#proxId').val('');
            $('#proxEstado').val('pendiente');
            totalManualOverride = false;
            $('#modalProximaTitulo').html('<i class="fas fa-calendar-plus me-1 text-primary"></i> Nueva Próxima Reserva');
            $('#modalProxima').modal('show');
        }

        function editarProxima(r) {
            $('#proxId').val(r.id_proxima);
            $('#proxFechaInicio').val(r.fecha_inicio);
            $('#proxFechaFin').val(r.fecha_fin);
            $('#proxNoches').val(r.numero_noches);
            $('#proxFechasTexto').val(r.fechas_texto || '');
            $('#proxNombreVendedor').val(r.nombre_vendedor || '');
            $('#proxPrecioNoche').val(r.precio_noche !== null ? new Intl.NumberFormat('es-CO').format(parseInt(r.precio_noche, 10)) : '');
            $('#proxDeposito').val(r.deposito !== null && parseFloat(r.deposito) > 0 ? new Intl.NumberFormat('es-CO').format(parseInt(r.deposito, 10)) : '');
            $('#proxTotal').val(r.total !== null ? new Intl.NumberFormat('es-CO').format(parseInt(r.total, 10)) : '');
            $('#proxEstado').val(r.estado || 'pendiente');
            $('#proxNotas').val(r.notas || '');

            totalManualOverride = (r.total !== null);
            recalcularBalance();

            $('#modalProximaTitulo').html(`<i class="fas fa-edit me-1 text-primary"></i> Editar Próxima Reserva`);
            $('#modalProxima').modal('show');
        }

        function guardarReserva() {
            const btn = $('#btnGuardarProxima');
            const orig = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');

            const payload = {
                id_proxima: $('#proxId').val(),
                fecha_inicio: $('#proxFechaInicio').val(),
                fecha_fin: $('#proxFechaFin').val(),
                numero_noches: $('#proxNoches').val(),
                fechas_texto: $('#proxFechasTexto').val(),
                nombre_vendedor: $('#proxNombreVendedor').val(),
                precio_noche: $('#proxPrecioNoche').val(),
                deposito: $('#proxDeposito').val(),
                total: $('#proxTotal').val(),
                grupo: 'proximas',
                estado: $('#proxEstado').val(),
                notas: $('#proxNotas').val()
            };

            fetch('/app/api/admin/proximas_reservas_save.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    $('#modalProxima').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: '¡Guardado!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    cargarReservas();
                } else {
                    Swal.fire('Error', data.message || 'No se pudo guardar', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Error de conexión con el servidor', 'error');
            })
            .finally(() => {
                btn.prop('disabled', false).html(orig);
            });
        }

        function eliminarProxima(id, nombre) {
            Swal.fire({
                title: '¿Eliminar registro?',
                html: `¿Deseas eliminar la reserva de <strong>${nombre}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('/app/api/admin/proximas_reservas_delete.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ id_proxima: id })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: data.message,
                                timer: 1400,
                                showConfirmButton: false
                            });
                            cargarReservas();
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo eliminar', 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Error', 'Error de conexión con el servidor', 'error');
                    });
                }
            });
        }

        function sincronizarReservasWeb() {
            const btn = $('#btnSyncWeb');
            const orig = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Sincronizando...');

            fetch('/app/api/admin/proximas_reservas_sync.php', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sincronización Completada',
                        text: data.message,
                        timer: 2200,
                        showConfirmButton: false
                    });
                    cargarReservas();
                } else {
                    Swal.fire('Error', data.message || 'No se pudo sincronizar', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Error de conexión con el servidor', 'error');
            })
            .finally(() => {
                btn.prop('disabled', false).html(orig);
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</body>
</html>

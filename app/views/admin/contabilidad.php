<?php
/**
 * Panel de Administración - Módulo de Contabilidad (Entradas y Salidas)
 * My Suite In Cartagena
 */

require_once __DIR__ . '/../../../includes/auth_helper.php';
AuthHelper::requireModule('contabilidad');

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/contabilidad_helper.php';

$isAdmin = AuthHelper::isAdmin();
$mesActual = (int)date('n');
$anioActual = (int)date('Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contabilidad - My Suite In Cartagena</title>
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
            background: #f8fafc;
            min-height: 100vh;
            margin-top: 48px;
            padding: 25px;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            background: #fff;
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #edf2f7;
            padding: 14px 20px;
            font-weight: 600;
        }

        /* Stat Cards */
        .stat-card {
            border-left: 4px solid #cbd5e1;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.06);
        }
        .stat-card.stat-entrada {
            border-left-color: #10b981;
        }
        .stat-card.stat-salida {
            border-left-color: #ef4444;
        }
        .stat-card.stat-balance {
            border-left-color: #3b82f6;
        }
        .stat-card.stat-caja-evelyn {
            border-left-color: #6366f1;
        }
        .stat-card.stat-caja-carlos {
            border-left-color: #0d9488;
        }
        .badge-caja-evelyn {
            background-color: #ede9fe;
            color: #5b21b6;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
        }
        .badge-caja-carlos {
            background-color: #ccfbf1;
            color: #0f766e;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
        }
        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            font-family: 'Oxygen', sans-serif;
        }
        .stat-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            font-weight: 600;
        }

        /* Badges de tipo */
        .badge-entrada {
            background-color: #d1fae5;
            color: #065f46;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .badge-salida {
            background-color: #fee2e2;
            color: #991b1b;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 20px;
        }

        /* Montos */
        .monto-entrada {
            color: #10b981;
            font-weight: 700;
        }
        .monto-salida {
            color: #ef4444;
            font-weight: 700;
        }

        /* Tabla */
        .table > :not(caption) > * > * {
            padding: 12px 14px;
            vertical-align: middle;
        }
        .table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: 700;
            border-bottom: 2px solid #e2e8f0;
        }

        /* Toggle tipo en modal */
        .btn-check:checked + .btn-outline-success {
            background-color: #10b981;
            color: white;
            border-color: #10b981;
        }
        .btn-check:checked + .btn-outline-danger {
            background-color: #ef4444;
            color: white;
            border-color: #ef4444;
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
        
        .container-fluid {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Solución para el bug del zoom 75%: ocultar backdrop recortado y aplicar fondo uniforme a .modal */
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
            <!-- Sidebar Dinámico RBAC -->
            <?php AuthHelper::renderSidebar('contabilidad'); ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                
                <!-- Encabezado y Botones de Acción -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-3 border-bottom">
                    <div>
                        <h1 class="h2 mb-1"><i class="fas fa-file-invoice-dollar text-primary me-2"></i> Módulo de Contabilidad</h1>
                        <p class="text-muted mb-0">Gestión de flujo de caja: registro de entradas, salidas<?= $isAdmin ? ' y exportación a Excel' : '' ?></p>
                    </div>
                    <div class="d-flex gap-2">
                        <?php if ($isAdmin): ?>
                        <button class="btn btn-outline-success" id="btnExportarExcel" title="Exportar movimientos a Excel con los filtros actuales">
                            <i class="fas fa-file-excel me-1 text-success"></i> Exportar a Excel
                        </button>
                        <?php endif; ?>
                        <button class="btn btn-danger" onclick="abrirModalMovimiento('salida')">
                            <i class="fas fa-arrow-up me-1"></i> + Nueva Salida
                        </button>
                        <button class="btn btn-success" onclick="abrirModalMovimiento('entrada')">
                            <i class="fas fa-arrow-down me-1"></i> + Nueva Entrada
                        </button>
                    </div>
                </div>

                <?php if ($isAdmin): ?>
                <!-- 5 Tarjetas de Resumen para Administrador -->
                <div class="row g-3 mb-4">
                    <div class="col-xl col-md-4 col-sm-6">
                        <div class="card stat-card stat-entrada p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Total Entradas</div>
                                    <div class="stat-value text-success" id="statEntradas">$ 0</div>
                                </div>
                                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                                    <i class="fas fa-arrow-down-long fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-md-4 col-sm-6">
                        <div class="card stat-card stat-salida p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Total Salidas</div>
                                    <div class="stat-value text-danger" id="statSalidas">$ 0</div>
                                </div>
                                <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger">
                                    <i class="fas fa-arrow-up-long fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-md-4 col-sm-6">
                        <div class="card stat-card stat-balance p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Balance Neto</div>
                                    <div class="stat-value" id="statBalance">$ 0</div>
                                </div>
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                                    <i class="fas fa-scale-balanced fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-md-6 col-sm-6">
                        <div class="card stat-card stat-caja-evelyn p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Saldo Caja Evelyn</div>
                                    <div class="stat-value" id="statSaldoEvelyn" style="color: #6366f1;">$ 0</div>
                                </div>
                                <div class="rounded-circle p-3 text-white" style="background-color: #6366f1;">
                                    <i class="fas fa-cash-register fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-md-6 col-sm-6">
                        <div class="card stat-card stat-caja-carlos p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Saldo Caja Carlos</div>
                                    <div class="stat-value" id="statSaldoCarlos" style="color: #0d9488;">$ 0</div>
                                </div>
                                <div class="rounded-circle p-3 text-white" style="background-color: #0d9488;">
                                    <i class="fas fa-vault fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Barra de Filtros (Card) -->
                <div class="card p-3 mb-4">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1 fw-bold">Mes</label>
                            <select class="form-select form-select-sm" id="filtroMes">
                                <option value="">Todos los meses</option>
                                <option value="1" <?= $mesActual === 1 ? 'selected' : '' ?>>Enero</option>
                                <option value="2" <?= $mesActual === 2 ? 'selected' : '' ?>>Febrero</option>
                                <option value="3" <?= $mesActual === 3 ? 'selected' : '' ?>>Marzo</option>
                                <option value="4" <?= $mesActual === 4 ? 'selected' : '' ?>>Abril</option>
                                <option value="5" <?= $mesActual === 5 ? 'selected' : '' ?>>Mayo</option>
                                <option value="6" <?= $mesActual === 6 ? 'selected' : '' ?>>Junio</option>
                                <option value="7" <?= $mesActual === 7 ? 'selected' : '' ?>>Julio</option>
                                <option value="8" <?= $mesActual === 8 ? 'selected' : '' ?>>Agosto</option>
                                <option value="9" <?= $mesActual === 9 ? 'selected' : '' ?>>Septiembre</option>
                                <option value="10" <?= $mesActual === 10 ? 'selected' : '' ?>>Octubre</option>
                                <option value="11" <?= $mesActual === 11 ? 'selected' : '' ?>>Noviembre</option>
                                <option value="12" <?= $mesActual === 12 ? 'selected' : '' ?>>Diciembre</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1 fw-bold">Año</label>
                            <select class="form-select form-select-sm" id="filtroAnio">
                                <option value="">Todos</option>
                                <?php for ($y = $anioActual + 1; $y >= 2024; $y--): ?>
                                    <option value="<?= $y ?>" <?= $y === $anioActual ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1 fw-bold">Tipo</label>
                            <select class="form-select form-select-sm" id="filtroTipo">
                                <option value="">Todos los tipos</option>
                                <option value="entrada">Solo Entradas (+)</option>
                                <option value="salida">Solo Salidas (-)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1 fw-bold">Caja</label>
                            <select class="form-select form-select-sm" id="filtroCaja">
                                <option value="">Todas las cajas</option>
                                <option value="Evelyn">Caja Evelyn</option>
                                <option value="Carlos">Caja Carlos</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1 fw-bold">Categoría</label>
                            <select class="form-select form-select-sm" id="filtroCategoria">
                                <option value="">Todas las categorías</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1 fw-bold">Buscar</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="filtroSearch" placeholder="Concepto, recibo...">
                                <button class="btn btn-outline-secondary" type="button" id="btnLimpiarFiltros" title="Limpiar filtros">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla Principal de Movimientos (Solo Admin) -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-list me-1 text-primary"></i> 
                            <strong>Registro de Movimientos</strong> 
                            <span class="badge bg-secondary ms-1" id="badgeTotalRegistros">0 registros</span>
                        </div>
                        <div class="text-muted small">
                            Mostrando página <span id="spanPaginaActual" class="fw-bold">1</span> de <span id="spanTotalPaginas" class="fw-bold">1</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaMovimientos">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">ID</th>
                                    <th style="width: 105px;">Fecha</th>
                                    <th style="width: 95px;">Tipo</th>
                                    <th style="width: 115px;">Caja</th>
                                    <th style="width: 200px;">Categoría</th>
                                    <th>Descripción / Concepto</th>
                                    <th style="width: 140px;">Medio de Pago</th>
                                    <th style="width: 130px;">Responsable</th>
                                    <th style="width: 140px;" class="text-end">Monto</th>
                                    <th style="width: 90px;" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyMovimientos">
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                        Cargando movimientos contables...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2">
                        <div class="small text-muted" id="infoPaginacion">
                            Cargando información...
                        </div>
                        <nav aria-label="Paginación de movimientos">
                            <ul class="pagination pagination-sm mb-0" id="paginationControls">
                                <!-- Botones de paginación dinámicos -->
                            </ul>
                        </nav>
                    </div>
                </div>
                <?php else: ?>
                <!-- Vista para Operador: Únicamente Tarjeta de Saldo Caja Evelyn -->
                <div class="row mb-4">
                    <div class="col-md-6 col-lg-5 col-xl-4">
                        <div class="card stat-card stat-caja-evelyn p-4 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label text-uppercase fw-bold text-muted mb-1" style="letter-spacing: 0.5px;">Saldo Caja Evelyn</div>
                                    <div class="stat-value" id="statSaldoEvelyn" style="color: #6366f1; font-size: 2.2rem; font-weight: 800;">$ 0</div>
                                    <div class="small text-muted mt-2">
                                        <i class="fas fa-wallet me-1 text-primary"></i> Efectivo disponible en tu caja
                                    </div>
                                </div>
                                <div class="rounded-circle p-3 text-white shadow-sm" style="background-color: #6366f1;">
                                    <i class="fas fa-cash-register fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 bg-white p-4 shadow-sm">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-light p-3 me-3 text-primary flex-shrink-0" style="width: 54px; height: 54px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-info-circle fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold text-dark" style="font-family: 'Oxygen', sans-serif !important; line-height: 1.4 !important; font-size: 1.15rem;">Registro de Operaciones de Caja</h5>
                            <p class="text-muted mb-0" style="line-height: 1.5;">Para registrar un ingreso o salida de dinero utiliza los botones <strong>+ Nueva Entrada</strong> o <strong>+ Nueva Salida</strong> ubicados en la parte superior derecha.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </main>
        </div>
    </div>

    <!-- Modal Registrar / Editar Movimiento -->
    <div class="modal fade" id="modalMovimiento" tabindex="-1" aria-labelledby="modalMovimientoTitulo" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formMovimiento">
                    <input type="hidden" id="movId" name="id_movimiento" value="">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalMovimientoTitulo">
                            <i class="fas fa-plus-circle me-1 text-primary"></i> Registrar Movimiento
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body p-4">
                        <!-- Selector de Tipo: Entrada o Salida -->
                        <div class="mb-3 text-center">
                            <label class="form-label d-block fw-bold small text-muted text-uppercase mb-2">Tipo de Movimiento</label>
                            <div class="btn-group w-50" role="group">
                                <input type="radio" class="btn-check" name="tipo" id="tipoEntrada" value="entrada" checked>
                                <label class="btn btn-outline-success py-2 fw-bold" for="tipoEntrada">
                                    <i class="fas fa-arrow-down me-1"></i> Entrada de Dinero
                                </label>

                                <input type="radio" class="btn-check" name="tipo" id="tipoSalida" value="salida">
                                <label class="btn btn-outline-danger py-2 fw-bold" for="tipoSalida">
                                    <i class="fas fa-arrow-up me-1"></i> Salida / Gasto
                                </label>
                            </div>
                        </div>

                        <div class="row g-3">
                            <!-- Caja: Evelyn o Carlos -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small" id="lblMovCaja">
                                    <i class="fas fa-cash-register me-1 text-success"></i> ¿A qué caja entra el dinero? <span class="text-danger">*</span>
                                </label>
                                <select class="form-select fw-bold" id="movCaja" name="caja" required>
                                    <option value="Evelyn" selected>Caja Evelyn</option>
                                    <option value="Carlos">Caja Carlos</option>
                                </select>
                                <div class="form-text small" id="helpMovCaja">El dinero sumará al saldo de la caja seleccionada.</div>
                            </div>

                            <!-- Fecha -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Fecha <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="movFecha" name="fecha" required value="<?= date('Y-m-d') ?>">
                            </div>

                            <!-- Monto -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Monto (COP) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control fw-bold" id="movMonto" name="monto" placeholder="Ej: 150.000" required>
                                </div>
                            </div>

                            <!-- Categoría (Lista según Excel) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Categoría / Concepto <span class="text-danger">*</span></label>
                                <select class="form-select" id="movCategoria" name="categoria" required>
                                    <option value="">Seleccione una categoría...</option>
                                </select>
                            </div>

                            <!-- Método de Pago -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Medio de Pago</label>
                                <select class="form-select" id="movMetodoPago" name="metodo_pago">
                                    <!-- Opciones cargadas por JS -->
                                </select>
                            </div>

                            <!-- Responsable -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Responsable / Persona</label>
                                <input type="text" class="form-control" id="movResponsable" name="responsable" list="listResponsables" placeholder="Seleccione o escriba...">
                                <datalist id="listResponsables">
                                    <option value="Evelyn Acuña">
                                    <option value="Carlos Viveros">
                                    <option value="Andrés Diaz">
                                </datalist>
                            </div>

                            <!-- Comprobante / Factura -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small"># Comprobante / Recibo / Factura (Opcional)</label>
                                <input type="text" class="form-control" id="movComprobante" name="comprobante_numero" placeholder="Ej: REC-00123">
                            </div>

                            <!-- Reserva Relacionada -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold small"># ID de Reserva Vinculada (Opcional)</label>
                                <input type="number" class="form-control" id="movReserva" name="id_reserva" placeholder="Ej: 104">
                                <div class="form-text small">Si este movimiento corresponde al 20%, 80% o gasto de una reserva del sistema, indica su ID.</div>
                            </div>

                            <!-- Descripción / Observaciones -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold small">Descripción / Observación Detallada</label>
                                <textarea class="form-control" id="movDescripcion" name="descripcion" rows="3" placeholder="Detalles específicos del movimiento, factura, proveedor o cliente..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4" id="btnGuardarMovimiento">
                            <i class="fas fa-save me-1"></i> Guardar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script de Gestión Contable -->
    <script>
        const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;

        // Catálogo en memoria
        let catalogo = {
            categorias: { entradas: [], salidas: [] },
            metodos_pago: [],
            responsables: [],
            cajas: ['Evelyn', 'Carlos']
        };

        let currentPage = 1;

        // Formateador de moneda en pesos colombianos
        const formatoMoneda = new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            maximumFractionDigits: 0
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar carga de movimientos y totales
            cargarMovimientos(1);

            if (isAdmin) {
                // Listeners de filtros exclusivos de Admin
                $('#filtroMes, #filtroAnio, #filtroTipo, #filtroCaja, #filtroCategoria, #filtroMetodoPago').on('change', function() {
                    cargarMovimientos(1);
                });

                // Buscador con debounce
                let searchTimeout = null;
                $('#filtroSearch').on('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        cargarMovimientos(1);
                    }, 300);
                });

                // Limpiar filtros
                $('#btnLimpiarFiltros').on('click', function() {
                    $('#filtroMes').val('');
                    $('#filtroAnio').val('<?= $anioActual ?>');
                    $('#filtroTipo').val('');
                    $('#filtroCaja').val('');
                    $('#filtroCategoria').val('');
                    $('#filtroMetodoPago').val('');
                    $('#filtroSearch').val('');
                    actualizarSelectCategoriasFiltro();
                    cargarMovimientos(1);
                });

                // Cuando cambia el filtro de Tipo, actualizar las opciones de Categoría en el filtro
                $('#filtroTipo').on('change', function() {
                    actualizarSelectCategoriasFiltro();
                });

                // Botón exportar Excel
                $('#btnExportarExcel').on('click', function() {
                    exportarExcel();
                });
            }

            // Cambio de Tipo en el modal (Entrada vs Salida)
            $('input[name="tipo"]').on('change', function() {
                const tipoSel = $(this).val();
                actualizarLabelsCajaModal(tipoSel);
                actualizarCategoriasModal();
            });

            // Formateo del input monto al escribir
            $('#movMonto').on('input', function() {
                let val = this.value.replace(/\D/g, '');
                if (val) {
                    this.value = new Intl.NumberFormat('es-CO').format(parseInt(val, 10));
                } else {
                    this.value = '';
                }
            });

            // Submit del formulario de movimiento
            $('#formMovimiento').on('submit', function(e) {
                e.preventDefault();
                guardarMovimiento();
            });
        });

        // Cargar movimientos con la API
        function cargarMovimientos(page = 1) {
            currentPage = page;

            const params = new URLSearchParams({
                page: page,
                limit: 20
            });

            if (isAdmin) {
                const mes = $('#filtroMes').val();
                const anio = $('#filtroAnio').val();
                const tipo = $('#filtroTipo').val();
                const caja = $('#filtroCaja').val();
                const categoria = $('#filtroCategoria').val();
                const metodo_pago = $('#filtroMetodoPago').val();
                const search = $('#filtroSearch').val();

                if (mes) params.append('mes', mes);
                if (anio) params.append('anio', anio);
                if (tipo) params.append('tipo', tipo);
                if (caja) params.append('caja', caja);
                if (categoria) params.append('categoria', categoria);
                if (metodo_pago) params.append('metodo_pago', metodo_pago);
                if (search) params.append('search', search);

                $('#tbodyMovimientos').html(`
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                            Cargando movimientos...
                        </td>
                    </tr>
                `);
            }

            fetch(`/app/api/admin/contabilidad_get_movimientos.php?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Actualizar catálogo
                    if (data.catalogo) {
                        catalogo = data.catalogo;
                        actualizarCatalogosEnUI();
                    }

                    // Renderizar tarjetas de totales
                    renderTotales(data.totales);

                    // Renderizar tabla y paginación solo si es admin
                    if (isAdmin) {
                        renderTabla(data.movimientos);
                        renderPaginacion(data.pagination);
                    }
                } else {
                    if (isAdmin) {
                        $('#tbodyMovimientos').html(`
                            <tr>
                                <td colspan="10" class="text-center py-4 text-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Error: ${data.message || 'No se pudieron cargar los movimientos'}
                                </td>
                            </tr>
                        `);
                    }
                }
            })
            .catch(err => {
                console.error('Error al cargar movimientos:', err);
                if (isAdmin) {
                    $('#tbodyMovimientos').html(`
                        <tr>
                            <td colspan="10" class="text-center py-4 text-danger">
                                <i class="fas fa-exclamation-circle me-1"></i> Error de conexión con el servidor.
                            </td>
                        </tr>
                    `);
                }
            });
        }

        // Renderizar totales en las tarjetas según rol
        function renderTotales(totales) {
            if (!totales) return;

            // Saldo Caja Evelyn (visible tanto para admin como para operador)
            if ($('#statSaldoEvelyn').length) {
                $('#statSaldoEvelyn').text(formatoMoneda.format(totales.saldo_caja_evelyn || 0));
            }

            // Métricas exclusivas para Admin
            if (isAdmin) {
                if ($('#statSaldoCarlos').length) {
                    $('#statSaldoCarlos').text(formatoMoneda.format(totales.saldo_caja_carlos || 0));
                }

                $('#statEntradas').text(formatoMoneda.format(totales.total_entradas || 0));
                $('#statSalidas').text(formatoMoneda.format(totales.total_salidas || 0));

                const balance = totales.balance_neto || 0;
                const elBalance = $('#statBalance');
                elBalance.text((balance >= 0 ? '+ ' : '') + formatoMoneda.format(balance));
                if (balance >= 0) {
                    elBalance.removeClass('text-danger').addClass('text-primary');
                } else {
                    elBalance.removeClass('text-primary').addClass('text-danger');
                }

                $('#badgeTotalRegistros').text(`${totales.total_registros || 0} movimientos`);
            }
        }

        // Renderizar tabla de movimientos (Solo vista Admin)
        function renderTabla(movimientos) {
            const tbody = $('#tbodyMovimientos');
            if (!tbody.length) return;
            tbody.empty();

            if (!movimientos || movimientos.length === 0) {
                tbody.html(`
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                            <strong>No hay movimientos registrados</strong>
                            <p class="small mb-0">No se encontraron registros para los filtros seleccionados.</p>
                        </td>
                    </tr>
                `);
                return;
            }

            movimientos.forEach(m => {
                const isEntrada = m.tipo === 'entrada';
                const badgeTipo = isEntrada 
                    ? `<span class="badge badge-entrada"><i class="fas fa-arrow-down me-1"></i>Entrada</span>` 
                    : `<span class="badge badge-salida"><i class="fas fa-arrow-up me-1"></i>Salida</span>`;
                
                const cajaNombre = m.caja || 'Evelyn';
                const badgeCaja = (cajaNombre === 'Carlos')
                    ? `<span class="badge badge-caja-carlos"><i class="fas fa-vault me-1"></i>Caja Carlos</span>`
                    : `<span class="badge badge-caja-evelyn"><i class="fas fa-cash-register me-1"></i>Caja Evelyn</span>`;

                const signo = isEntrada ? '+ ' : '- ';
                const claseMonto = isEntrada ? 'monto-entrada' : 'monto-salida';

                const tr = $(`
                    <tr>
                        <td class="text-muted fw-bold">#${m.id_movimiento}</td>
                        <td>
                            <div class="fw-bold">${m.fecha}</div>
                        </td>
                        <td>${badgeTipo}</td>
                        <td>${badgeCaja}</td>
                        <td>
                            <strong class="text-dark">${escapeHtml(m.categoria)}</strong>
                            ${m.id_reserva ? `<br><small class="text-muted"><i class="fas fa-calendar-check me-1 text-primary"></i>Reserva #${m.id_reserva}</small>` : ''}
                        </td>
                        <td>
                            <div class="text-wrap" style="max-width: 300px;">
                                ${m.descripcion ? escapeHtml(m.descripcion) : '<span class="text-muted fst-italic">Sin descripción</span>'}
                                ${m.comprobante_numero ? `<div class="small text-muted"><i class="fas fa-receipt me-1"></i>Comp: <strong>${escapeHtml(m.comprobante_numero)}</strong></div>` : ''}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">${escapeHtml(m.metodo_pago || 'EFECTIVO')}</span>
                        </td>
                        <td>
                            <div class="small text-muted"><i class="fas fa-user-circle me-1"></i>${escapeHtml(m.responsable || 'No asignado')}</div>
                        </td>
                        <td class="text-end">
                            <span class="${claseMonto} fs-6">${signo}${formatoMoneda.format(m.monto)}</span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick='editarMovimiento(${JSON.stringify(m)})' title="Editar movimiento">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="eliminarMovimiento(${m.id_movimiento}, '${escapeHtml(m.categoria)}', ${m.monto})" title="Eliminar movimiento">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `);
                tbody.append(tr);
            });
        }

        // Renderizar paginación
        function renderPaginacion(pag) {
            if (!pag || !$('#paginationControls').length) return;
            $('#spanPaginaActual').text(pag.current_page);
            $('#spanTotalPaginas').text(pag.total_pages);
            
            const start = ((pag.current_page - 1) * pag.per_page) + 1;
            const end = Math.min(pag.current_page * pag.per_page, pag.total_records);
            $('#infoPaginacion').text(`Mostrando ${pag.total_records > 0 ? start : 0} - ${end} de ${pag.total_records} registros`);

            const ul = $('#paginationControls');
            ul.empty();

            if (pag.total_pages <= 1) return;

            // Anterior
            ul.append(`
                <li class="page-item ${pag.current_page <= 1 ? 'disabled' : ''}">
                    <button class="page-link" onclick="cargarMovimientos(${pag.current_page - 1})">&laquo;</button>
                </li>
            `);

            // Páginas
            for (let i = 1; i <= pag.total_pages; i++) {
                if (i === 1 || i === pag.total_pages || (i >= pag.current_page - 2 && i <= pag.current_page + 2)) {
                    ul.append(`
                        <li class="page-item ${i === pag.current_page ? 'active' : ''}">
                            <button class="page-link" onclick="cargarMovimientos(${i})">${i}</button>
                        </li>
                    `);
                } else if (i === pag.current_page - 3 || i === pag.current_page + 3) {
                    ul.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                }
            }

            // Siguiente
            ul.append(`
                <li class="page-item ${pag.current_page >= pag.total_pages ? 'disabled' : ''}">
                    <button class="page-link" onclick="cargarMovimientos(${pag.current_page + 1})">&raquo;</button>
                </li>
            `);
        }

        // Actualizar catálogos en los selects
        function actualizarCatalogosEnUI() {
            if (isAdmin) {
                // Métodos de pago en filtro
                const selectMetodoFiltro = $('#filtroMetodoPago');
                const selFiltroVal = selectMetodoFiltro.val();
                selectMetodoFiltro.find('option:not(:first)').remove();
                catalogo.metodos_pago.forEach(m => {
                    selectMetodoFiltro.append(`<option value="${m}">${m}</option>`);
                });
                if (selFiltroVal) selectMetodoFiltro.val(selFiltroVal);

                // Categorías en filtro
                actualizarSelectCategoriasFiltro();
            }

            // Métodos de pago en modal
            const selectMetodoModal = $('#movMetodoPago');
            selectMetodoModal.empty();
            catalogo.metodos_pago.forEach(m => {
                selectMetodoModal.append(`<option value="${m}">${m}</option>`);
            });
        }

        // Actualizar opciones de categoría en la barra de filtro (Admin)
        function actualizarSelectCategoriasFiltro() {
            const select = $('#filtroCategoria');
            if (!select.length) return;
            const currentVal = select.val();
            const tipo = $('#filtroTipo').val();

            select.find('option:not(:first)').remove();

            let lista = [];
            if (tipo === 'entrada') {
                lista = catalogo.categorias.entradas;
            } else if (tipo === 'salida') {
                lista = catalogo.categorias.salidas;
            } else {
                lista = [...new Set([...catalogo.categorias.entradas, ...catalogo.categorias.salidas])].sort();
            }

            lista.forEach(c => {
                select.append(`<option value="${c}">${c}</option>`);
            });

            if (currentVal && lista.includes(currentVal)) {
                select.val(currentVal);
            }
        }

        // Actualizar etiquetas dinámicas del selector de caja en modal
        function actualizarLabelsCajaModal(tipo) {
            if (tipo === 'salida') {
                $('#lblMovCaja').html('<i class="fas fa-cash-register me-1 text-danger"></i> ¿De qué caja sale el dinero? <span class="text-danger">*</span>');
                $('#helpMovCaja').text('El monto se descontará del saldo de la caja seleccionada.');
            } else {
                $('#lblMovCaja').html('<i class="fas fa-cash-register me-1 text-success"></i> ¿A qué caja entra el dinero? <span class="text-danger">*</span>');
                $('#helpMovCaja').text('El monto se sumará al saldo de la caja seleccionada.');
            }
        }

        // Actualizar opciones de categoría en el Modal según el Tipo seleccionado
        function actualizarCategoriasModal(selectedCategory = null) {
            const tipo = $('input[name="tipo"]:checked').val();
            const select = $('#movCategoria');
            select.empty();
            select.append('<option value="">Seleccione una categoría...</option>');

            const lista = (tipo === 'entrada') ? catalogo.categorias.entradas : catalogo.categorias.salidas;
            lista.forEach(c => {
                select.append(`<option value="${c}">${c}</option>`);
            });

            if (selectedCategory) {
                if (!lista.includes(selectedCategory)) {
                    select.append(`<option value="${selectedCategory}">${selectedCategory}</option>`);
                }
                select.val(selectedCategory);
            }
        }

        // Abrir modal para crear movimiento
        function abrirModalMovimiento(tipo = 'entrada') {
            $('#formMovimiento')[0].reset();
            $('#movId').val('');
            $('#movFecha').val(new Date().toISOString().split('T')[0]);
            $('#movCaja').val('Evelyn');

            if (tipo === 'salida') {
                $('#tipoSalida').prop('checked', true);
            } else {
                $('#tipoEntrada').prop('checked', true);
            }

            actualizarLabelsCajaModal(tipo);
            actualizarCategoriasModal();
            $('#modalMovimientoTitulo').html('<i class="fas fa-plus-circle me-1 text-primary"></i> Registrar ' + (tipo === 'salida' ? 'Salida / Gasto' : 'Entrada de Dinero'));
            $('#modalMovimiento').modal('show');
        }

        // Abrir modal para editar movimiento
        function editarMovimiento(m) {
            $('#movId').val(m.id_movimiento);
            $('#movFecha').val(m.fecha);
            $('#movMonto').val(new Intl.NumberFormat('es-CO').format(parseInt(m.monto, 10)));
            $('#movCaja').val(m.caja || 'Evelyn');
            $('#movMetodoPago').val(m.metodo_pago || 'EFECTIVO');
            $('#movResponsable').val(m.responsable || '');
            $('#movComprobante').val(m.comprobante_numero || '');
            $('#movReserva').val(m.id_reserva || '');
            $('#movDescripcion').val(m.descripcion || '');

            if (m.tipo === 'salida') {
                $('#tipoSalida').prop('checked', true);
            } else {
                $('#tipoEntrada').prop('checked', true);
            }

            actualizarLabelsCajaModal(m.tipo);
            actualizarCategoriasModal(m.categoria);
            $('#modalMovimientoTitulo').html(`<i class="fas fa-edit me-1 text-primary"></i> Editar Movimiento #${m.id_movimiento}`);
            $('#modalMovimiento').modal('show');
        }

        // Guardar movimiento vía AJAX
        function guardarMovimiento() {
            const btn = $('#btnGuardarMovimiento');
            const originalText = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');

            const formData = {
                id_movimiento: $('#movId').val(),
                tipo: $('input[name="tipo"]:checked').val(),
                caja: $('#movCaja').val(),
                fecha: $('#movFecha').val(),
                monto: $('#movMonto').val(),
                categoria: $('#movCategoria').val(),
                metodo_pago: $('#movMetodoPago').val(),
                responsable: $('#movResponsable').val(),
                comprobante_numero: $('#movComprobante').val(),
                id_reserva: $('#movReserva').val(),
                descripcion: $('#movDescripcion').val()
            };

            fetch('/app/api/admin/contabilidad_save_movimiento.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    $('#modalMovimiento').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: '¡Operación Exitosa!',
                        text: data.message,
                        timer: 1800,
                        showConfirmButton: false
                    });
                    cargarMovimientos(currentPage);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'No se pudo guardar el movimiento'
                    });
                }
            })
            .catch(err => {
                console.error('Error al guardar:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Red',
                    text: 'Ocurrió un error al comunicarse con el servidor.'
                });
            })
            .finally(() => {
                btn.prop('disabled', false).html(originalText);
            });
        }

        // Eliminar movimiento con confirmación
        function eliminarMovimiento(id, categoria, monto) {
            Swal.fire({
                title: '¿Eliminar movimiento?',
                html: `¿Estás seguro de que deseas eliminar el registro <strong>#${id} (${categoria})</strong> por valor de <strong>${formatoMoneda.format(monto)}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/app/api/admin/contabilidad_delete_movimiento.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ id_movimiento: id })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            cargarMovimientos(currentPage);
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo eliminar el movimiento', 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
                    });
                }
            });
        }

        // Exportar a Excel con los filtros actuales
        function exportarExcel() {
            const mes = $('#filtroMes').val();
            const anio = $('#filtroAnio').val();
            const tipo = $('#filtroTipo').val();
            const caja = $('#filtroCaja').val();
            const categoria = $('#filtroCategoria').val();
            const metodo_pago = $('#filtroMetodoPago').val();
            const search = $('#filtroSearch').val();

            const params = new URLSearchParams();
            if (mes) params.append('mes', mes);
            if (anio) params.append('anio', anio);
            if (tipo) params.append('tipo', tipo);
            if (caja) params.append('caja', caja);
            if (categoria) params.append('categoria', categoria);
            if (metodo_pago) params.append('metodo_pago', metodo_pago);
            if (search) params.append('search', search);

            window.location.href = `/app/api/admin/contabilidad_export.php?${params.toString()}`;
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</body>
</html>

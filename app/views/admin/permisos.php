<?php
/**
 * Panel de Administración - Permisos de Acceso (RBAC)
 * My Suite In Cartagena
 */

require_once __DIR__ . '/../../../includes/auth_helper.php';
AuthHelper::requireModule('permisos');

require_once __DIR__ . '/../../../config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permisos de Acceso (RBAC) - My Suite In Cartagena</title>
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
            background: #fff;
            min-height: 100vh;
            margin-top: 48px;
            padding: 25px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            background: #fff;
            margin-bottom: 25px;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #f8f9fa;
            padding: 18px 25px;
            border-top-left-radius: 15px !important;
            border-top-right-radius: 15px !important;
        }
        .table > :not(caption) > * > * {
            padding: 12px 16px;
            vertical-align: middle;
        }
        .form-switch .form-check-input {
            width: 2.7em;
            height: 1.4em;
            cursor: pointer;
        }
        .badge-privado {
            background-color: #fce8e6;
            color: #c5221f;
            border: 1px solid #fad2cf;
            font-weight: 600;
        }
        .badge-libre {
            background-color: #e6f4ea;
            color: #137333;
            border: 1px solid #ceead6;
            font-weight: 600;
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
        <input class="form-control form-control-dark w-100" type="text" placeholder="Search" aria-label="Search">
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3" href="/app/controllers/auth/logout.php">Sign out</a>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Dinámico RBAC -->
            <?php AuthHelper::renderSidebar('permisos'); ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <div>
                        <h1 class="h2 mb-1"><i class="fas fa-user-shield text-primary me-2"></i> Permisos de Acceso (RBAC)</h1>
                        <p class="text-muted mb-0">Control granular de acceso a módulos por roles y asignación de roles a usuarios</p>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <span class="badge bg-danger p-2 me-2"><i class="fas fa-lock me-1"></i> Módulo Exclusivo Admin</span>
                    </div>
                </div>

                <div id="alertContainer"></div>

                <!-- Resumen de Políticas -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card p-3 border-start border-danger border-4">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3 text-danger fs-4">
                                    <i class="fas fa-crown"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-muted">Rol Administrador</h6>
                                    <h4 class="mb-0 fw-bold">Acceso Total</h4>
                                    <small class="text-muted">Todos los módulos habilitados</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-3 border-start border-primary border-4">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary fs-4">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-muted">Rol Operador</h6>
                                    <h4 class="mb-0 fw-bold">Acceso Operativo</h4>
                                    <small class="text-muted">Reservas, Calendario y Contabilidad</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-3 border-start border-secondary border-4">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3 text-secondary fs-4">
                                    <i class="fas fa-users-slash"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-muted">Rol Cliente</h6>
                                    <h4 class="mb-0 fw-bold">Sin Acceso al Panel</h4>
                                    <small class="text-muted">Solo reservas en el frontend público</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Matriz de Permisos -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><i class="fas fa-th-list me-2 text-primary"></i> Matriz de Visibilidad por Módulo</h5>
                            <small class="text-muted">Define qué módulos puede ver y acceder cada rol en el panel</small>
                        </div>
                        <button type="button" class="btn btn-primary" id="btnGuardarPermisos" onclick="guardarPermisos()">
                            <i class="fas fa-save me-1"></i> Guardar Cambios de Permisos
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tablaPermisos">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 25%;">Módulo</th>
                                        <th style="width: 30%;">Ruta / Descripción</th>
                                        <th style="width: 15%;" class="text-center">Tipo de Módulo</th>
                                        <th style="width: 15%;" class="text-center text-danger">
                                            <i class="fas fa-crown me-1"></i> Administrador
                                        </th>
                                        <th style="width: 15%;" class="text-center text-primary">
                                            <i class="fas fa-user-tie me-1"></i> Operador
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="permisosTableBody">
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                            Cargando configuración de permisos...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-muted small py-3">
                        <i class="fas fa-info-circle me-1 text-primary"></i> 
                        <strong>PRIVADO:</strong> Solo visible para el rol Administrador. 
                        <strong>LIBRE:</strong> Habilitado para el Operador de la suite encargado de la atención de reservas.
                    </div>
                </div>

                <!-- Gestión de Roles de Usuarios -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="mb-0"><i class="fas fa-users-cog me-2 text-primary"></i> Asignación de Roles a Usuarios</h5>
                            <small class="text-muted">Promueve a usuarios a Operador o Administrador, o desactiva su acceso</small>
                        </div>
                        <div class="d-flex align-items-center" style="min-width: 260px;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" id="filtroUsuario" class="form-control" placeholder="Buscar por nombre o correo..." oninput="filtrarUsuarios()">
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0" id="tablaUsuarios">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Correo</th>
                                        <th>Teléfono</th>
                                        <th class="text-center">Rol Actual</th>
                                        <th style="width: 240px;">Cambiar Rol</th>
                                    </tr>
                                </thead>
                                <tbody id="usuariosTableBody">
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                            Cargando listado de usuarios...
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

    <script>
        let rbacData = {
            roles: [],
            modulos: [],
            permisos: {},
            usuarios: []
        };

        document.addEventListener('DOMContentLoaded', function() {
            cargarDatosRBAC();
        });

        function mostrarAlerta(tipo, mensaje) {
            const container = document.getElementById('alertContainer');
            const alertHtml = `
                <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                    <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    ${mensaje}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            container.innerHTML = alertHtml;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function cargarDatosRBAC() {
            fetch('/app/api/admin/get_rbac_data.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        rbacData = data;
                        renderMatrizPermisos();
                        renderUsuarios();
                    } else {
                        mostrarAlerta('danger', 'Error al cargar datos: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    mostrarAlerta('danger', 'Error de conexión con el servidor al cargar permisos.');
                });
        }

        function renderMatrizPermisos() {
            const tbody = document.getElementById('permisosTableBody');
            tbody.innerHTML = '';

            rbacData.modulos.forEach(mod => {
                const cod = mod.codigo;
                const esOperadorPermitido = (rbacData.permisos.operador && rbacData.permisos.operador[cod] == 1);
                
                // Módulos clasificados
                const esLibre = esOperadorPermitido;
                const badgeTipo = esLibre 
                    ? '<span class="badge badge-libre px-3 py-2"><i class="fas fa-door-open me-1"></i> LIBRE</span>' 
                    : '<span class="badge badge-privado px-3 py-2"><i class="fas fa-lock me-1"></i> PRIVADO</span>';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="rounded bg-light p-2 text-primary me-3 text-center" style="width: 38px;">
                                <i class="${mod.icono} fa-lg"></i>
                            </div>
                            <div>
                                <strong class="text-dark">${mod.nombre}</strong>
                                <br><small class="text-muted"><code>${cod}</code></small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="text-dark">${mod.descripcion || '-'}</span>
                        <br><small class="text-muted font-monospace">${mod.ruta}</small>
                    </td>
                    <td class="text-center" id="badge_tipo_${cod}">
                        ${badgeTipo}
                    </td>
                    <td class="text-center">
                        <div class="d-inline-flex align-items-center gap-1 text-danger fw-bold">
                            <i class="fas fa-check-circle"></i>
                            <span>Acceso Total</span>
                            <i class="fas fa-lock text-muted small ms-1" title="El rol administrador siempre tiene acceso total"></i>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input" type="checkbox" id="perm_operador_${cod}" 
                                ${esOperadorPermitido ? 'checked' : ''} 
                                onchange="onPermisoToggleChange('${cod}', this.checked)">
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function onPermisoToggleChange(cod, checked) {
            if (!rbacData.permisos.operador) {
                rbacData.permisos.operador = {};
            }
            rbacData.permisos.operador[cod] = checked ? 1 : 0;

            const badgeCell = document.getElementById(`badge_tipo_${cod}`);
            if (badgeCell) {
                badgeCell.innerHTML = checked 
                    ? '<span class="badge badge-libre px-3 py-2"><i class="fas fa-door-open me-1"></i> LIBRE</span>' 
                    : '<span class="badge badge-privado px-3 py-2"><i class="fas fa-lock me-1"></i> PRIVADO</span>';
            }
        }

        function guardarPermisos() {
            const btn = document.getElementById('btnGuardarPermisos');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

            fetch('/app/api/admin/save_rbac_permissions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    permisos: rbacData.permisos
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                if (data.success) {
                    mostrarAlerta('success', data.message);
                } else {
                    mostrarAlerta('danger', 'Error al guardar: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                console.error(err);
                mostrarAlerta('danger', 'Error de comunicación al guardar permisos.');
            });
        }

        function renderUsuarios(filtro = '') {
            const tbody = document.getElementById('usuariosTableBody');
            tbody.innerHTML = '';

            const term = filtro.toLowerCase().trim();
            const filtrados = rbacData.usuarios.filter(u => {
                const nombreCompleto = (u.nombre + ' ' + u.apellido).toLowerCase();
                const correo = (u.correo || '').toLowerCase();
                return nombreCompleto.includes(term) || correo.includes(term);
            });

            if (filtrados.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted">No se encontraron usuarios con ese criterio.</td></tr>`;
                return;
            }

            filtrados.forEach(u => {
                let badgeRol = '';
                if (u.rol === 'admin') {
                    badgeRol = '<span class="badge bg-danger px-2 py-1"><i class="fas fa-crown me-1"></i> Administrador</span>';
                } else if (u.rol === 'operador') {
                    badgeRol = '<span class="badge bg-primary px-2 py-1"><i class="fas fa-user-tie me-1"></i> Operador</span>';
                } else {
                    badgeRol = '<span class="badge bg-secondary px-2 py-1"><i class="fas fa-user me-1"></i> Cliente</span>';
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <div class="fw-bold text-dark">${u.nombre} ${u.apellido}</div>
                        <small class="text-muted">ID: #${u.id_usuario}</small>
                    </td>
                    <td>
                        <a href="mailto:${u.correo}" class="text-decoration-none">${u.correo}</a>
                    </td>
                    <td>
                        <span class="text-muted">${u.telefono || '-'}</span>
                    </td>
                    <td class="text-center" id="badge_rol_${u.id_usuario}">
                        ${badgeRol}
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <select class="form-select" id="select_rol_${u.id_usuario}">
                                <option value="cliente" ${u.rol === 'cliente' ? 'selected' : ''}>Cliente</option>
                                <option value="operador" ${u.rol === 'operador' ? 'selected' : ''}>Operador de Suite</option>
                                <option value="admin" ${u.rol === 'admin' ? 'selected' : ''}>Administrador</option>
                            </select>
                            <button class="btn btn-outline-primary" type="button" onclick="cambiarRolUsuario(${u.id_usuario})">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function filtrarUsuarios() {
            const query = document.getElementById('filtroUsuario').value;
            renderUsuarios(query);
        }

        function cambiarRolUsuario(idUsuario) {
            const select = document.getElementById(`select_rol_${idUsuario}`);
            const nuevoRol = select.value;

            if (!confirm(`¿Estás seguro de asignar el rol "${nuevoRol.toUpperCase()}" a este usuario?`)) {
                return;
            }

            fetch('/app/api/admin/update_user_role.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_usuario: idUsuario,
                    rol: nuevoRol
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('success', data.message);
                    // Actualizar memoria local
                    const u = rbacData.usuarios.find(item => item.id_usuario == idUsuario);
                    if (u) {
                        u.rol = nuevoRol;
                    }
                    renderUsuarios(document.getElementById('filtroUsuario').value);
                } else {
                    mostrarAlerta('danger', data.message || 'Error al actualizar rol');
                }
            })
            .catch(err => {
                console.error(err);
                mostrarAlerta('danger', 'Error de comunicación al actualizar rol.');
            });
        }
    </script>
</body>
</html>

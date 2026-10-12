<?php
/**
 * Helper de Autenticación y Control de Acceso Basado en Roles (RBAC)
 * My Suite In Cartagena
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

class AuthHelper {
    private static $permissions_cache = [];

    /**
     * Obtiene el rol del usuario actual
     */
    public static function getCurrentRole(): string {
        if (isset($_SESSION['user_rol']) && !empty($_SESSION['user_rol'])) {
            return $_SESSION['user_rol'];
        }
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            return 'admin';
        }
        return 'cliente';
    }

    /**
     * Verifica si el usuario actual es personal de administración (admin u operador)
     */
    public static function isStaff(): bool {
        $role = self::getCurrentRole();
        if ($role === 'admin' || $role === 'operador') {
            return true;
        }
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            return true;
        }
        return false;
    }

    /**
     * Verifica si el usuario actual es superadmin
     */
    public static function isAdmin(): bool {
        return self::getCurrentRole() === 'admin';
    }

    /**
     * Verifica si un rol específico tiene permiso para un módulo determinado
     */
    public static function hasPermission(string $moduloCodigo, ?string $role = null): bool {
        if ($role === null) {
            $role = self::getCurrentRole();
        }

        // Si es admin, tiene acceso total siempre
        if ($role === 'admin') {
            return true;
        }

        // Si no es un rol de staff (ej. cliente o no autenticado), no tiene acceso
        if ($role !== 'operador') {
            return false;
        }

        // Usar cache estática en la ejecución actual
        $cacheKey = $role . ':' . $moduloCodigo;
        if (isset(self::$permissions_cache[$cacheKey])) {
            return self::$permissions_cache[$cacheKey];
        }

        try {
            $database = new Database();
            $db = $database->getConnection();
            if (!$db) {
                // Fallback seguro si falla BD
                $permisos_default = [
                    'reservas' => true,
                    'calendario' => true,
                    'contabilidad' => true
                ];
                return $permisos_default[$moduloCodigo] ?? false;
            }

            $stmt = $db->prepare("SELECT permitido FROM rol_permisos WHERE rol_codigo = ? AND modulo_codigo = ?");
            $stmt->execute([$role, $moduloCodigo]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            $permitido = $res ? ((int)$res['permitido'] === 1) : false;
            self::$permissions_cache[$cacheKey] = $permitido;
            return $permitido;
        } catch (Exception $e) {
            error_log("Error en AuthHelper::hasPermission: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Determina si la petición actual es de tipo API / AJAX o espera JSON
     */
    public static function isApiRequest(): bool {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        if (strpos($uri, '/api/') !== false || strpos($script, '/api/') !== false) {
            return true;
        }
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (strpos($accept, 'application/json') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Asegura que el usuario tenga acceso al panel de administración.
     * Si no está logueado o es cliente, redirige al login o devuelve 401 JSON.
     */
    public static function requireStaffLogin(): void {
        if (!self::isStaff()) {
            if (self::isApiRequest()) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'Sesión expirada o no autenticado como staff.'
                ]);
                exit;
            }
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/app/views/admin/reservas.php';
            header('Location: /app/controllers/auth/login.php');
            exit;
        }
    }

    /**
     * Requiere permiso explícito para un módulo. Si no lo tiene, redirige o aborta con 403.
     */
    public static function requireModule(string $moduloCodigo): void {
        self::requireStaffLogin();

        if (!self::hasPermission($moduloCodigo)) {
            // Si es petición API o AJAX
            if (self::isApiRequest()) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'Acceso restringido: no tienes permisos para acceder a este módulo.'
                ]);
                exit;
            }

            // Redirigir al módulo predeterminado permitido para su rol (reservas)
            $_SESSION['flash_error'] = 'No tienes permisos para acceder al módulo solicitado.';
            header('Location: /app/views/admin/reservas.php');
            exit;
        }
    }

    /**
     * Obtiene los módulos permitidos para el rol dado
     */
    public static function getAllowedModules(?string $role = null): array {
        if ($role === null) {
            $role = self::getCurrentRole();
        }

        try {
            $database = new Database();
            $db = $database->getConnection();
            if (!$db) return [];

            if ($role === 'admin') {
                $query = "SELECT * FROM modulos WHERE activo = 1 ORDER BY orden ASC";
                $stmt = $db->query($query);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $query = "
                SELECT m.* 
                FROM modulos m
                INNER JOIN rol_permisos rp ON m.codigo = rp.modulo_codigo
                WHERE rp.rol_codigo = ? AND rp.permitido = 1 AND m.activo = 1
                ORDER BY m.orden ASC
            ";
            $stmt = $db->prepare($query);
            $stmt->execute([$role]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en AuthHelper::getAllowedModules: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Renderiza el sidebar de navegación del panel admin con los módulos permitidos
     */
    public static function renderSidebar(string $activeCode = ''): void {
        $role = self::getCurrentRole();
        $modules = self::getAllowedModules($role);
        $roleBadge = ($role === 'admin') 
            ? '<span class="badge bg-danger ms-1">Administrador</span>' 
            : '<span class="badge bg-primary ms-1">Operador</span>';
        $userName = htmlspecialchars($_SESSION['user_nombre'] ?? $_SESSION['admin_nombre'] ?? 'Usuario');

        echo '
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3 sidebar-sticky">
                <h4 class="sidebar-heading">
                    <i class="fas fa-plane me-2"></i>
                    Admin Panel
                </h4>
                <div class="px-3 pb-2 mb-2 border-bottom d-flex align-items-center justify-content-between">
                    <span class="text-truncate fw-bold text-muted small" style="max-width: 120px;" title="' . $userName . '">' . $userName . '</span>
                    ' . $roleBadge . '
                </div>
                <ul class="nav flex-column">';

        foreach ($modules as $mod) {
            $isActive = ($mod['codigo'] === $activeCode) ? 'active' : '';
            $ruta = htmlspecialchars($mod['ruta']);
            $icono = htmlspecialchars($mod['icono']);
            $nombre = htmlspecialchars($mod['nombre']);

            echo '
                    <li class="nav-item">
                        <a class="nav-link ' . $isActive . '" href="' . $ruta . '">
                            <i class="' . $icono . ' me-2"></i> ' . $nombre . '
                        </a>
                    </li>';
        }

        echo '
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Enlaces</span>
                    </h6>
                    <li class="nav-item">
                        <a class="nav-link" href="/index.php?lang=es" target="_blank">
                            <i class="fas fa-external-link-alt me-2 text-muted"></i> Ver Sitio Web
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="/app/controllers/auth/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </nav>';
    }
}

/**
 * Función procedimental de conveniencia
 */
function require_modulo(string $codigo): void {
    AuthHelper::requireModule($codigo);
}

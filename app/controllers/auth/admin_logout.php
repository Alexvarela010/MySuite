<?php
/**
 * Logout de Administrador - Sistema de Reservas
 * My Suite In Cartagena
 */

session_start();

// Limpiar todas las variables de sesión específicas del admin
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_nombre']);
unset($_SESSION['admin_correo']);

// Si el usuario solo estaba logueado como admin, también limpiar variables de usuario
// Pero si también estaba logueado como usuario regular, mantenerlas
if (isset($_SESSION['user_logged_in']) && isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin') {
    // Si el rol es admin, limpiar también las variables de usuario
    unset($_SESSION['user_logged_in']);
    unset($_SESSION['user_id']);
    unset($_SESSION['user_nombre']);
    unset($_SESSION['user_correo']);
    unset($_SESSION['user_rol']);
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Redirigir al login principal
header('Location: ../../app/controllers/auth/login.php');
exit;
?>


<?php
/**
 * Redirección al nuevo dashboard del admin
 */
session_start();
require_once __DIR__ . '/../includes/auth_helper.php';

AuthHelper::requireStaffLogin();

if (AuthHelper::hasPermission('dashboard')) {
    header('Location: ../app/views/admin/index.php');
} else {
    header('Location: ../app/views/admin/reservas.php');
}
exit;


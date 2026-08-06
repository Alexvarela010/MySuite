<?php
/**
 * Restablecer Contraseña - Sistema de Reservas
 * My Suite In Cartagena
 */

session_start();

// Verificar si ya está logueado
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: ../../index.php?lang=en');
    exit;
}

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$verified = $_GET['verified'] ?? false;
$validToken = false;
$user = null;

// Verificar token válido y que el código fue verificado
if ($token && $verified) {
    require_once __DIR__ . '/../../config/database.php';
    
    try {
        $database = new Database();
        $pdo = $database->getConnection();
        
        $query = "SELECT prt.*, u.nombre, u.apellido, u.correo 
                  FROM password_reset_tokens prt 
                  JOIN usuarios u ON prt.user_id = u.id_usuario 
                  WHERE prt.token = ? AND prt.expires_at > NOW() AND prt.used = FALSE";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$token]);
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($tokenData) {
            $validToken = true;
            $user = $tokenData;
        } else {
            $error = 'Token inválido o expirado';
        }
        
    } catch (Exception $e) {
        $error = 'Error del sistema: ' . $e->getMessage();
    }
} elseif ($token && !$verified) {
    // Si tiene token pero no está verificado, redirigir a verificación
    header('Location: ../../app/controllers/auth/verify-code.php?token=' . $token);
    exit;
} else {
    $error = 'Acceso no autorizado';
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    if (empty($newPassword) || empty($confirmPassword)) {
        $error = 'Por favor, complete todos los campos';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($newPassword) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres';
    } elseif (!preg_match('/[A-Z]/', $newPassword)) {
        $error = 'La contraseña debe contener al menos una letra mayúscula';
    } elseif (!preg_match('/[0-9]/', $newPassword)) {
        $error = 'La contraseña debe contener al menos un número';
    } elseif (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $newPassword)) {
        $error = 'La contraseña debe contener al menos un carácter especial (!@#$%^&*()_+-=[]{};\':"|,.<>/?])';
    } else {
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            
            // Verificar que la nueva contraseña no sea igual a la actual
            $queryCurrentPassword = "SELECT contrasena FROM usuarios WHERE id_usuario = ?";
            $stmtCurrentPassword = $pdo->prepare($queryCurrentPassword);
            $stmtCurrentPassword->execute([$user['user_id']]);
            $currentUser = $stmtCurrentPassword->fetch(PDO::FETCH_ASSOC);
            
            if ($currentUser && password_verify($newPassword, $currentUser['contrasena'])) {
                $error = 'La contraseña no puede ser igual a la actual';
            } else {
                // Actualizar contraseña
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateQuery = "UPDATE usuarios SET contrasena = ? WHERE id_usuario = ?";
                $updateStmt = $pdo->prepare($updateQuery);
                $updateResult = $updateStmt->execute([$hashedPassword, $user['user_id']]);
                
                if ($updateResult) {
                    // Marcar token como usado
                    $markUsedQuery = "UPDATE password_reset_tokens SET used = TRUE WHERE token = ?";
                    $markUsedStmt = $pdo->prepare($markUsedQuery);
                    $markUsedStmt->execute([$token]);
                    
                    $success = 'password_changed';
                } else {
                    $error = 'Error al actualizar la contraseña';
                }
            }
            
        } catch (Exception $e) {
            $error = 'Error del sistema: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - My Suite In Cartagena</title>
    <link rel="shortcut icon" href="images/favicon.png"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Zoom aplicado a toda la página - 75% pero ocupando todo el ancho */
        html {
            zoom: 0.75 !important;
            width: 100% !important;
            height: 100% !important;
            overflow-x: hidden !important;
        }
        
        /* Estilos completos inline para asegurar que se carguen */
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
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: url('../../../assets/shared/cartagena.webp');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            color: #343a40;
        }
        .login-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            max-width: 600px; 
            padding: 20px;
            box-sizing: border-box;
        }
        .login-container-ihg {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            width: 100%;
            padding: 50px;
            box-sizing: border-box;
        }
        .login-form-ihg {
            width: 100%;
        }
        .form-left-panel {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .logo-ihg h1 {
            font-size: 28px;
            color: rgb(199, 156, 65);
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .form-left-panel h2 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 30px;
            color: #343a40;
            font-weight: 600;
        }
        .input-group-ihg {
            margin-bottom: 20px;
        }
        .input-group-ihg label {
            display: block;
            margin-bottom: 8px;
            font-size: 15px;
            font-weight: 600;
            color: #343a40;
        }
        .input-group-ihg input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .input-group-ihg input:focus {
            outline: none;
            border-color: rgb(25, 130, 151);
            box-shadow: 0 0 0 3px rgba(25, 130, 151, 0.1);
        }
        .password-input-wrapper {
            position: relative;
            width: 100%;
        }
        .password-input-wrapper input {
            padding-right: 40px;
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #666;
            z-index: 10;
            padding: 5px;
            border-radius: 3px;
            transition: color 0.3s ease;
        }
        .toggle-password:hover {
            color: #007bff;
        }
        .login-button-ihg {
            background: rgb(199, 156, 65);
            color: white;
            padding: 18px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(199, 156, 65, 0.3);
            margin-top: 10px;
        }
        .login-button-ihg:hover {
            background: rgb(186, 117, 13);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(225, 143, 19, 0.7);
        }
        .links-ihg a {
            color: rgb(25, 130, 151);
            text-decoration: none;
            display: block;
            margin-top: 5px;
            font-weight: 500;
        }
        .links-ihg {
            text-align: center;
            margin-top: 20px;
        }
        .info-text {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
            font-size: 16px;
        }
        
        /* Modales estéticos pequeños */
        .custom-modal, .success-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            animation: fadeIn 0.3s ease;
        }
        
        .custom-modal-content, .success-modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            max-width: 380px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .modal-icon, .success-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .modal-icon.success, .success-icon {
            color: #28a745;
        }
        
        .modal-icon.error {
            color: #dc3545;
        }
        
        .modal-title, .success-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 12px;
            font-weight: 600;
        }
        
        .modal-message, .success-message {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.5;
            font-size: 15px;
        }
        
        .modal-btn, .success-btn {
            background: linear-gradient(135deg, rgb(199, 156, 65), rgb(186, 117, 13));
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .modal-btn:hover, .success-btn:hover {
            background: linear-gradient(135deg, rgb(186, 117, 13), rgb(170, 100, 10));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(199, 156, 65, 0.4);
        }
        
        .modal-btn:active, .success-btn:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="login-wrapper"> 
        <div class="login-container-ihg">
            <form action="reset-password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST" class="login-form-ihg" id="resetForm">
                <div class="form-left-panel">
                    <div class="logo-ihg">
                        <h1>My Suite In Cartagena </h1> 
                    </div>
                    <h2>Nueva Contraseña</h2>

                    <?php if ($error): ?>
                        <div class="custom-modal" id="errorModal" style="display: flex;">
                            <div class="custom-modal-content">
                                <div class="modal-icon error">❌</div>
                                <div class="modal-title">Error</div>
                                <div class="modal-message"><?php echo htmlspecialchars($error); ?></div>
                                <button class="modal-btn" onclick="closeModal('errorModal')">Aceptar</button>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success === 'password_changed'): ?>
                        <!-- Modal de confirmación -->
                        <div class="success-modal" id="successModal" style="display: flex;">
                            <div class="success-modal-content">
                                <div class="success-icon">✅</div>
                                <div class="success-title">¡Contraseña Actualizada!</div>
                                <div class="success-message">
                                    Tu contraseña ha sido cambiada exitosamente.<br>
                                    Ya puedes iniciar sesión con tu nueva contraseña.
                                </div>
                                <div id="countdown" style="margin-bottom: 20px; color: #666; font-size: 14px;">
                                    Redirigiendo automáticamente en <span id="timer">3</span> segundos...
                                </div>
                                <button class="success-btn" onclick="redirectToLogin()">
                                    Ir al Login
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($validToken): ?>
                        <div class="info-text">
                            ✅ <strong>Código verificado correctamente</strong><br>
                            Hola <strong><?php echo htmlspecialchars($user['nombre']); ?></strong>,<br>
                            Ahora puedes crear tu nueva contraseña.
                        </div>
                        
                        <div class="input-group-ihg password-group">
                            <label for="newPassword">Nueva Contraseña</label>
                            <div class="password-input-wrapper">
                                <input type="password" id="newPassword" name="newPassword" placeholder="Mínimo 8 caracteres" required minlength="8">
                                <span class="toggle-password" onclick="togglePassword()"><i class="fas fa-eye"></i></span>
                            </div>
                            <small style="color: #666; font-size: 13px; display: block; margin-top: 5px;">
                                La contraseña debe contener: al menos 8 caracteres, una mayúscula, un número y un carácter especial
                            </small>
                            <span class="error-message" id="newPasswordError"></span> 
                        </div>

                        <div class="input-group-ihg password-group">
                            <label for="confirmPassword">Confirmar Nueva Contraseña</label>
                            <div class="password-input-wrapper">
                                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Repite tu nueva contraseña" required minlength="8">
                                <span class="toggle-password" onclick="toggleConfirmPassword()"><i class="fas fa-eye"></i></span>
                            </div>
                            <span class="error-message" id="confirmPasswordError"></span> 
                        </div>
                        
                        <button type="submit" class="login-button-ihg">Cambiar Contraseña</button>
                    <?php else: ?>
                        <div class="info-text">
                            Enlace inválido o expirado. Por favor, solicita un nuevo enlace de recuperación.
                        </div>
                    <?php endif; ?>
                    
                    <div class="links-ihg">
                        <a href="login.php">Volver al Login</a> 
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Función para cerrar modales
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
            }
        }
        
        // Cerrar modal al hacer clic fuera de él
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('custom-modal') || e.target.classList.contains('success-modal')) {
                e.target.style.display = 'none';
            }
        });
        
        // Funciones para mostrar/ocultar contraseña
        function togglePassword() {
            const passwordInput = document.getElementById('newPassword');
            const toggleIcon = document.querySelector('.password-input-wrapper .toggle-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                passwordInput.type = 'password';
                toggleIcon.innerHTML = '<i class="fas fa-eye"></i>';
            }
        }
        
        function toggleConfirmPassword() {
            const passwordInput = document.getElementById('confirmPassword');
            const toggleIcon = document.querySelectorAll('.password-input-wrapper .toggle-password')[1];
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                passwordInput.type = 'password';
                toggleIcon.innerHTML = '<i class="fas fa-eye"></i>';
            }
        }
        
        // Función para redirigir al login
        function redirectToLogin() {
            window.location.href = 'login.php';
        }
        
        // Mostrar modal automáticamente si la contraseña fue cambiada
        document.addEventListener('DOMContentLoaded', function() {
            const successModal = document.getElementById('successModal');
            if (successModal) {
                let countdown = 3;
                const timerElement = document.getElementById('timer');
                
                // Actualizar contador cada segundo
                const countdownInterval = setInterval(function() {
                    countdown--;
                    if (timerElement) {
                        timerElement.textContent = countdown;
                    }
                    
                    if (countdown <= 0) {
                        clearInterval(countdownInterval);
                        redirectToLogin();
                    }
                }, 1000);
            }
        });

        // Función para mostrar modal de error
        function showErrorModal(message) {
            // Eliminar modal anterior si existe
            const existingModal = document.getElementById('jsErrorModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            const modal = document.createElement('div');
            modal.className = 'custom-modal';
            modal.id = 'jsErrorModal';
            modal.style.display = 'flex';
            
            const modalContent = document.createElement('div');
            modalContent.className = 'custom-modal-content';
            
            const icon = document.createElement('div');
            icon.className = 'modal-icon error';
            icon.textContent = '❌';
            
            const title = document.createElement('div');
            title.className = 'modal-title';
            title.textContent = 'Error';
            
            const messageDiv = document.createElement('div');
            messageDiv.className = 'modal-message';
            messageDiv.textContent = message;
            
            const button = document.createElement('button');
            button.className = 'modal-btn';
            button.textContent = 'Aceptar';
            button.addEventListener('click', function() {
                closeModal('jsErrorModal');
            });
            
            modalContent.appendChild(icon);
            modalContent.appendChild(title);
            modalContent.appendChild(messageDiv);
            modalContent.appendChild(button);
            modal.appendChild(modalContent);
            
            document.body.appendChild(modal);
            
            // Cerrar al hacer clic fuera del modal
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal('jsErrorModal');
                }
            });
        }
        
        // Validación del formulario
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            let isValid = true;
            let errorMessage = '';
            
            // Limpiar errores anteriores
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            document.querySelectorAll('input').forEach(el => el.classList.remove('error'));
            
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Validar nueva contraseña
            if (!newPassword) {
                errorMessage = 'La nueva contraseña es requerida';
                document.getElementById('newPassword').classList.add('error');
                isValid = false;
            } else if (newPassword.length < 8) {
                errorMessage = 'La contraseña debe tener al menos 8 caracteres';
                document.getElementById('newPassword').classList.add('error');
                isValid = false;
            } else {
                // Validar requisitos de seguridad
                const hasUpperCase = /[A-Z]/.test(newPassword);
                const hasNumber = /[0-9]/.test(newPassword);
                const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(newPassword);
                
                if (!hasUpperCase || !hasNumber || !hasSpecialChar) {
                    errorMessage = 'La contraseña debe contener: al menos una mayúscula, un número y un carácter especial';
                    document.getElementById('newPassword').classList.add('error');
                    isValid = false;
                }
            }
            
            // Validar confirmación de contraseña
            if (isValid && !confirmPassword) {
                errorMessage = 'Confirma tu nueva contraseña';
                document.getElementById('confirmPassword').classList.add('error');
                isValid = false;
            } else if (isValid && newPassword !== confirmPassword) {
                errorMessage = 'Las contraseñas no coinciden';
                document.getElementById('confirmPassword').classList.add('error');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                if (errorMessage) {
                    showErrorModal(errorMessage);
                }
            }
        });
    </script>
</body>
</html>

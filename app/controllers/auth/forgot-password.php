<?php
/**
 * Recuperación de Contraseña - Sistema de Reservas
 * My Suite In Cartagena
 */

session_start();

// Verificar si ya está logueado
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    // Si es admin, redirigir al panel
    if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin') {
        header('Location: ../../app/views/admin/index.php');
        exit;
    } else {
        // Si es cliente, redirigir al calendario
        header('Location: ../../index.php?lang=en');
        exit;
    }
}

$error = '';
$success = '';
$step = 'email'; // email, code, password
$userEmail = '';
$token = '';

// Procesar solicitud de código
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_code') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Por favor, ingresa tu email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresa un email válido';
    } else {    
        require_once __DIR__ . '/../../../config/database.php';
        
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            
            // Verificar si el email existe
            $query = "SELECT id_usuario, nombre, apellido, correo FROM usuarios WHERE correo = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Generar token único y código de verificación
                $token = bin2hex(random_bytes(32));
                $verificationCode = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
                $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                
                // Eliminar tokens anteriores del usuario
                $deleteQuery = "DELETE FROM password_reset_tokens WHERE user_id = ?";
                $deleteStmt = $pdo->prepare($deleteQuery);
                $deleteStmt->execute([$user['id_usuario']]);
                
                // Guardar nuevo token con código
                $insertQuery = "INSERT INTO password_reset_tokens (user_id, token, verification_code, expires_at) VALUES (?, ?, ?, ?)";
                $insertStmt = $pdo->prepare($insertQuery);
                $insertStmt->execute([$user['id_usuario'], $token, $verificationCode, $expires]);
                
                // Enviar correo
                require_once __DIR__ . '/../../../includes/GmailSender.php';
                $gmailSender = new GmailSender();
                
                $to = $user['correo'];
                $subject = "Código de Verificación - My Suite In Cartagena";
                
                $htmlContent = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: linear-gradient(135deg, rgb(199, 156, 65), rgb(186, 117, 13)); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                        .code-box { background: white; border: 3px solid rgb(199, 156, 65); border-radius: 10px; padding: 20px; text-align: center; margin: 20px 0; }
                        .verification-code { font-size: 32px; font-weight: bold; color: rgb(199, 156, 65); letter-spacing: 5px; }
                        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1> My Suite In Cartagena</h1>
                            <h2>Código de Verificación</h2>
                        </div>
                        <div class='content'>
                            <h3>Hola " . htmlspecialchars($user['nombre']) . ",</h3>
                            <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta.</p>
                            <p>Para continuar, ingresa el siguiente código de verificación:</p>
                            
                            <div class='code-box'>
                                <div class='verification-code'>" . $verificationCode . "</div>
                            </div>
                            
                            <p><strong>Este código expirará en 15 minutos por seguridad.</strong></p>
                            <p>Si no solicitaste este cambio, puedes ignorar este correo.</p>
                        </div>
                        <div class='footer'>
                            <p>© 2025 My Suite In Cartagena. Todos los derechos reservados.</p>
                        </div>
                    </div>
                </body>
                </html>";
                
                $result = $gmailSender->sendEmail($to, $subject, $htmlContent, true, null, false); // false = sin CC a gerencia
                
                if ($result) {
                    $step = 'code';
                    $userEmail = $email;
                    $success = 'Se envió un código de verificación al correo registrado';
                } else {
                    $error = 'Error al enviar el correo';
                }
                
            } else {
                $error = 'Este email no está registrado en nuestro sistema';
            }
            
        } catch (Exception $e) {
            $error = 'Error del sistema: ' . $e->getMessage();
        }
    }
}

// Procesar verificación de código
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_code') {
    $code = trim($_POST['code'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Si no hay userEmail, intentar obtenerlo del POST
    if (empty($userEmail) && !empty($email)) {
        $userEmail = $email;
    }
    
    // Validaciones en orden de prioridad
    if (empty($code)) {
        $error = 'Por favor, ingresa el código de verificación';
    } elseif (strlen($code) !== 6 || !is_numeric($code)) {
        $error = 'El código debe tener 6 dígitos';
    } elseif (empty($userEmail) && empty($email)) {
        $error = 'Por favor, ingresa tu correo primero';
        $step = 'email';
    } elseif (!empty($email) && !empty($userEmail) && $email !== $userEmail) {
        $error = 'El correo no puede ser modificado';
    } else {
        require_once __DIR__ . '/../../../config/database.php';
        
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            
            // Usar el correo del POST si userEmail está vacío, sino usar userEmail
            $emailToUse = !empty($userEmail) ? $userEmail : $email;
            
            // Verificar el código
            $query = "SELECT prt.*, u.correo FROM password_reset_tokens prt 
                      JOIN usuarios u ON prt.user_id = u.id_usuario 
                      WHERE u.correo = ? AND prt.verification_code = ? AND prt.expires_at > NOW() AND prt.used = FALSE";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$emailToUse, $code]);
            $codeData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($codeData) {
                $step = 'password';
                // Mantener el correo original
                $userEmail = $codeData['correo'];
                $token = $codeData['token'];
                $success = 'Código verificado correctamente. Ahora puedes crear tu nueva contraseña.';
            } else {
                $error = 'Código incorrecto';
                // Mantener en step 'code' para que pueda intentar de nuevo
                // Asegurar que userEmail se mantenga
                if (empty($userEmail) && !empty($email)) {
                    $userEmail = $email;
                }
            }
            
        } catch (Exception $e) {
            $error = 'Error del sistema: ' . $e->getMessage();
        }
    }
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $email = $_POST['email'] ?? '';
    $token = $_POST['token'] ?? '';
    
    // Si no hay userEmail, intentar obtenerlo del POST
    if (empty($userEmail) && !empty($email)) {
        $userEmail = $email;
    }
    
    // Validaciones de contraseña primero
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
        require_once __DIR__ . '/../../../config/database.php';
        
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            
            // Usar el correo del POST si userEmail está vacío, sino usar userEmail
            $emailToUse = !empty($userEmail) ? $userEmail : $email;
            
            // Verificar token válido
            $query = "SELECT prt.*, u.id_usuario FROM password_reset_tokens prt 
                      JOIN usuarios u ON prt.user_id = u.id_usuario 
                      WHERE u.correo = ? AND prt.token = ? AND prt.expires_at > NOW() AND prt.used = FALSE";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$emailToUse, $token]);
            $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($tokenData) {
                // Verificar que la nueva contraseña no sea igual a la actual
                $queryCurrentPassword = "SELECT contrasena FROM usuarios WHERE id_usuario = ?";
                $stmtCurrentPassword = $pdo->prepare($queryCurrentPassword);
                $stmtCurrentPassword->execute([$tokenData['user_id']]);
                $currentUser = $stmtCurrentPassword->fetch(PDO::FETCH_ASSOC);
                
                if ($currentUser && password_verify($newPassword, $currentUser['contrasena'])) {
                    $error = 'La contraseña no puede ser igual a la actual';
                    // Mantener en step 'password' para que pueda intentar de nuevo
                    // Asegurar que userEmail se mantenga
                    if (empty($userEmail) && !empty($email)) {
                        $userEmail = $email;
                    }
                } else {
                    // Actualizar contraseña
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $updateQuery = "UPDATE usuarios SET contrasena = ? WHERE id_usuario = ?";
                    $updateStmt = $pdo->prepare($updateQuery);
                    $updateResult = $updateStmt->execute([$hashedPassword, $tokenData['user_id']]);
                    
                    if ($updateResult) {
                        // Marcar token como usado
                        $markUsedQuery = "UPDATE password_reset_tokens SET used = TRUE WHERE token = ?";
                        $markUsedStmt = $pdo->prepare($markUsedQuery);
                        $markUsedStmt->execute([$token]);
                        
                        $step = 'success';
                        $success = 'Contraseña actualizada exitosamente';
                    } else {
                        $error = 'Error al actualizar la contraseña';
                    }
                }
            } else {
                $error = 'Token inválido o expirado';
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
    <title>Recuperar Contraseña - My Suite In Cartagena</title>
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
            padding: 35px;
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
            margin-bottom: -13px;
            font-weight: bold;
        }
        .form-left-panel h2 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 6px;
            color: #343a40;
            font-weight: 600;
        }
        .input-group-ihg {
            margin-bottom: 4px;
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
            border-color: rgb(199, 156, 65);
            box-shadow: 0 0 0 3px rgba(199, 156, 65, 0.1);
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
            margin-top: 10px;
        }
        .login-button-ihg:hover {
            background: rgb(186, 117, 13);
            transform: translateY(-2px);
        }
        .links-ihg a {
            color: rgb(199, 156, 65);
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
            margin-bottom: -4px;
            line-height: 1.5;
            font-size: 16px;
        }
        
        /* Modales estéticos pequeños */
        .custom-modal {
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
        
        .custom-modal-content {
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
        
        .modal-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .modal-icon.success {
            color: #28a745;
        }
        
        .modal-icon.error {
            color: #dc3545;
        }
        
        .modal-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 12px;
            font-weight: 600;
        }
        
        .modal-message {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.5;
            font-size: 15px;
        }
        
        .modal-btn {
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
        
        .modal-btn:hover {
            background: linear-gradient(135deg, rgb(186, 117, 13), rgb(170, 100, 10));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(199, 156, 65, 0.4);
        }
        
        .modal-btn:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="login-wrapper"> 
        <div class="login-container-ihg">
            <div class="login-form-ihg">
                <div class="form-left-panel">
                    <div class="logo-ihg">
                        <h1> My Suite In Cartagena</h1> 
                    </div>
                    
                    <?php if ($step === 'email'): ?>
                        <h2>Restablecer Contraseña</h2>
                        
                        <div class="info-text">
                            Ingresa tu email y te enviaremos un código de verificación para restablecer tu contraseña.
                        </div>
                        
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
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="send_code">
                            <div class="input-group-ihg">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" placeholder="ej: tu_nombre@email.com" required>
                            </div>
                            <button type="submit" class="login-button-ihg">Enviar Código</button>
                        </form>
                    
                    <?php elseif ($step === 'code'): ?>
                        <h2>Verificar Código</h2>
                        
                        <div class="info-text">
                            Se envió un código de verificación al correo registrado.<br>
                            Ingresa el código de 6 dígitos para continuar.
                        </div>
                        
                        <?php if ($success && !$error): ?>
                            <div class="custom-modal" id="successModal" style="display: flex;">
                                <div class="custom-modal-content">
                                    <div class="modal-icon success">✅</div>
                                    <div class="modal-title">Éxito</div>
                                    <div class="modal-message"><?php echo htmlspecialchars($success); ?></div>
                                    <button class="modal-btn" onclick="closeModal('successModal')">Aceptar</button>
                                </div>
                            </div>
                        <?php endif; ?>
                        
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
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="verify_code">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" id="email_hidden">
                            <div class="input-group-ihg">
                                <label for="code">Código de Verificación</label>
                                <input type="text" id="code" name="code" placeholder="000000" required maxlength="6" pattern="[0-9]{6}" style="text-align: center; letter-spacing: 3px; font-size: 24px; font-weight: bold;">
                            </div>
                            <button type="submit" class="login-button-ihg">Verificar Código</button>
                        </form>
                    
                    <?php elseif ($step === 'password'): ?>
                        <h2>Nueva Contraseña</h2>
                        
                        <div class="info-text">
                            Crea tu nueva contraseña siguiendo los requisitos de seguridad.
                        </div>
                        
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
                        
                        <?php if ($success): ?>
                            <div class="custom-modal" id="successModal" style="display: flex;">
                                <div class="custom-modal-content">
                                    <div class="modal-icon success">✅</div>
                                    <div class="modal-title">Éxito</div>
                                    <div class="modal-message"><?php echo htmlspecialchars($success); ?></div>
                                    <button class="modal-btn" onclick="closeModal('successModal')">Aceptar</button>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" id="email_hidden_password">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            
                            <div class="input-group-ihg password-group">
                                <label for="newPassword">Nueva Contraseña</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="newPassword" name="newPassword" placeholder="Mínimo 8 caracteres" required minlength="8">
                                    <span class="toggle-password" onclick="togglePassword()"><i class="fas fa-eye"></i></span>
                                </div>
                                <small style="color: #666; font-size: 13px; display: block; margin-top: 5px;">
                                    La contraseña debe contener: al menos 8 caracteres, una mayúscula, un número y un carácter especial
                                </small>
                            </div>
                            
                            <div class="input-group-ihg password-group">
                                <label for="confirmPassword">Confirmar Nueva Contraseña</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Repite tu nueva contraseña" required minlength="8">
                                    <span class="toggle-password" onclick="toggleConfirmPassword()"><i class="fas fa-eye"></i></span>
                                </div>
                            </div>
                            
                            <button type="submit" class="login-button-ihg">Cambiar Contraseña</button>
                        </form>
                    
                    <?php elseif ($step === 'success'): ?>
                        <h2>¡Contraseña Actualizada!</h2>
                        <div class="custom-modal" id="successModal" style="display: flex;">
                            <div class="custom-modal-content">
                                <div class="modal-icon success">✅</div>
                                <div class="modal-title">¡Contraseña Actualizada!</div>
                                <div class="modal-message">
                                    Tu contraseña ha sido cambiada exitosamente.<br>
                                    Ya puedes iniciar sesión con tu nueva contraseña.
                                </div>
                                <button class="modal-btn" onclick="window.location.href='login.php'">Ir al Login</button>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="links-ihg">
                        <a href="login.php">Volver a Iniciar Sesión</a> 
                    </div>
                </div>
            </div>
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
            if (e.target.classList.contains('custom-modal')) {
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
        
        // Auto-focus en el input del código
        document.addEventListener('DOMContentLoaded', function() {
            const codeInput = document.getElementById('code');
            if (codeInput) {
                codeInput.focus();
            }
        });
        
        // Solo permitir números en el input del código
        const codeInput = document.getElementById('code');
        if (codeInput) {
            codeInput.addEventListener('input', function(e) {
                // Remover cualquier carácter que no sea número
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Si tiene 6 dígitos, auto-submit
                if (this.value.length === 6) {
                    this.form.submit();
                }
            });
            
            // Prevenir pegar texto que no sean números
            codeInput.addEventListener('paste', function(e) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                const numbers = paste.replace(/[^0-9]/g, '').substring(0, 6);
                this.value = numbers;
                
                if (numbers.length === 6) {
                    this.form.submit();
                }
            });
        }
        
        // Proteger campos ocultos del correo - prevenir modificación
        const emailHiddenCode = document.getElementById('email_hidden');
        const emailHiddenPassword = document.getElementById('email_hidden_password');
        
        if (emailHiddenCode) {
            const originalEmailCode = emailHiddenCode.value;
            // Monitorear cambios y restaurar el valor original
            setInterval(function() {
                if (emailHiddenCode.value !== originalEmailCode) {
                    emailHiddenCode.value = originalEmailCode;
                }
            }, 100);
        }
        
        if (emailHiddenPassword) {
            const originalEmailPassword = emailHiddenPassword.value;
            // Monitorear cambios y restaurar el valor original
            setInterval(function() {
                if (emailHiddenPassword.value !== originalEmailPassword) {
                    emailHiddenPassword.value = originalEmailPassword;
                }
            }, 100);
        }
        
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
        
        // Validación del formulario de contraseña
        const passwordForm = document.querySelector('input[name="action"][value="change_password"]')?.closest('form');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                
                if (!newPassword) {
                    e.preventDefault();
                    showErrorModal('La nueva contraseña es requerida');
                    return;
                }
                
                if (newPassword.length < 8) {
                    e.preventDefault();
                    showErrorModal('La contraseña debe tener al menos 8 caracteres');
                    return;
                }
                
                // Validar requisitos de seguridad
                const hasUpperCase = /[A-Z]/.test(newPassword);
                const hasNumber = /[0-9]/.test(newPassword);
                const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(newPassword);
                
                if (!hasUpperCase || !hasNumber || !hasSpecialChar) {
                    e.preventDefault();
                    showErrorModal('La contraseña debe contener: al menos una mayúscula, un número y un carácter especial');
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    showErrorModal('Las contraseñas no coinciden');
                    return;
                }
            });
        }
    </script>
</body>
</html>

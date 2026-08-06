<?php
/**
 * Login de Usuarios - Sistema de Reservas (VERSIÓN OPTIMIZADA)
 * My Suite In Cartagena
 */

// Limpiar sesión completamente
session_start();
session_destroy();
session_start();

// Inicializar sistema de internacionalización
require_once __DIR__ . '/../../../includes/i18n/i18n.php';

// Detectar idioma desde parámetro GET, referer o sesión
$lang = 'es'; // Por defecto español
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'es', 'it'])) {
    $lang = $_GET['lang'];
} elseif (isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    // Buscar ?lang= en el referer
    if (preg_match('/[?&]lang=([a-z]{2})/', $referer, $matches)) {
        if (in_array($matches[1], ['en', 'es', 'it'])) {
            $lang = $matches[1];
        }
    } elseif (strpos($referer, '/es/') !== false) {
        $lang = 'es';
    } elseif (strpos($referer, '/it/') !== false) {
        $lang = 'it';
    }
} elseif (isset($_SESSION['user_lang']) && in_array($_SESSION['user_lang'], ['en', 'es', 'it'])) {
    $lang = $_SESSION['user_lang'];
}

// Inicializar i18n con el idioma detectado
I18n::init($lang);

$error = '';
$success = '';
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = __('login.error.empty_fields');
    } else {
        require_once __DIR__ . '/../../../config/database.php';
        
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            if (!$db) {
                throw new Exception("Error de conexión a la base de datos");
            }
            
            // Buscar usuario
            $query = "SELECT id_usuario, nombre, apellido, correo, contrasena, rol FROM usuarios WHERE correo = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['contrasena'])) {
                // Login exitoso - limpiar sesión anterior
                session_destroy();
                session_start();
                
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $user['id_usuario'];
                $_SESSION['user_nombre'] = $user['nombre'] . ' ' . $user['apellido'];
                $_SESSION['user_correo'] = $user['correo'];
                $_SESSION['user_rol'] = $user['rol'];
                
                // Detectar idioma desde POST, GET, referer o sesión
                $lang = 'es'; // Por defecto español
                
                // 1. Primero verificar si viene en POST (formulario)
                if (isset($_POST['lang']) && in_array($_POST['lang'], ['en', 'es', 'it'])) {
                    $lang = $_POST['lang'];
                }
                // 2. Luego verificar GET
                elseif (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'es', 'it'])) {
                    $lang = $_GET['lang'];
                }
                // 3. Verificar en el referer (buscar ?lang=)
                elseif (isset($_SERVER['HTTP_REFERER'])) {
                    $referer = $_SERVER['HTTP_REFERER'];
                    if (preg_match('/[?&]lang=([a-z]{2})/', $referer, $matches)) {
                        if (in_array($matches[1], ['en', 'es', 'it'])) {
                            $lang = $matches[1];
                        }
                    } elseif (strpos($referer, '/es/') !== false) {
                        $lang = 'es';
                    } elseif (strpos($referer, '/it/') !== false) {
                        $lang = 'it';
                    }
                }
                // 4. Si hay idioma guardado en sesión, usarlo
                elseif (isset($_SESSION['user_lang']) && in_array($_SESSION['user_lang'], ['en', 'es', 'it'])) {
                    $lang = $_SESSION['user_lang'];
                }
                
                // Guardar idioma en sesión para mantenerlo
                $_SESSION['user_lang'] = $lang;
                
                // Determinar URL de redirección (ruta absoluta desde la raíz)
                $redirectUrl = '/index.php?lang=' . $lang;
                
                // Si es administrador, establecer también la sesión de admin
                if ($user['rol'] === 'admin') {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['id_usuario'];
                    $_SESSION['admin_nombre'] = $user['nombre'] . ' ' . $user['apellido'];
                    $_SESSION['admin_correo'] = $user['correo'];
                    
                    if ($isAjax) {
                        // Devolver JSON para AJAX
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'redirect' => $redirectUrl,
                            'message' => 'Login exitoso como administrador'
                        ]);
                        exit;
                    } else {
                        header('Location: ' . $redirectUrl);
                        exit;
                    }
                } else {
                    if ($isAjax) {
                        // Devolver JSON para AJAX
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'redirect' => $redirectUrl,
                            'message' => 'Login exitoso'
                        ]);
                        exit;
                    } else {
                        // Si es cliente, redirigir al calendario
                        header('Location: ' . $redirectUrl);
                        exit;
                    }
                }
            } else {
                $error = __('login.error.credentials');
            }
            
        } catch (Exception $e) {
            $error = __('login.error.system') . ': ' . $e->getMessage();
        }
    }
    
    // Si es AJAX y hay error, devolver JSON
    if ($isAjax && $error) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $error
        ]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo I18n::getLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('login.title'); ?></title>
    <link rel="shortcut icon" href="../../../assets/shared/favicon.png"/>
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
            max-width: 900px; 
            padding: 20px;
            box-sizing: border-box;
        }
        .login-container-ihg {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            display: flex;
            overflow: hidden;
            min-height: 500px;
        }
        .login-form-ihg {
            display: flex;
            width: 100%;
        }
        .form-left-panel {
            flex: 1; 
            padding: 40px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 15px; 
            border-right: 1px solid #ced4da;
        }
        .form-right-panel {
            flex: 1; 
            padding: 40px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 15px;
            background-color: #f8f9fa;
        }
        .logo-ihg h1 {  
            font-size: 24px;
            color: rgb(199, 156, 65);
            display: flex;
            justify-content: center;
            text-align: center;
            margin-bottom: 0px;
        }
        .form-left-panel h2 {
            font-size: 28px;
            text-align: left;
            margin-bottom: 25px;
            color: #343a40;
        }
        .form-right-panel h3 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 15px;
            color: #343a40
            ;
        }
        .input-group-ihg {
            margin-bottom: 15px;
        }
        .input-group-ihg label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: bold;
            color: #343a40;
        }
        .input-group-ihg input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .login-button-ihg {
            background:  rgb(199, 156, 65);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(44, 82, 130, 0.3);
        }
        .login-button-ihg:hover {
            background: rgb(186, 117, 13);
            transform: translateY(-2px);
        }
        .register-button-ihg {
            background: rgb(199, 156, 65);
            color: white;
            padding: 12px 4px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            width: 100%;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .register-button-ihg:hover {
            background:  rgb(186, 117, 13);
            color: white;
            transform: translateY(-2px);
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
            color:rgb(255, 0, 0);
        }
        .links-ihg a {
            color:rgb(183, 143, 32);
            text-decoration: none;
            display: block;
            margin-top: 5px;    
        }
        .links-ihg .need-help {
            color: #343a40;
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .checkbox-group-ihg {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .checkbox-group-ihg input[type="checkbox"] {
            width: auto;
        }
        .checkbox-group-ihg label {
            margin: 0;
            font-weight: normal;
        }
        
        /* Estilos para login social */
        .social-login {
            margin-top: 20px;
        }
        
        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e1e5e9;
        }
        
        .divider span {
            background: #f8f9fa;
            padding: 0 15px;
            color: #666;
            font-size: 14px;
        }
        
        .social-btn {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            background: white;
            color: #333;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .google-btn {
            border-color: #db4437;
            color: #db4437;
        }
        
        .google-btn:hover {
            background: #db4437;
            color: white;
        }
        
        .facebook-btn {
            border-color: #4267B2;
            color: #4267B2;
        }
        
        .facebook-btn:hover {
            background: #4267B2;
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-wrapper"> 
        <div class="login-container-ihg">
            <form action="login.php?lang=<?php echo I18n::getLanguage(); ?>" method="POST" class="login-form-ihg" id="loginForm">
                <input type="hidden" name="lang" value="<?php echo I18n::getLanguage(); ?>"> 
                
                <div class="form-left-panel">
                    <div class="logo-ihg">
                                                <h1><a href="https://mysuiteincartagena.com.co/index.php" target="_self" rel="noopener noreferrer" style="color:inherit; text-decoration:none;">My Suite In Cartagena</a></h1>
                    </div>
                    <h2><?php echo __('login.heading'); ?></h2>

                    <?php if ($error): ?>
                        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #f5c6cb;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-warning" style="background-color: #fff3cd; color: #856404; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #ffeaa7;">
                            <?php
                            switch($_GET['error']) {
                                case 'oauth_not_configured':
                                    echo __('login.error.oauth_not_configured');
                                    break;
                                case 'google_error':
                                    echo __('login.error.google');
                                    break;
                                case 'facebook_error':
                                    echo __('login.error.facebook');
                                    break;
                                default:
                                    echo __('login.error.generic');
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="input-group-ihg">
                        <label for="username"><?php echo __('login.email_label'); ?></label>
                        <input type="email" id="username" name="username"  value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                        <span class="error-message" id="userError"></span> 
                    </div>
                    
                    <div class="input-group-ihg password-group">
                        <label for="password"><?php echo __('login.password_label'); ?></label>
                        <div class="password-input-wrapper">
                            <input type="password" id="password" name="password"  required minlength="8">
                            <span class="toggle-password" onclick="togglePassword()"><i class="fas fa-eye"></i></span> 
                        </div>
                        <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                            <?php echo __('login.password_requirements'); ?>
                        </small>
                        <span class="error-message" id="passError"></span> 
                    </div>

                    <div class="checkbox-group-ihg">
                        <input type="checkbox" id="rememberMe" name="rememberMe">
                        <label for="rememberMe"><?php echo __('login.remember_me'); ?></label>
                    </div>
                    
                    <button type="submit" class="login-button-ihg" ><?php echo __('login.submit'); ?></button>
                    
                    <div class="links-ihg">
                        <span class="need-help"><?php echo __('login.need_help'); ?></span>
                        <a href="forgot-password.php?lang=<?php echo I18n::getLanguage(); ?>"><?php echo __('login.reset_password'); ?></a>
                    </div>
                </div>

                <div class="form-right-panel" style="padding-top: 100px;">
                    <h3><?php echo __('login.not_member'); ?></h3>
                    <p style="text-align: center;"><?php echo __('login.member_description'); ?></p>
                    <a href="register.php?lang=<?php echo I18n::getLanguage(); ?>" class="register-button-ihg"><?php echo __('login.register_now'); ?></a>
                    
                    <div class="social-login">
                        <div class="divider">
                            <span><?php echo __('login.continue_with'); ?></span>
                        </div>
                        
                        
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Función para mostrar/ocultar contraseña
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                passwordInput.type = 'password';
                toggleIcon.innerHTML = '<i class="fas fa-eye"></i>';
            }
        }
        
        // Funciones para login social
        function loginWithGoogle() {
            // Redirigir a Google OAuth
            window.location.href = 'social_login.php?provider=google&lang=<?php echo I18n::getLanguage(); ?>';
        }
        
        function loginWithFacebook() {
            // Redirigir a Facebook OAuth
            window.location.href = 'social_login.php?provider=facebook&lang=<?php echo I18n::getLanguage(); ?>';
        }

        // Validación del lado del cliente
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Limpiar errores anteriores
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            document.querySelectorAll('input').forEach(el => el.classList.remove('error'));
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            // Traducciones para JavaScript
            const translations = {
                email_required: <?php echo json_encode(__('login.validation.email_required')); ?>,
                email_invalid: <?php echo json_encode(__('login.validation.email_invalid')); ?>,
                password_required: <?php echo json_encode(__('login.validation.password_required')); ?>,
                password_min: <?php echo json_encode(__('login.validation.password_min')); ?>,
                password_requirements: <?php echo json_encode(__('login.validation.password_requirements')); ?>
            };
            
            // Validar email
            if (!username) {
                document.getElementById('userError').textContent = translations.email_required;
                document.getElementById('username').classList.add('error');
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(username)) {
                document.getElementById('userError').textContent = translations.email_invalid;
                document.getElementById('username').classList.add('error');
                isValid = false;
            }
            
            // Validar contraseña
            if (!password) {
                document.getElementById('passError').textContent = translations.password_required;
                document.getElementById('password').classList.add('error');
                isValid = false;
            } else if (password.length < 8) {
                document.getElementById('passError').textContent = translations.password_min;
                document.getElementById('password').classList.add('error');
                isValid = false;
            } else {
                // Validar requisitos de seguridad
                const hasUpperCase = /[A-Z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
                
                let errors = [];
                // Nota: Los mensajes de error específicos se pueden traducir si se necesitan
                // Por ahora, usamos el mensaje genérico de traducciones
                if (!hasUpperCase || !hasNumber || !hasSpecialChar) {
                    document.getElementById('passError').textContent = translations.password_requirements;
                    document.getElementById('password').classList.add('error');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>

<?php
/**
 * Registro de Usuarios - Sistema de Reservas
 * My Suite In Cartagena
 */

session_start();

// Inicializar sistema de internacionalización
require_once __DIR__ . '/../../../includes/i18n/i18n.php';

// Detectar idioma desde parámetro GET, POST, referer o sesión
$lang = 'es'; // Por defecto español
if (isset($_POST['lang']) && in_array($_POST['lang'], ['en', 'es', 'it'])) {
    $lang = $_POST['lang'];
} elseif (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'es', 'it'])) {
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

// Guardar idioma en sesión
$_SESSION['user_lang'] = $lang;

// Inicializar i18n con el idioma detectado
I18n::init($lang);

// Verificar si ya está logueado
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    // Si es admin, redirigir al panel
    if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin') {
        header('Location: ../../app/views/admin/index.php');
        exit;
    } else {
        // Si es cliente, redirigir al calendario del idioma correspondiente (ruta absoluta)
        header('Location: /index.php?lang=' . I18n::getLanguage());
        exit;
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $dob = $_POST['dob'] ?? '';
    
    // Validaciones
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword) || empty($phone) || empty($dob)) {
        $error = __('register.error.all_fields');
    } elseif ($password !== $confirmPassword) {
        $error = __('register.error.password_mismatch');
    } elseif (strlen($password) < 8) {
        $error = __('register.error.password_min');
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = __('register.error.password_uppercase');
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = __('register.error.password_number');
    } elseif (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
        $error = __('register.error.password_special');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = __('register.error.email_invalid');
    } else {
        require_once __DIR__ . '/../../../config/database.php';
        
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            if (!$db) {
                throw new Exception("Error de conexión a la base de datos");
            }
            
            // Verificar si el email ya existe
            $query = "SELECT id_usuario FROM usuarios WHERE correo = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = __('register.error.email_exists');
            } else {
                // Crear nuevo usuario
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $query = "INSERT INTO usuarios (nombre, apellido, correo, contrasena, telefono, fecha_nacimiento, rol) VALUES (?, ?, ?, ?, ?, ?, 'cliente')";
                $stmt = $db->prepare($query);
                $result = $stmt->execute([$firstName, $lastName, $email, $hashedPassword, $phone, $dob]);
                
                if ($result) {
                    $success = 'account_created';
                } else {
                    $error = __('register.error.create_account');
                }
            }
            
        } catch (Exception $e) {
            $error = __('register.error.system') . ': ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo I18n::getLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('register.title'); ?></title>
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
            box-shadow: 0 4px 15px rgba(199, 156, 65, 0.3);
            margin-top: 10px;
        }
        .login-button-ihg:hover {
            background: rgb(186, 117, 13);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(225, 143, 19, 0.7);
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
            box-shadow: 0 4px 15px rgba(199, 156, 65, 0.3);
        }
        .register-button-ihg:hover {
            background: rgb(186, 117, 13);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(225, 143, 19, 0.7);
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
        .links-ihg a {
            color: #007bff;
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
        
        /* Estilos para secciones del formulario */
        .form-section {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .section-header h3 {
            font-size: 20px;
            color: rgb(199, 156, 65);
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .section-header p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        
        .section-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .next-btn, .back-btn {
            flex: 1;
            padding: 15px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .next-btn {
            background: linear-gradient(135deg, rgb(199, 156, 65), rgb(186, 117, 13));
            color: white;
          
        }
        
        .next-btn:hover {
            background:rgb(186, 117, 13);
            transform: translateY(-2px);
        }
        
        .back-btn {
            background: #f8f9fa;
            color: #666;
            border: 2px solid #e1e5e9;
        }
        
        .back-btn:hover {
            background: #e9ecef;
            color: #495057;
            border-color: #ced4da;
        }
        
        /* Indicador de progreso */
        .progress-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .progress-step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
        }
        
        .progress-step.active {
            background: rgb(199, 156, 65);
            color: white;
        }
        
        .progress-step.completed {
            background:rgb(255, 213, 0);
            color: white;
        }
        
        .progress-step.inactive {
            background: #e9ecef;
            color: #6c757d;
        }
        
        /* Modal de confirmación */
        .success-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }
        
        .success-modal-content {
            background: white;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .success-icon {
            font-size: 48px;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .success-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .success-message {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        
        .success-btn {
            background: linear-gradient(135deg, rgb(199, 156, 65), rgb(186, 117, 13));
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .success-btn:hover {
            background: linear-gradient(135deg, rgb(199, 156, 65), rgb(186, 117, 13));
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="login-wrapper"> 
        <div class="login-container-ihg">
            <form action="register.php?lang=<?php echo I18n::getLanguage(); ?>" method="POST" class="login-form-ihg" id="registrationForm">
                <input type="hidden" name="lang" value="<?php echo I18n::getLanguage(); ?>">
                <div class="form-left-panel">
                    <div class="logo-ihg">
                        <h1> My Suite In Cartagena</h1> 
                    </div>
                    <h2><?php echo __('register.heading'); ?></h2>
                    
                    <!-- Indicador de progreso -->
                    <div class="progress-indicator">
                        <div class="progress-step active" id="step1">1</div>
                        <div class="progress-step inactive" id="step2">2</div>
                        <div class="progress-step inactive" id="step3">3</div>
                        <div class="progress-step inactive" id="step4">4</div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb; text-align: center;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success === 'account_created'): ?>
                        <!-- Modal de confirmación -->
                        <div class="success-modal" id="successModal">
                            <div class="success-modal-content">
                                <div class="success-icon">✅</div>
                                <div class="success-title"><?php echo __('register.success.title'); ?></div>
                                <div class="success-message">
                                    <?php echo str_replace('\\n', '<br>', __('register.success.message')); ?>
                                </div>
                                <div id="countdown" style="margin-bottom: 20px; color: #666; font-size: 14px;">
                                    <?php echo __('register.success.redirecting'); ?> <span id="timer">3</span> <?php echo __('register.success.seconds'); ?>
                                </div>
                                <button class="success-btn" onclick="redirectToLogin()">
                                    <?php echo __('register.success.go_login'); ?>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Sección 1: Nombre y Apellido -->
                    <div class="form-section" id="section1">
                        <div class="section-header">
                            <h3><?php echo __('register.step'); ?> 1 <?php echo __('register.of'); ?> 4</h3>
                            <p><?php echo __('register.step1.title'); ?></p>
                        </div>
                        
                        <div class="input-group-ihg">
                            <label for="firstName"><?php echo __('register.first_name'); ?></label>
                            <input type="text" id="firstName" name="firstName" placeholder="<?php echo __('register.first_name_placeholder'); ?>" value="<?php echo htmlspecialchars($firstName ?? ''); ?>" required>
                            <span class="error-message" id="firstNameError"></span> 
                        </div>

                        <div class="input-group-ihg">
                            <label for="lastName"><?php echo __('register.last_name'); ?></label>
                            <input type="text" id="lastName" name="lastName" placeholder="<?php echo __('register.last_name_placeholder'); ?>" value="<?php echo htmlspecialchars($lastName ?? ''); ?>" required>
                            <span class="error-message" id="lastNameError"></span> 
                        </div>
                        
                        <button type="button" class="next-btn" onclick="nextSection(1)"><?php echo __('register.continue'); ?></button>
                    </div>

                    <!-- Sección 2: Email -->
                    <div class="form-section" id="section2" style="display: none;">
                        <div class="section-header">
                            <h3><?php echo __('register.step'); ?> 2 <?php echo __('register.of'); ?> 4</h3>
                            <p><?php echo __('register.step2.title'); ?></p>
                        </div>
                        
                        <div class="input-group-ihg">
                            <label for="email"><?php echo __('register.email'); ?></label>
                            <input type="email" id="email" name="email" placeholder="<?php echo __('register.email_placeholder'); ?>" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            <span class="error-message" id="emailError"></span> 
                        </div>
                        
                        <div class="section-buttons">
                            <button type="button" class="back-btn" onclick="prevSection(2)"><?php echo __('register.back'); ?></button>
                            <button type="button" class="next-btn" onclick="nextSection(2)"><?php echo __('register.continue'); ?></button>
                        </div>
                    </div>

                    <!-- Sección 3: Contraseñas -->
                    <div class="form-section" id="section3" style="display: none;">
                        <div class="section-header">
                            <h3><?php echo __('register.step'); ?> 3 <?php echo __('register.of'); ?> 4</h3>
                            <p><?php echo __('register.step3.title'); ?></p>
                        </div>
                        
                        <div class="input-group-ihg password-group">
                            <label for="password"><?php echo __('register.password'); ?></label>
                            <div class="password-input-wrapper">
                                <input type="password" id="password" name="password" placeholder="<?php echo __('register.password_placeholder'); ?>" required minlength="8">
                                <span class="toggle-password" onclick="togglePassword()"><i class="fas fa-eye"></i></span>
                            </div>
                            <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                                <?php echo __('register.password_requirements'); ?>
                            </small>
                            <span class="error-message" id="passError"></span> 
                        </div>

                        <div class="input-group-ihg password-group">
                            <label for="confirmPassword"><?php echo __('register.confirm_password'); ?></label>
                            <div class="password-input-wrapper">
                                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="<?php echo __('register.confirm_password_placeholder'); ?>" required minlength="8">
                                <span class="toggle-password" onclick="toggleConfirmPassword()"><i class="fas fa-eye"></i></span>
                            </div>
                            <span class="error-message" id="confirmPassError"></span> 
                        </div>
                        
                        <div class="section-buttons">
                            <button type="button" class="back-btn" onclick="prevSection(3)"><?php echo __('register.back'); ?></button>
                            <button type="button" class="next-btn" onclick="nextSection(3)"><?php echo __('register.continue'); ?></button>
                        </div>
                    </div>

                    <!-- Sección 4: Teléfono y Fecha -->
                    <div class="form-section" id="section4" style="display: none;">
                        <div class="section-header">
                            <h3><?php echo __('register.step'); ?> 4 <?php echo __('register.of'); ?> 4</h3>
                            <p><?php echo __('register.step4.title'); ?></p>
                        </div>
                        
                        <div class="input-group-ihg">
                            <label for="phone"><?php echo __('register.phone'); ?></label>
                            <input type="tel" id="phone" name="phone" placeholder="<?php echo __('register.phone_placeholder'); ?>" value="<?php echo htmlspecialchars($phone ?? ''); ?>" required>
                            <span class="error-message" id="phoneError"></span> 
                        </div>

                        <div class="input-group-ihg">
                            <label for="dob"><?php echo __('register.birthday'); ?></label>
                            <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($dob ?? ''); ?>" required>
                            <span class="error-message" id="dobError"></span> 
                        </div>
                        
                        <div class="section-buttons">
                            <button type="button" class="back-btn" onclick="prevSection(4)"><?php echo __('register.back'); ?></button>
                            <button type="submit" class="login-button-ihg"><?php echo __('register.submit'); ?></button>
                        </div>
                    </div>
                    
                    <div class="links-ihg" style="text-align: center; margin-top: 20px;">
                        <a href="login.php?lang=<?php echo I18n::getLanguage(); ?>" style="color: rgb(199, 156, 65); text-decoration: none; font-weight: 500;"><?php echo __('register.already_account'); ?></a> 
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Traducciones para JavaScript
        const translations = {
            first_name_required: <?php echo json_encode(__('register.validation.first_name_required')); ?>,
            last_name_required: <?php echo json_encode(__('register.validation.last_name_required')); ?>,
            email_required: <?php echo json_encode(__('register.validation.email_required')); ?>,
            email_invalid: <?php echo json_encode(__('register.validation.email_invalid')); ?>,
            password_required: <?php echo json_encode(__('register.validation.password_required')); ?>,
            password_min: <?php echo json_encode(__('register.validation.password_min')); ?>,
            password_requirements: <?php echo json_encode(__('register.validation.password_requirements')); ?>,
            confirm_password_required: <?php echo json_encode(__('register.validation.confirm_password_required')); ?>,
            passwords_mismatch: <?php echo json_encode(__('register.validation.passwords_mismatch')); ?>,
            phone_required: <?php echo json_encode(__('register.validation.phone_required')); ?>,
            birthday_required: <?php echo json_encode(__('register.validation.birthday_required')); ?>,
            success_title: <?php echo json_encode(__('register.success.title')); ?>,
            success_message: <?php echo json_encode(__('register.success.message')); ?>,
            success_redirecting: <?php echo json_encode(__('register.success.redirecting')); ?>,
            success_seconds: <?php echo json_encode(__('register.success.seconds')); ?>,
            success_go_login: <?php echo json_encode(__('register.success.go_login')); ?>
        };
        
        // Variables globales para el control de secciones
        let currentSection = 1;
        const totalSections = 4;
        
        // Funciones para navegación entre secciones
        function nextSection(sectionNumber) {
            if (validateSection(sectionNumber)) {
                // Marcar sección actual como completada
                document.getElementById(`step${sectionNumber}`).classList.remove('active');
                document.getElementById(`step${sectionNumber}`).classList.add('completed');
                
                // Ocultar sección actual
                document.getElementById(`section${sectionNumber}`).style.display = 'none';
                
                // Mostrar siguiente sección
                const nextSectionNum = sectionNumber + 1;
                document.getElementById(`section${nextSectionNum}`).style.display = 'block';
                
                // Actualizar indicador de progreso
                document.getElementById(`step${nextSectionNum}`).classList.remove('inactive');
                document.getElementById(`step${nextSectionNum}`).classList.add('active');
                
                currentSection = nextSectionNum;
            }
        }
        
        function prevSection(sectionNumber) {
            // Marcar sección actual como inactiva
            document.getElementById(`step${sectionNumber}`).classList.remove('active');
            document.getElementById(`step${sectionNumber}`).classList.add('inactive');
            
            // Ocultar sección actual
            document.getElementById(`section${sectionNumber}`).style.display = 'none';
            
            // Mostrar sección anterior
            const prevSectionNum = sectionNumber - 1;
            document.getElementById(`section${prevSectionNum}`).style.display = 'block';
            
            // Actualizar indicador de progreso
            document.getElementById(`step${prevSectionNum}`).classList.remove('completed');
            document.getElementById(`step${prevSectionNum}`).classList.add('active');
            
            currentSection = prevSectionNum;
        }
        
        // Función para validar cada sección
        function validateSection(sectionNumber) {
            let isValid = true;
            
            // Limpiar errores anteriores
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            document.querySelectorAll('input').forEach(el => el.classList.remove('error'));
            
            if (sectionNumber === 1) {
                // Validar nombre y apellido
                const firstName = document.getElementById('firstName').value.trim();
                const lastName = document.getElementById('lastName').value.trim();
                
                if (!firstName) {
                    document.getElementById('firstNameError').textContent = translations.first_name_required;
                    document.getElementById('firstName').classList.add('error');
                    isValid = false;
                }
                
                if (!lastName) {
                    document.getElementById('lastNameError').textContent = translations.last_name_required;
                    document.getElementById('lastName').classList.add('error');
                    isValid = false;
                }
            } else if (sectionNumber === 2) {
                // Validar email
                const email = document.getElementById('email').value.trim();
                
                if (!email) {
                    document.getElementById('emailError').textContent = translations.email_required;
                    document.getElementById('email').classList.add('error');
                    isValid = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    document.getElementById('emailError').textContent = translations.email_invalid;
                    document.getElementById('email').classList.add('error');
                    isValid = false;
                }
            } else if (sectionNumber === 3) {
                // Validar contraseñas
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                
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
                    
                    if (!hasUpperCase || !hasNumber || !hasSpecialChar) {
                        document.getElementById('passError').textContent = translations.password_requirements;
                        document.getElementById('password').classList.add('error');
                        isValid = false;
                    }
                }
                
                if (!confirmPassword) {
                    document.getElementById('confirmPassError').textContent = translations.confirm_password_required;
                    document.getElementById('confirmPassword').classList.add('error');
                    isValid = false;
                } else if (password !== confirmPassword) {
                    document.getElementById('confirmPassError').textContent = translations.passwords_mismatch;
                    document.getElementById('confirmPassword').classList.add('error');
                    isValid = false;
                }
            } else if (sectionNumber === 4) {
                // Validar teléfono y fecha de nacimiento
                const phone = document.getElementById('phone').value.trim();
                const dob = document.getElementById('dob').value;
                
                if (!phone) {
                    document.getElementById('phoneError').textContent = translations.phone_required;
                    document.getElementById('phone').classList.add('error');
                    isValid = false;
                }
                
                if (!dob) {
                    document.getElementById('dobError').textContent = translations.birthday_required;
                    document.getElementById('dob').classList.add('error');
                    isValid = false;
                }
            }
            
            return isValid;
        }
        
        // Funciones para mostrar/ocultar contraseña
        function togglePassword() {
            const passwordInput = document.getElementById('password');
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
            window.location.href = 'login.php?lang=<?php echo I18n::getLanguage(); ?>';
        }
        
        // Mostrar modal automáticamente si la cuenta fue creada
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

        // Validación final del formulario (solo para la última sección)
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            // Validar la última sección antes de enviar
            if (!validateSection(4)) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>

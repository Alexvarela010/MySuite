<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Cargar configuración OAuth
if (file_exists('oauth_config.php')) {
    require_once __DIR__ . '/../../config/oauth_config.php';
} else {
    // Configuración por defecto (debes crear oauth_config.php con tus credenciales reales)
    $google_client_id = 'TU_GOOGLE_CLIENT_ID';
    $google_client_secret = 'TU_GOOGLE_CLIENT_SECRET';
    $google_redirect_uri = 'http://localhost/en/social_login.php?provider=google';

    $facebook_app_id = 'TU_FACEBOOK_APP_ID';
    $facebook_app_secret = 'TU_FACEBOOK_APP_SECRET';
    $facebook_redirect_uri = 'http://localhost/en/social_login.php?provider=facebook';
}

$provider = $_GET['provider'] ?? '';

// Verificar si las credenciales están configuradas
if ($google_client_id === 'TU_GOOGLE_CLIENT_ID' || $facebook_app_id === 'TU_FACEBOOK_APP_ID') {
    header('Location: ../../app/controllers/auth/login.php?error=oauth_not_configured');
    exit;
}

if ($provider === 'google') {
    handleGoogleLogin();
} elseif ($provider === 'facebook') {
    handleFacebookLogin();
} else {
    header('Location: ../../app/controllers/auth/login.php');
    exit;
}

function handleGoogleLogin() {
    global $google_client_id, $google_redirect_uri;
    
    if (!isset($_GET['code'])) {
        // Primera fase: redirigir a Google
        $auth_url = "https://accounts.google.com/o/oauth2/auth?" . http_build_query([
            'client_id' => $google_client_id,
            'redirect_uri' => $google_redirect_uri,
            'scope' => 'email profile',
            'response_type' => 'code',
            'access_type' => 'offline'
        ]);
        header('Location: ' . $auth_url);
        exit;
    } else {
        // Segunda fase: intercambiar código por token
        $code = $_GET['code'];
        $token_url = 'https://oauth2.googleapis.com/token';
        
        $post_data = [
            'client_id' => $google_client_id,
            'client_secret' => $GLOBALS['google_client_secret'],
            'redirect_uri' => $google_redirect_uri,
            'grant_type' => 'authorization_code',
            'code' => $code
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $token_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $token_data = json_decode($response, true);
        
        if (isset($token_data['access_token'])) {
            // Obtener información del usuario
            $user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $token_data['access_token'];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $user_info_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $user_response = curl_exec($ch);
            curl_close($ch);
            
            $user_data = json_decode($user_response, true);
            
            if ($user_data && isset($user_data['email'])) {
                processSocialLogin($user_data, 'google');
            } else {
                header('Location: ../../app/controllers/auth/login.php?error=google_error');
                exit;
            }
        } else {
            header('Location: ../../app/controllers/auth/login.php?error=google_token_error');
            exit;
        }
    }
}

function handleFacebookLogin() {
    global $facebook_app_id, $facebook_redirect_uri;
    
    if (!isset($_GET['code'])) {
        // Primera fase: redirigir a Facebook
        $auth_url = "https://www.facebook.com/v18.0/dialog/oauth?" . http_build_query([
            'client_id' => $facebook_app_id,
            'redirect_uri' => $facebook_redirect_uri,
            'scope' => 'email',
            'response_type' => 'code'
        ]);
        header('Location: ' . $auth_url);
        exit;
    } else {
        // Segunda fase: intercambiar código por token
        $code = $_GET['code'];
        $token_url = 'https://graph.facebook.com/v18.0/oauth/access_token';
        
        $post_data = [
            'client_id' => $facebook_app_id,
            'client_secret' => $GLOBALS['facebook_app_secret'],
            'redirect_uri' => $facebook_redirect_uri,
            'code' => $code
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $token_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $token_data = json_decode($response, true);
        
        if (isset($token_data['access_token'])) {
            // Obtener información del usuario
            $user_info_url = 'https://graph.facebook.com/v18.0/me?fields=id,name,email&access_token=' . $token_data['access_token'];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $user_info_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $user_response = curl_exec($ch);
            curl_close($ch);
            
            $user_data = json_decode($user_response, true);
            
            if ($user_data && isset($user_data['email'])) {
                processSocialLogin($user_data, 'facebook');
            } else {
                header('Location: ../../app/controllers/auth/login.php?error=facebook_error');
                exit;
            }
        } else {
            header('Location: ../../app/controllers/auth/login.php?error=facebook_token_error');
            exit;
        }
    }
}

function processSocialLogin($user_data, $provider) {
    try {
        // Detectar idioma desde GET, referer o sesión
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
        
        // Guardar idioma en sesión
        $_SESSION['user_lang'] = $lang;
        
        $database = new Database();
        $pdo = $database->getConnection();
        
        $email = $user_data['email'];
        $nombre = $user_data['name'] ?? $user_data['first_name'] ?? '';
        $apellido = $user_data['last_name'] ?? '';
        
        // Verificar si el usuario ya existe
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
        $stmt->execute([$email]);
        $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing_user) {
            // Usuario existe, iniciar sesión
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $existing_user['id_usuario'];
            $_SESSION['user_nombre'] = $existing_user['nombre'] . ' ' . $existing_user['apellido'];
            $_SESSION['user_correo'] = $existing_user['correo'];
            $_SESSION['user_rol'] = $existing_user['rol'];
            $_SESSION['login_method'] = $provider;
            
            // Si es admin, establecer también la sesión de admin
            if ($existing_user['rol'] === 'admin') {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $existing_user['id_usuario'];
                $_SESSION['admin_nombre'] = $existing_user['nombre'] . ' ' . $existing_user['apellido'];
                $_SESSION['admin_correo'] = $existing_user['correo'];
                header('Location: ../../app/views/admin/index.php');
            } else {
                header('Location: ../../index.php?lang=' . $lang);
            }
            exit;
        } else {
            // Usuario no existe, crear cuenta
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, correo, rol, login_method) VALUES (?, ?, ?, 'cliente', ?)");
            $stmt->execute([$nombre, $apellido, $email, $provider]);
            
            $user_id = $pdo->lastInsertId();
            
            // Iniciar sesión
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_nombre'] = $nombre . ' ' . $apellido;
            $_SESSION['user_correo'] = $email;
            $_SESSION['user_rol'] = 'cliente';
            $_SESSION['login_method'] = $provider;
            
            header('Location: ../../index.php?lang=' . $lang . '&welcome=1');
            exit;
        }
        
    } catch (Exception $e) {
        error_log("Error en login social: " . $e->getMessage());
        header('Location: ../../app/controllers/auth/login.php?error=social_login_error');
        exit;
    }
}
?>

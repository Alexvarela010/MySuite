<?php
/**
 * Verificación de Código - Sistema de Recuperación de Contraseña
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
$validToken = false;
$user = null;

// Verificar token válido
if ($token) {
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
}

// Procesar verificación de código
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $verificationCode = trim($_POST['verificationCode'] ?? '');
    
    if (empty($verificationCode)) {
        $error = 'Por favor, ingresa el código de verificación';
    } elseif (strlen($verificationCode) !== 6 || !is_numeric($verificationCode)) {
        $error = 'El código debe tener 6 dígitos';
    } else {
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            
            // Verificar el código
            $query = "SELECT * FROM password_reset_tokens 
                      WHERE token = ? AND verification_code = ? AND expires_at > NOW() AND used = FALSE";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$token, $verificationCode]);
            $codeData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($codeData) {
                // Código correcto, redirigir a cambio de contraseña
                header('Location: ../../app/controllers/auth/reset-password.php?token=' . $token . '&verified=1');
                exit;
            } else {
                $error = 'Código incorrecto o expirado';
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
    <title>Verificar Código - My Suite In Cartagena</title>
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
            text-align: center;
            letter-spacing: 3px;
            font-size: 24px;
            font-weight: bold;
        }
        .input-group-ihg input:focus {
            outline: none;
            border-color: rgb(25, 130, 151);
            box-shadow: 0 0 0 3px rgba(25, 130, 151, 0.1);
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
        .code-info {
            background: #f8f9fa;
            border: 2px solid rgb(25, 130, 151);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .code-info h3 {
            color: rgb(25, 130, 151);
            margin-bottom: 10px;
        }
        .code-info p {
            color: #666;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="login-wrapper"> 
        <div class="login-container-ihg">
            <form action="verify-code.php?token=<?php echo htmlspecialchars($token); ?>" method="POST" class="login-form-ihg" id="verifyForm">
                <div class="form-left-panel">
                    <div class="logo-ihg">
                        <h1>My Suite In Cartagena </h1> 
                    </div>
                    <h2>Verificar Código</h2>

                    <?php if ($error): ?>
                        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb; text-align: center;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($validToken): ?>
                        <div class="code-info">
                            <h3>📧 Código Enviado</h3>
                            <p>Hemos enviado un código de 6 dígitos a:</p>
                            <p><strong><?php echo htmlspecialchars($user['correo']); ?></strong></p>
                            <p>Ingresa el código para continuar con la recuperación de tu contraseña.</p>
                        </div>
                        
                        <div class="input-group-ihg">
                            <label for="verificationCode">Código de Verificación</label>
                            <input type="text" id="verificationCode" name="verificationCode" placeholder="000000" required maxlength="6" pattern="[0-9]{6}">
                            <span class="error-message" id="codeError"></span> 
                        </div>
                        
                        <button type="submit" class="login-button-ihg">Verificar Código</button>
                    <?php else: ?>
                        <div class="info-text">
                            Enlace inválido o expirado. Por favor, solicita un nuevo código de recuperación.
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
        // Auto-focus en el input del código
        document.addEventListener('DOMContentLoaded', function() {
            const codeInput = document.getElementById('verificationCode');
            if (codeInput) {
                codeInput.focus();
            }
        });
        
        // Solo permitir números en el input del código
        document.getElementById('verificationCode').addEventListener('input', function(e) {
            // Remover cualquier carácter que no sea número
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Si tiene 6 dígitos, auto-submit
            if (this.value.length === 6) {
                document.getElementById('verifyForm').submit();
            }
        });
        
        // Prevenir pegar texto que no sean números
        document.getElementById('verificationCode').addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const numbers = paste.replace(/[^0-9]/g, '').substring(0, 6);
            this.value = numbers;
            
            if (numbers.length === 6) {
                document.getElementById('verifyForm').submit();
            }
        });

        // Validación del formulario
        document.getElementById('verifyForm').addEventListener('submit', function(e) {
            const code = document.getElementById('verificationCode').value;
            
            if (!code) {
                e.preventDefault();
                document.getElementById('codeError').textContent = 'El código es requerido';
                return;
            }
            
            if (code.length !== 6) {
                e.preventDefault();
                document.getElementById('codeError').textContent = 'El código debe tener 6 dígitos';
                return;
            }
            
            if (!/^[0-9]{6}$/.test(code)) {
                e.preventDefault();
                document.getElementById('codeError').textContent = 'El código debe contener solo números';
                return;
            }
        });
    </script>
</body>
</html>

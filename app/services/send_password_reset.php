<?php
/**
 * Envío de correo de recuperación de contraseña
 * My Suite In Cartagena
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/GmailSender.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        echo json_encode(['success' => false, 'error' => 'Email requerido']);
        exit;
    }
    
    try {
        $database = new Database();
        $pdo = $database->getConnection();
        
        // Verificar si el email existe
        $query = "SELECT id_usuario, nombre, apellido, correo FROM usuarios WHERE correo = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Email no encontrado']);
            exit;
        }
        
        // Generar token único y código de verificación
        $token = bin2hex(random_bytes(32));
        $verificationCode = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', strtotime('+15 minutes')); // Código expira en 15 minutos
        
        // Eliminar tokens anteriores del usuario
        $deleteQuery = "DELETE FROM password_reset_tokens WHERE user_id = ?";
        $deleteStmt = $pdo->prepare($deleteQuery);
        $deleteStmt->execute([$user['id_usuario']]);
        
        // Guardar nuevo token con código
        $insertQuery = "INSERT INTO password_reset_tokens (user_id, token, verification_code, expires_at) VALUES (?, ?, ?, ?)";
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute([$user['id_usuario'], $token, $verificationCode, $expires]);
        
        // Crear enlace de recuperación
        $resetLink = "http://localhost/en/verify-code.php?token=" . $token;
        
        // Configurar el correo
        $gmailSender = new GmailSender();
        
        $to = $user['correo'];
        $subject = "Recuperación de Contraseña - My Suite In Cartagena";
        
        $htmlContent = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, rgb(25, 130, 151), rgb(20, 110, 130)); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: linear-gradient(135deg, rgb(25, 130, 151), rgb(20, 110, 130)); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
                .code-box { background: white; border: 3px solid rgb(25, 130, 151); border-radius: 10px; padding: 20px; text-align: center; margin: 20px 0; }
                .verification-code { font-size: 32px; font-weight: bold; color: rgb(25, 130, 151); letter-spacing: 5px; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🏖️ My Suite In Cartagena</h1>
                    <h2>Código de Verificación</h2>
                </div>
                <div class='content'>
                    <h3>Hola " . htmlspecialchars($user['nombre']) . ",</h3>
                    <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en My Suite In Cartagena.</p>
                    <p>Para continuar, ingresa el siguiente código de verificación:</p>
                    
                    <div class='code-box'>
                        <div class='verification-code'>" . $verificationCode . "</div>
                    </div>
                    
                    <p><strong>Este código expirará en 15 minutos por seguridad.</strong></p>
                    <p>Si no solicitaste este cambio, puedes ignorar este correo. Tu contraseña actual seguirá siendo válida.</p>
                    <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>
                    <p><strong>¿No funciona el enlace?</strong><br>
                    Copia y pega este enlace en tu navegador:<br>
                    <a href='" . $resetLink . "' style='color: rgb(25, 130, 151);'>" . $resetLink . "</a></p>
                </div>
                <div class='footer'>
                    <p>Este correo fue enviado automáticamente. Por favor, no respondas a este mensaje.</p>
                    <p>© 2025 My Suite In Cartagena. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>";
        
        // Enviar el correo
        $result = $gmailSender->sendEmail($to, $subject, $htmlContent, true, null, false); // false = sin CC a gerencia
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Correo enviado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al enviar el correo']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error del sistema: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
?>

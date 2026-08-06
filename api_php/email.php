<?php
// email.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

function sendReservationEmail($to, $name, $date, $serviceName, $id, $meetLink = null) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP (cPanel)
        $mail->isSMTP();
        $mail->Host       = 'mail.mysuiteincartagena.com.co';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'reservasvivieros@mysuiteincartagena.com.co';
        $mail->Password   = 'MyS2.025InCartagena'; // Pon aquí la clave real de este correo
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SMTPS es para el puerto 465
        $mail->Port       = 465;
        
        // Configuracion del remitente
        $mail->setFrom('reservasvivieros@mysuiteincartagena.com.co', 'Reservas Consultoría');
        
        // --------------------------------------------------------
        // 1. PREPARAR EL CONTENIDO DEL CORREO
        // --------------------------------------------------------
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        
        $meetInfo = $meetLink ? "<p><strong>Enlace de reunión:</strong> <a href=\"$meetLink\">$meetLink</a></p>" : "";
        $cancelUrl = "https://mysuiteincartagena.com.co/reservas/cancel?id=$id";
        
        $bodyContent = "
        <html>
        <head>
          <title>Confirmación de Reserva</title>
        </head>
        <body style='font-family: Arial, sans-serif; color: #333;'>
          <h2>Hola $name,</h2>
          <p>La reserva ha sido confirmada con éxito.</p>
          <div style='background-color: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>
              <p style='margin-bottom: 5px;'><strong>Servicio:</strong> $serviceName</p>
              <p style='margin-bottom: 5px;'><strong>Fecha y Hora:</strong> $date</p>
              $meetInfo
          </div>
          <p>Si necesitas cancelar la cita, por favor haz clic en el siguiente enlace:</p>
          <p><a href=\"$cancelUrl\" style='color: #ef4444; text-decoration: underline;'>Cancelar mi Reserva</a></p>
          <br>
          <p>¡Gracias por tu preferencia!</p>
        </body>
        </html>
        ";
        
        $mail->Body = $bodyContent;

        // --------------------------------------------------------
        // 2. ENVIAR CORREO AL CLIENTE
        // --------------------------------------------------------
        $mail->addAddress($to, $name);
        $mail->Subject = "Confirmación de Reserva - $serviceName";
        $mail->send(); // Se envía al cliente
        
        // --------------------------------------------------------
        // 3. ENVIAR AVISO A ADMINISTRACIÓN
        // --------------------------------------------------------
        $mail->clearAllRecipients(); // Limpiamos el destinatario anterior
        $mail->addAddress('reservasvivieros@mysuiteincartagena.com.co', 'Administración Reservas');
        $mail->Subject = "NUEVA RESERVA (Copia) - $serviceName ($name)";
        $mail->send(); // Se envía a ti
        
        return true;
    } catch (Exception $e) {
        // Log error si es necesario: echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}
?>

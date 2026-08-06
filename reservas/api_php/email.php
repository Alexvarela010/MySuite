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
        // Configuración del servidor SMTP (DreamHost)
        $mail->isSMTP();
        $mail->Host       = 'smtp.dreamhost.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'test@advantascience.com'; // Tu correo de DreamHost
        $mail->Password   = 'JTT-sq16cy21'; // Tu contraseña de DreamHost
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Configuracion de correos
        $mail->setFrom('test@advantascience.com', 'Reservas C. Viveros');
        $mail->addAddress($to, $name);
        $mail->addBCC('reservasviveros@mysuiteincartagena.com.co', 'Administración Reservas');
        $mail->addBCC('caviverosv@gmail.com', 'Administración Reservas');
        
        // Contenido del correo
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Confirmación de Reserva - $serviceName";
        
        $meetInfo = $meetLink ? "<p><strong>Enlace de reunión:</strong> <a href=\"$meetLink\">$meetLink</a></p>" : "";
        // URL de cancelación en producción
        $cancelUrl = "https://mysuiteincartagena.com.co/reservas/cancel?id=$id"; 
        
        $mail->Body    = "
        <html>
        <head>
          <title>Confirmación de Reserva</title>
        </head>
        <body style='font-family: Arial, sans-serif; color: #333;'>
          <h2>Hola $name,</h2>
          <p>Tu reserva ha sido confirmada con éxito.</p>
          <div style='background-color: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>
              <p style='margin-bottom: 5px;'><strong>Servicio:</strong> $serviceName</p>
              <p style='margin-bottom: 5px;'><strong>Fecha y Hora:</strong> $date</p>
              $meetInfo
          </div>
          <p>Si necesitas cancelar tu cita, por favor haz clic en el siguiente enlace:</p>
          <p><a href=\"$cancelUrl\" style='color: #ef4444; text-decoration: underline;'>Cancelar mi Reserva</a></p>
          <br>
          <p>¡Gracias por tu preferencia!</p>
        </body>
        </html>
        ";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log error si es necesario: echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}
?>

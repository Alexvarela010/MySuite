<?php
/**
 * Script para enviar recordatorios de saldo pendiente (80%) un día antes de la fecha de entrada.
 * Debe ser ejecutado como una tarea programada (cron job) diariamente.
 */

// Incluir archivos necesarios
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/GmailSender.php';

// Configuración de log
ini_set('log_errors', 'On');
ini_set('error_log', __DIR__ . '/../../logs/cron_saldo_reminders.log');

echo "Iniciando script de recordatorios de saldo...\n";
error_log("Iniciando script de recordatorios de saldo...");

try {
    $db = (new Database())->getConnection();
    $gmail = new GmailSender();

    // Calcular la fecha de entrada para mañana
    $tomorrow = (new DateTime())->modify('+1 day')->format('Y-m-d');

    echo "Buscando reservas con fecha_entrada: $tomorrow y estado 'abonada'...\n";
    error_log("Buscando reservas con fecha_entrada: $tomorrow y estado 'abonada'...");

    // Consulta para obtener reservas que cumplen los criterios
    // Asumiendo que existe una columna `saldo_email_sent_date` para evitar reenvíos
    $query = "SELECT r.*, 
                     u.id_usuario AS usuario_id,
                     u.nombre AS usuario_nombre,
                     u.apellido AS usuario_apellido,
                     u.telefono AS usuario_telefono,
                     u.correo AS usuario_correo
              FROM reservas r 
              LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario 
              WHERE r.estado = 'abonada' 
                AND r.fecha_entrada = ?
                AND (r.saldo_email_sent_date IS NULL OR r.saldo_email_sent_date = '0000-00-00 00:00:00')"; // Solo si no se ha enviado antes

    $stmt = $db->prepare($query);
    $stmt->execute([$tomorrow]);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($reservas)) {
        echo "No se encontraron reservas para enviar recordatorios hoy.\n";
        error_log("No se encontraron reservas para enviar recordatorios hoy.");
    } else {
        echo "Se encontraron " . count($reservas) . " reservas para procesar.\n";
        error_log("Se encontraron " . count($reservas) . " reservas para procesar.");

        foreach ($reservas as $reserva) {
            $reserva_id = $reserva['id_reserva'];
            $correo_cliente = !empty($reserva['correo']) ? $reserva['correo'] : ($reserva['usuario_correo'] ?? null);

            if (empty($correo_cliente) || !filter_var($correo_cliente, FILTER_VALIDATE_EMAIL)) {
                echo "Error: Email de cliente no válido para reserva #$reserva_id. Saltando.\n";
                error_log("Error: Email de cliente no válido para reserva #$reserva_id. Saltando.");
                continue;
            }

            $metodo = strtolower(trim($reserva['metodo_pago'] ?? 'transferencia'));
            $sent = false;

            echo "Procesando reserva #$reserva_id (método: $metodo, cliente: $correo_cliente)...\n";
            error_log("Procesando reserva #$reserva_id (método: $metodo, cliente: $correo_cliente)...");

            if ($metodo === 'tarjeta' || $metodo === 'card' || $metodo === 'tarjeta_credito') {
                $sent = $gmail->sendSaldoTarjeta80($reserva, false);
            } else {
                // Por defecto tratar como transferencia/efectivo
                $sent = $gmail->sendSaldoTransferencia80($reserva, false);
            }

            if ($sent) {
                echo "Email de saldo 80% enviado exitosamente a: $correo_cliente para reserva #$reserva_id.\n";
                error_log("Email de saldo 80% enviado exitosamente a: $correo_cliente para reserva #$reserva_id.");

                // Actualizar la reserva para marcar que el email de saldo ha sido enviado
                $update_query = "UPDATE reservas SET saldo_email_sent_date = NOW() WHERE id_reserva = ?";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->execute([$reserva_id]);
                echo "Reserva #$reserva_id marcada como 'saldo_email_sent_date' actualizado.\n";
                error_log("Reserva #$reserva_id marcada como 'saldo_email_sent_date' actualizado.");

            } else {
                echo "Error enviando email de saldo 80% a: $correo_cliente para reserva #$reserva_id.\n";
                error_log("Error enviando email de saldo 80% a: $correo_cliente para reserva #$reserva_id.");
            }
        }
    }

} catch (Exception $e) {
    echo "Error fatal en el script de recordatorios de saldo: " . $e->getMessage() . "\n";
    error_log("Error fatal en el script de recordatorios de saldo: " . $e->getMessage());
} catch (Error $e) {
    echo "Error fatal en el script de recordatorios de saldo: " . $e->getMessage() . "\n";
    error_log("Error fatal en el script de recordatorios de saldo: " . $e->getMessage());
}

echo "Script de recordatorios de saldo finalizado.\n";
error_log("Script de recordatorios de saldo finalizado.");

?>
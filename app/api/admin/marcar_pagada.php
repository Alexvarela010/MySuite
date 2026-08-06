<?php
/**
 * Marcar Reserva como Pagada sin Comprobante
 * Sistema de Reservas - My Suite In Cartagena
 */

session_start();
/**
 * Enviar email de confirmación cuando el pago queda completamente confirmado (reserva PAGADA)
 */
function enviarEmailPagoConfirmado($reserva) {
    try {
        if (!class_exists('GmailSender')) {
            error_log('Error: GmailSender no disponible para enviar confirmación de pago');
            return false;
        }

        $gmail = new GmailSender();
        $hotel_image_path = $gmail->getHotelImagePath();
        $destinatario = $reserva['correo'] ?? ($reserva['usuario_correo'] ?? null);
        if (empty($destinatario) || !filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
            error_log('Email de cliente no válido para reserva: ' . ($reserva['id_reserva'] ?? 'N/A'));
            return false;
        }

        $asunto = "✅ Pago Confirmado - My Suite In Cartagena #" . ($reserva['id_reserva'] ?? 'N/A');

        // Preparar datos legibles
        $nombre_huesped = trim(($reserva['usuario_nombre'] ?? '') . ' ' . ($reserva['usuario_apellido'] ?? ''));
        if (empty($nombre_huesped)) {
            $nombre_huesped = trim($reserva['nombre'] ?? ($reserva['nombre_huesped'] ?? ''));
        }
        $nombre_huesped_html = $nombre_huesped !== '' ? '<p><strong>Huésped:</strong> ' . htmlspecialchars($nombre_huesped) . '</p>' : '';

        $fecha_entrada = !empty($reserva['fecha_entrada']) ? date('d/m/Y', strtotime($reserva['fecha_entrada'])) : 'N/A';
        $fecha_salida = !empty($reserva['fecha_salida']) ? date('d/m/Y', strtotime($reserva['fecha_salida'])) : 'N/A';
        $adultos = (int)($reserva['num_adultos'] ?? 0);
        $ninos = (int)($reserva['num_ninos'] ?? 0);
        $personas = $adultos + $ninos;
        $metodo_raw = (string)($reserva['metodo_pago'] ?? 'Transferencia');
        $metodo = ucwords(str_replace('_', ' ', strtolower($metodo_raw)));
        $total = isset($reserva['total']) ? (float)$reserva['total'] : (isset($reserva['precio_total']) ? (float)$reserva['precio_total'] : 0);
        $total_text = $total > 0 ? number_format($total, 0, ',', '.') : 'N/A';
        $reserva_ref = htmlspecialchars((string)($reserva['id_reserva'] ?? 'N/A'));
        $carta_texto = "Cordial saludo\n"
            . "Señor(a) {$nombre_huesped}\n"
            . "Nos complace informarle que el pago de su reserva fue confirmado exitosamente.\n"
            . "Referencia de reserva: #{$reserva_ref}\n"
            . "Fechas de estadia: {$fecha_entrada} - {$fecha_salida}\n"
            . "Cantidad de personas: {$personas} ({$adultos} adultos, {$ninos} ninos)\n"
            . "Metodo de pago: {$metodo}\n"
            . "Total pagado: $ " . $total_text . " COP\n"
            . "Su reserva ya quedo en estado PAGADA y confirmada en nuestro sistema.\n"
            . "Si requiere asistencia adicional o desea compartir informacion de horarios de vuelo,\n"
            . "puede responder a este correo y con gusto le apoyaremos.\n"
            . "Cordialmente,\n"
            . "Andrés Diaz\n"
            . "Soporte My Suite In Cartagena";
        $carta_texto_html = nl2br(htmlspecialchars($carta_texto));

        $message = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 700px; margin: 0 auto; background: #ffffff; padding: 20px; }
                .header { text-align: right; margin-bottom: 30px; }
                .logo { width: 80px; height: auto; }
                .letter { white-space: pre-wrap; font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.8; color: #333; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='cid:hotel_logo' alt='My Suite In Cartagena' class='logo'>
                </div>
                <div class='letter'>{$carta_texto_html}</div>
            </div>
        </body>
        </html>
        HTML;

        // Enviar el email con la imagen del hotel embebida
        return $gmail->sendEmail($destinatario, $asunto, $message, true, $hotel_image_path, true);

    } catch (Exception $e) {
        error_log('Error enviando email de confirmación de pago: ' . $e->getMessage());
        return false;
    } catch (Error $e) {
        error_log('Error fatal enviando email de confirmación de pago: ' . $e->getMessage());
        return false;
    }
}


// Verificar si el usuario está logueado como admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/GmailSender.php';

header('Content-Type: application/json');

/**
 * Notificación interna a Evelyn cuando una reserva queda pagada
 */
function enviarNotificacionPagoAEvelyn($reserva) {
    try {
        if (!class_exists('GmailSender')) {
            error_log('Error: GmailSender no disponible para notificación interna');
            return false;
        }

        $gmail = new GmailSender();
        $hotel_image_path = $gmail->getHotelImagePath();
        $destinatario = 'mysuiteincartagena@gmail.com';
        $subject = '✅ Reserva Pagada - My Suite In Cartagena #' . $reserva['id_reserva'];

        $nombre_huesped = '';
        if (!empty($reserva['usuario_id'])) {
            $nombre_huesped = trim(($reserva['usuario_nombre'] ?? '') . ' ' . ($reserva['usuario_apellido'] ?? ''));
        }

        $whatsapp = trim($reserva['usuario_telefono'] ?? $reserva['telefono'] ?? '');
        $cantidad_adultos = (int)($reserva['num_adultos'] ?? 0);
        $cantidad_ninos = (int)($reserva['num_ninos'] ?? 0);
        $cantidad_personas = $cantidad_adultos + $cantidad_ninos;
        $fecha_entrada = date('d/m/Y', strtotime($reserva['fecha_entrada']));
        $fecha_salida = date('d/m/Y', strtotime($reserva['fecha_salida']));

        $nombre_huesped_html = '';
        if ($nombre_huesped !== '') {
            $nombre_huesped_html = '<p><strong>Huésped registrado:</strong> ' . htmlspecialchars($nombre_huesped) . '</p>';
        }

        $whatsapp_html = !empty($whatsapp) ? htmlspecialchars($whatsapp) : 'No disponible';

        $message = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
                .header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 30px 20px; text-align: center; }
                .content { padding: 30px; background: #f8f9fa; }
                .info-box { background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #2196f3; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #6c757d; font-size: 14px; }
                h1, h2, h3 { margin: 0 0 15px 0; }
                p { margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1 style='text-align: center; margin: 0 0 15px 0; line-height: 50px;'>
                        <img src='cid:hotel_logo' alt='My Suite In Cartagena' style='width: 50px; height: 50px; vertical-align: middle; margin-right: 10px; display: inline-block;' />
                        <span style='vertical-align: middle; display: inline-block;'>My Suite In Cartagena</span>
                    </h1>
                    <h2>✅ Reserva Pagada</h2>
                </div>
                <div class='content'>
                    <p>Hola Evelyn,</p>
                    <p>Se confirmó el pago de una reserva. Por favor revisar la siguiente información:</p>

                    <div class='info-box'>
                        <h3>📋 Datos de la Reserva</h3>
                        <p><strong>ID Reserva:</strong> #{$reserva['id_reserva']}</p>
                        {$nombre_huesped_html}
                        <p><strong>WhatsApp:</strong> {$whatsapp_html}</p>
                        <p><strong>Cantidad de personas:</strong> {$cantidad_personas} ({$cantidad_adultos} adultos, {$cantidad_ninos} niños)</p>
                        <p><strong>Fecha de ingreso:</strong> {$fecha_entrada}</p>
                        <p><strong>Fecha de salida:</strong> {$fecha_salida}</p>
                    </div>

                    <p>La reserva ya quedó en estado <strong>PAGADA</strong>.</p>
                </div>
                <div class='footer'>
                    <p>Saludos cordiales,<br><strong>My Suite In Cartagena</strong></p>
                </div>
            </div>
        </body>
        </html>
        HTML;

        return $gmail->sendEmail($destinatario, $subject, $message, true, $hotel_image_path, true);
    } catch (Exception $e) {
        error_log('Error enviando notificación a Evelyn: ' . $e->getMessage());
        return false;
    } catch (Error $e) {
        error_log('Error fatal enviando notificación a Evelyn: ' . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reserva_id = $_POST['reserva_id'] ?? '';
    $notas = $_POST['notas'] ?? '';
    $marcar_pagada = $_POST['marcar_pagada'] ?? '';
    
    if (empty($reserva_id)) {
        echo json_encode(['success' => false, 'message' => 'ID de reserva no proporcionado']);
        exit;
    }
    
    if ($marcar_pagada !== '1') {
        echo json_encode(['success' => false, 'message' => 'Parámetro inválido']);
        exit;
    }
    
    try {
        $db = (new Database())->getConnection();
        
        // Obtener el estado actual de la reserva
        $query_estado = "SELECT estado, estado_pago FROM reservas WHERE id_reserva = ?";
        $stmt_estado = $db->prepare($query_estado);
        $stmt_estado->execute([$reserva_id]);
        $reserva_actual = $stmt_estado->fetch(PDO::FETCH_ASSOC);
        
        if (!$reserva_actual) {
            echo json_encode(['success' => false, 'message' => 'Reserva no encontrada']);
            exit;
        }
        
        // Normalizar estados para comparación
        $estado_actual = strtolower(trim($reserva_actual['estado'] ?? ''));
        $estado_pago_actual = strtolower(trim($reserva_actual['estado_pago'] ?? ''));
        
        // Log para debugging
        error_log("Marcar pagada - Reserva ID: $reserva_id, Estado: {$reserva_actual['estado']}, Estado Pago: {$reserva_actual['estado_pago']}");
        error_log("Normalizado - Estado: $estado_actual, Estado Pago: $estado_pago_actual");
        
        // Lógica del flujo:
        // - Si está aprobada → cambiar a abonada
        // - Si está abonada → cambiar a aprobada y marcar como pagada
        if ($estado_actual === 'aprobada' && $estado_pago_actual === 'pendiente') {
            // Aprobada → Abonada
            $query = "UPDATE reservas SET 
                        estado = 'abonada',
                        comentario = CONCAT(IFNULL(comentario, ''), '\n--- MARCADA COMO ABONADA ---\n', ?)
                      WHERE id_reserva = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([
                $notas ? "Notas: " . $notas : "Marcada como abonada",
                $reserva_id
            ]);
        } elseif ($estado_actual === 'abonada') {
            // Abonada → Pagada (cambiar a aprobada y marcar como pagada)
            $query = "UPDATE reservas SET 
                        estado = 'aprobada',
                        estado_pago = 'pagada',
                        fecha_pago_confirmado = NOW(),
                        comentario = CONCAT(IFNULL(comentario, ''), '\n--- PAGO CONFIRMADO ---\n', ?)
                      WHERE id_reserva = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([
                $notas ? "Notas del pago: " . $notas : "Pago confirmado por el administrador",
                $reserva_id
            ]);
        } else {
            // Para otros casos, solo marcar como pagada (compatibilidad)
            $query = "UPDATE reservas SET 
                        estado_pago = 'pagada',
                        fecha_pago_confirmado = NOW(),
                        comentario = CONCAT(IFNULL(comentario, ''), '\n--- PAGO CONFIRMADO ---\n', ?)
                      WHERE id_reserva = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([
                $notas ? "Notas del pago: " . $notas : "Pago confirmado por el administrador",
                $reserva_id
            ]);
        }
        
        if ($stmt->rowCount() > 0) {
            // Determinar el mensaje según el cambio realizado
            if ($estado_actual === 'aprobada' && $estado_pago_actual === 'pendiente') {
                // Se cambió de aprobada a abonada - NO ENVIAR CORREO DE SALDO AQUÍ
                $mensaje_respuesta = 'Reserva marcada como ABONADA exitosamente.';
                error_log("Reserva $reserva_id cambiada de aprobada a abonada");
            } elseif ($estado_actual === 'abonada') {
                // Se cambió de abonada a pagada - enviar correo
                // Obtener datos de la reserva para enviar el correo
                $query_reserva = "SELECT r.*, 
                                u.id_usuario AS usuario_id,
                                u.nombre AS usuario_nombre,
                                u.apellido AS usuario_apellido,
                                u.telefono AS usuario_telefono,
                                u.correo AS usuario_correo
                                FROM reservas r 
                                LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario 
                                WHERE r.id_reserva = ?";
                $stmt_reserva = $db->prepare($query_reserva);
                $stmt_reserva->execute([$reserva_id]);
                $reserva = $stmt_reserva->fetch(PDO::FETCH_ASSOC);
                
                // Usar el correo de la reserva (r.correo) que siempre existe
                $correo_cliente = !empty($reserva['correo']) ? $reserva['correo'] : null;
                
                // Log para debugging
                error_log("Intentando enviar correo de confirmación de pago. Reserva ID: " . $reserva_id);
                error_log("Correo cliente: " . ($correo_cliente ?? 'NO ENCONTRADO'));
                
                if ($reserva && $correo_cliente && filter_var($correo_cliente, FILTER_VALIDATE_EMAIL)) {
                    // Enviar correo de confirmación de pago
                    try {
                        $gmail = new GmailSender();

                        // Enviar el email final de CONFIRMACIÓN (reserva PAGADA) usando la nueva plantilla
                        $sent = enviarEmailPagoConfirmado($reserva);

                        $notificacion_evelyn = enviarNotificacionPagoAEvelyn($reserva);

                        if ($sent) {
                            error_log("Email de confirmación (plantilla) enviado a: " . $correo_cliente . " para reserva #" . $reserva_id);
                            if ($notificacion_evelyn) {
                                error_log("Notificación interna enviada a Evelyn para reserva #" . $reserva_id);
                            }
                            $mensaje_respuesta = 'Reserva marcada como PAGADA exitosamente y correo de confirmación enviado';
                        } else {
                            error_log("Error enviando email de confirmación (plantilla) a: " . $correo_cliente);
                            $mensaje_respuesta = 'Reserva marcada como PAGADA exitosamente (error al enviar correo)';
                        }

                    } catch (Exception $e) {
                        error_log("Error enviando correo de confirmación de pago: " . $e->getMessage());
                        $mensaje_respuesta = 'Reserva marcada como PAGADA exitosamente (error al enviar correo)';
                    }
                } else {
                    $mensaje_respuesta = 'Reserva marcada como PAGADA exitosamente (correo no disponible)';
                }
            } else {
                $mensaje_respuesta = 'Reserva actualizada exitosamente';
            }
            
            echo json_encode([
                'success' => true, 
                'message' => $mensaje_respuesta
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'No se pudo actualizar la reserva'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Error de base de datos: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
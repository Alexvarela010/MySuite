<?php
/**
 * Funciones del Sistema de Reservas
 * My Suite In Cartagena
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/GmailSender.php';

/**
 * Obtener URLs iCal configuradas por apartamento.
 */
function getIcalFeedUrls($apartamento_id = 1) {
    $configPath = __DIR__ . '/../config/ical_feeds.php';

    if (!file_exists($configPath)) {
        return [];
    }

    $config = require $configPath;
    if (!is_array($config)) {
        return [];
    }

    $feeds = $config[$apartamento_id] ?? $config[(string)$apartamento_id] ?? [];
    if (!is_array($feeds)) {
        return [];
    }

    return array_values(array_filter($feeds, function ($url) {
        return is_string($url) && filter_var($url, FILTER_VALIDATE_URL);
    }));
}

/**
 * Obtener fechas ocupadas desde feeds iCal externos (Airbnb/Booking).
 */
function getFechasOcupadasIcal($apartamento_id = 1, $fechaInicio = null, $fechaFin = null) {
    $feedUrls = getIcalFeedUrls($apartamento_id);
    if (empty($feedUrls)) {
        return [];
    }

    require_once __DIR__ . '/CalendarIntegrator.php';

    $integrator = new CalendarIntegrator();
    $occupiedDates = [];

    $fechaInicioStr = $fechaInicio instanceof DateTimeInterface ? $fechaInicio->format('Y-m-d') : null;
    $fechaFinStr = $fechaFin instanceof DateTimeInterface ? $fechaFin->format('Y-m-d') : null;

    foreach ($feedUrls as $url) {
        try {
            $icalContent = $integrator->fetchIcalUrl($url);
            if (!$icalContent) {
                continue;
            }

            $events = $integrator->parseIcalData($icalContent);
            $dates = $integrator->getOccupiedDates($events);

            if ($fechaInicioStr || $fechaFinStr) {
                $dates = array_values(array_filter($dates, function ($date) use ($fechaInicioStr, $fechaFinStr) {
                    if ($fechaInicioStr && $date < $fechaInicioStr) {
                        return false;
                    }
                    if ($fechaFinStr && $date > $fechaFinStr) {
                        return false;
                    }
                    return true;
                }));
            }

            $occupiedDates = array_merge($occupiedDates, $dates);
        } catch (Exception $e) {
            error_log('Error al procesar feed iCal (' . $url . '): ' . $e->getMessage());
        }
    }

    $occupiedDates = array_unique($occupiedDates);
    sort($occupiedDates);

    return $occupiedDates;
}

/**
 * Obtener fechas ocupadas para el calendario (reservas + fechas bloqueadas)
 *
 * REGLA check-in 15:00 / check-out 11:00:
 * Solo se bloquean los días INTERMEDIOS de cada reserva.
 * Ni el día de check-in ni el de check-out se bloquean,
 * porque ambos pueden ser el extremo opuesto de otra reserva.
 *
 * Ejemplo:
 *   Reserva A: 10 mayo → 13 mayo
 *   Bloquea: 11, 12
 *   Libre: 10 ← puede ser check-OUT de Reserva C (7→10)
 *   Libre: 13 ← puede ser check-IN  de Reserva B (13→15)
 */
function getFechasOcupadas($apartamento_id = 1) {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        return [];
    }

    try {
        $fechas_ocupadas = [];

        // 1. Reservas aprobadas / abonadas
        $query_reservas = "
            SELECT DISTINCT
                DATE_FORMAT(fecha_entrada, '%Y-%m-%d') as fecha_entrada,
                DATE_FORMAT(fecha_salida,  '%Y-%m-%d') as fecha_salida
            FROM reservas
            WHERE id_apartamento = :apartamento_id
              AND (estado = 'aprobada' OR estado = 'abonada')
              AND fecha_salida >= CURDATE()
        ";

        $stmt = $db->prepare($query_reservas);
        $stmt->bindParam(':apartamento_id', $apartamento_id);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fecha_entrada_str = trim($row['fecha_entrada']);
            $fecha_salida_str  = trim($row['fecha_salida']);

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_entrada_str) ||
                !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_salida_str)) {
                error_log("Formato de fecha inválido en getFechasOcupadas: entrada=$fecha_entrada_str, salida=$fecha_salida_str");
                continue;
            }

            // Empezar desde el día SIGUIENTE al check-in
            $current = new DateTime($fecha_entrada_str);
            $current->modify('+1 day');

            // Terminar en el día ANTERIOR al check-out (< exclusivo)
            $end = new DateTime($fecha_salida_str);

            // Solo días intermedios: ni check-in ni check-out se bloquean
            while ($current < $end) {
                $fechas_ocupadas[] = $current->format('Y-m-d');
                $current->modify('+1 day');
            }
        }

        // 2. Fechas bloqueadas manualmente (bloqueo total, ambos extremos incluidos)
        $query_bloqueadas = "
            SELECT fecha_inicio, fecha_fin
            FROM fechas_bloqueadas
            WHERE activo = 1
              AND fecha_fin >= CURDATE()
        ";

        $stmt = $db->prepare($query_bloqueadas);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $inicio = new DateTime($row['fecha_inicio']);
            $fin    = new DateTime($row['fecha_fin']);
            $fin->modify('+1 day'); // inclusivo en ambos extremos

            while ($inicio < $fin) {
                $fechas_ocupadas[] = $inicio->format('Y-m-d');
                $inicio->modify('+1 day');
            }
        }


        $fechas_ocupadas = array_unique($fechas_ocupadas);
        sort($fechas_ocupadas);

        return $fechas_ocupadas;

    } catch (Exception $e) {
        error_log("Error en getFechasOcupadas: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener días de check-in y check-out de reservas existentes.
 * Sirve para mostrarlos en el calendario con estilo diferente:
 * - checkoutDates: día libre por la mañana (salida 11:00), disponible para nuevo check-in (15:00)
 * - checkinDates:  día libre por la mañana si viene de otro check-out, ocupado desde las 15:00
 *
 * Un mismo día puede estar en AMBOS arrays si es check-out de una reserva
 * y check-in de otra (reservas consecutivas).
 */
function getCheckinCheckoutDates($apartamento_id = 1) {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        return ['checkin' => [], 'checkout' => []];
    }

    try {
        $query = "
            SELECT DISTINCT
                DATE_FORMAT(fecha_entrada, '%Y-%m-%d') as fecha_entrada,
                DATE_FORMAT(fecha_salida,  '%Y-%m-%d') as fecha_salida
            FROM reservas
            WHERE id_apartamento = :apartamento_id
              AND (estado = 'aprobada' OR estado = 'abonada')
              AND fecha_salida >= CURDATE()
        ";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':apartamento_id', $apartamento_id);
        $stmt->execute();

        $checkin_dates  = [];
        $checkout_dates = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $checkin_dates[]  = trim($row['fecha_entrada']);
            $checkout_dates[] = trim($row['fecha_salida']);
        }

        return [
            'checkin'  => array_values(array_unique($checkin_dates)),
            'checkout' => array_values(array_unique($checkout_dates)),
        ];

    } catch (Exception $e) {
        error_log("Error en getCheckinCheckoutDates: " . $e->getMessage());
        return ['checkin' => [], 'checkout' => []];
    }
}

/**
 * Verificar si un rango está disponible en la base de datos.
 *
 * Dos reservas se SOLAPAN si:
 *   existing.fecha_entrada < nueva.fecha_salida
 *   AND existing.fecha_salida > nueva.fecha_entrada
 *
 * Si nueva_entrada == existing_salida (mismo día, check-in = check-out anterior)
 * la condición fecha_salida > nueva_entrada es FALSA → no hay solapamiento. ✅
 *
 * @param string   $fecha_entrada_nueva  'Y-m-d'
 * @param string   $fecha_salida_nueva   'Y-m-d'
 * @param int      $apartamento_id
 * @param int|null $excluir_reserva_id   Para ediciones: ignorar la propia reserva
 * @return bool    true = disponible, false = ocupado
 */
function isRangoDisponible($fecha_entrada_nueva, $fecha_salida_nueva, $apartamento_id = 1, $excluir_reserva_id = null) {
    $database = new Database();
    $db = $database->getConnection();
    if (!$db) return false;

    try {
        $sql = "
            SELECT COUNT(*)
            FROM reservas
            WHERE id_apartamento = :apartamento_id
              AND estado IN ('aprobada', 'abonada', 'pendiente')
              AND fecha_entrada < :fecha_salida_nueva
              AND fecha_salida  > :fecha_entrada_nueva
        ";

        $params = [
            ':apartamento_id'      => $apartamento_id,
            ':fecha_entrada_nueva' => $fecha_entrada_nueva,
            ':fecha_salida_nueva'  => $fecha_salida_nueva,
        ];

        if ($excluir_reserva_id) {
            $sql .= " AND id_reserva != :excluir_id";
            $params[':excluir_id'] = $excluir_reserva_id;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn() === 0;

    } catch (Exception $e) {
        error_log("Error en isRangoDisponible: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtener tarifa por fecha
 */
function getTarifaPorFecha($fecha, $apartamento_id = 1) {
    $database = new Database();
    $db = $database->getConnection();
    
    // Si no hay conexión, retornar precio base
    if (!$db) {
        return 200000;
    }
    
    try {
        $query = "
            SELECT precio 
            FROM tarifas 
            WHERE id_apartamento = :apartamento_id 
            AND fecha = :fecha
        ";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':apartamento_id', $apartamento_id);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['precio'] : 200000;
    } catch (Exception $e) {
        error_log("Error en getTarifaPorFecha: " . $e->getMessage());
        return 200000;
    }
}

/**
 * Guardar reserva en la base de datos
 * Versión simplificada y robusta
 */
function guardarReserva($datos) {
    // Validar que los datos requeridos estén presentes
    $campos_requeridos = ['nombre', 'apellido', 'correo', 'telefono', 'fecha_entrada', 'fecha_salida', 'num_adultos', 'metodo_pago', 'total'];
    foreach ($campos_requeridos as $campo) {
        if (!isset($datos[$campo])) {
            error_log("Error: Campo requerido '$campo' no está presente en guardarReserva");
            return false;
        }
    }
    
    // Crear conexión a la base de datos
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        error_log("Error: No se pudo conectar a la base de datos en guardarReserva");
        return false;
    }
    
    try {
        // Iniciar transacción
        $db->beginTransaction();
        
        // Preparar query de inserción
        $query = "INSERT INTO reservas (
            id_apartamento, 
            id_usuario, 
            nombre, 
            apellido, 
            correo, 
            telefono,
            fecha_nacimiento, 
            fecha_entrada, 
            fecha_salida, 
            num_adultos, 
            num_ninos,
            vive_palmira, 
            metodo_pago, 
            costo_base, 
            descuento_fidelizacion, 
            descuento_cumpleanios,
            descuento_promocional, 
            total, 
            estado
        ) VALUES (
            :id_apartamento, 
            :id_usuario, 
            :nombre, 
            :apellido, 
            :correo, 
            :telefono,
            :fecha_nacimiento, 
            :fecha_entrada, 
            :fecha_salida, 
            :num_adultos, 
            :num_ninos,
            :vive_palmira, 
            :metodo_pago, 
            :costo_base, 
            :descuento_fidelizacion, 
            :descuento_cumpleanios,
            :descuento_promocional, 
            :total, 
            'pendiente'
        )";
        
        $stmt = $db->prepare($query);
        
        // Bind de parámetros
        $id_apartamento = isset($datos['id_apartamento']) ? (int)$datos['id_apartamento'] : 1;
        $id_usuario = isset($datos['id_usuario']) && !empty($datos['id_usuario']) ? (int)$datos['id_usuario'] : null;
        $nombre = trim($datos['nombre']);
        $apellido = trim($datos['apellido']);
        $correo = trim($datos['correo']);
        $telefono = trim($datos['telefono']);
        $fecha_nacimiento = isset($datos['fecha_nacimiento']) && !empty($datos['fecha_nacimiento']) ? $datos['fecha_nacimiento'] : null;
        
        // Validar y normalizar fechas para evitar problemas de zona horaria
        $fecha_entrada = trim($datos['fecha_entrada']);
        $fecha_salida = trim($datos['fecha_salida']);
        
        // Validar formato de fecha (debe ser Y-m-d)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_entrada) || 
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_salida)) {
            error_log("Error: Formato de fecha inválido en guardarReserva - entrada: '$fecha_entrada', salida: '$fecha_salida'");
            throw new Exception("Formato de fecha inválido");
        }
        
        // Log para debugging (remover en producción si es necesario)
        error_log("Guardando reserva con fechas - entrada: $fecha_entrada, salida: $fecha_salida");
        // Validar disponibilidad real antes de insertar
        $id_apartamento_check = isset($datos['id_apartamento']) ? (int)$datos['id_apartamento'] : 1;
        if (!isRangoDisponible($fecha_entrada, $fecha_salida, $id_apartamento_check)) {
            error_log("Rango no disponible al guardar reserva: $fecha_entrada → $fecha_salida");
            if ($db->inTransaction()) {
                $db->rollback();
            }
            return 'rango_no_disponible'; // string para distinguirlo de false genérico
        }
        $num_adultos = (int)$datos['num_adultos'];
        $num_ninos = isset($datos['num_ninos']) ? (int)$datos['num_ninos'] : 0;
        $vive_palmira = isset($datos['vive_palmira']) && $datos['vive_palmira'] ? 1 : 0;
        // Validar y normalizar metodo_pago
        $metodo_pago = isset($datos['metodo_pago']) ? trim(strtolower($datos['metodo_pago'])) : 'tarjeta_credito';
        if (!in_array($metodo_pago, ['transferencia', 'tarjeta_credito'])) {
            error_log("Warning: metodo_pago inválido: '$metodo_pago', usando 'tarjeta_credito' por defecto");
            $metodo_pago = 'tarjeta_credito';
        }
        $costo_base = isset($datos['costo_base']) ? (float)$datos['costo_base'] : 0;
        $descuento_fidelizacion = isset($datos['descuento_fidelizacion']) ? (float)$datos['descuento_fidelizacion'] : 0;
        $descuento_cumpleanios = isset($datos['descuento_cumpleanios']) ? (float)$datos['descuento_cumpleanios'] : 0;
        $descuento_promocional = isset($datos['descuento_promocional']) ? (float)$datos['descuento_promocional'] : 0;
        $total = (float)$datos['total'];
        
        $stmt->bindParam(':id_apartamento', $id_apartamento, PDO::PARAM_INT);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':apellido', $apellido, PDO::PARAM_STR);
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento, PDO::PARAM_STR);
        $stmt->bindParam(':fecha_entrada', $fecha_entrada, PDO::PARAM_STR);
        $stmt->bindParam(':fecha_salida', $fecha_salida, PDO::PARAM_STR);
        $stmt->bindParam(':num_adultos', $num_adultos, PDO::PARAM_INT);
        $stmt->bindParam(':num_ninos', $num_ninos, PDO::PARAM_INT);
        $stmt->bindParam(':vive_palmira', $vive_palmira, PDO::PARAM_INT);
        $stmt->bindParam(':metodo_pago', $metodo_pago, PDO::PARAM_STR);
        $stmt->bindParam(':costo_base', $costo_base, PDO::PARAM_STR);
        $stmt->bindParam(':descuento_fidelizacion', $descuento_fidelizacion, PDO::PARAM_STR);
        $stmt->bindParam(':descuento_cumpleanios', $descuento_cumpleanios, PDO::PARAM_STR);
        $stmt->bindParam(':descuento_promocional', $descuento_promocional, PDO::PARAM_STR);
        $stmt->bindParam(':total', $total, PDO::PARAM_STR);
        
        // Ejecutar inserción
        $stmt->execute();
        
        // Obtener ID de la reserva insertada
        $reserva_id = $db->lastInsertId();
        
        // Confirmar transacción
        $db->commit();
        
        // Notificar a gerencia que existe una reserva pendiente por aprobar
        try {
            if (class_exists('GmailSender')) {
                $reserva_para_email = $datos;
                $reserva_para_email['id_reserva'] = $reserva_id;
                $reserva_para_email['num_adultos'] = $num_adultos;
                $reserva_para_email['num_ninos'] = $num_ninos;
                $reserva_para_email['vive_palmira'] = $vive_palmira;
                $reserva_para_email['metodo_pago'] = $metodo_pago;
                $reserva_para_email['costo_base'] = $costo_base;
                $reserva_para_email['descuento_fidelizacion'] = $descuento_fidelizacion;
                $reserva_para_email['descuento_cumpleanios'] = $descuento_cumpleanios;
                $reserva_para_email['descuento_promocional'] = $descuento_promocional;
                $reserva_para_email['total'] = $total;

                $gmailSender = new GmailSender();
                $gmailSender->sendReservaPendienteGerencia($reserva_para_email);
            }
        } catch (Throwable $emailError) {
            error_log("Error al enviar correo de reserva pendiente a gerencia (no crítico): " . $emailError->getMessage());
        }
        
        // Intentar enviar email (no crítico, no debe fallar la reserva si falla el email)
        try {
            enviarEmailConfirmacion($datos, $reserva_id);
        } catch (Exception $emailError) {
            error_log("Error al enviar email de confirmación (no crítico): " . $emailError->getMessage());
        }
        
        return $reserva_id;
        
    } catch (PDOException $e) {
        // Rollback en caso de error
        if ($db->inTransaction()) {
            $db->rollback();
        }
        error_log("Error PDO en guardarReserva: " . $e->getMessage());
        error_log("SQL State: " . $e->getCode());
        return false;
    } catch (Exception $e) {
        // Rollback en caso de error
        if ($db->inTransaction()) {
            $db->rollback();
        }
        error_log("Error en guardarReserva: " . $e->getMessage());
        return false;
    }
}

/**
 * Enviar email de confirmación
 */
function enviarEmailConfirmacion($datos, $reserva_id) {
    $asunto = "Nueva Solicitud de Reserva #$reserva_id - My Suite In Cartagena";
    
    $mensaje = "
    <h2>Nueva Solicitud de Reserva</h2>
    <p><strong>ID de Reserva:</strong> #$reserva_id</p>
    
    <h3>Datos del Cliente:</h3>
    <p><strong>Nombre:</strong> {$datos['nombre']} {$datos['apellido']}</p>
    <p><strong>Email:</strong> {$datos['correo']}</p>
    <p><strong>Teléfono:</strong> {$datos['telefono']}</p>
    <p><strong>Fecha de Nacimiento:</strong> " . (isset($datos['fecha_nacimiento']) ? $datos['fecha_nacimiento'] : 'N/A') . "</p>
    
    <h3>Detalles de la Reserva:</h3>
    <p><strong>Fecha de Entrada:</strong> {$datos['fecha_entrada']}</p>
    <p><strong>Fecha de Salida:</strong> {$datos['fecha_salida']}</p>
    <p><strong>Adultos:</strong> {$datos['num_adultos']}</p>
    <p><strong>Niños:</strong> " . (isset($datos['num_ninos']) ? $datos['num_ninos'] : 0) . "</p>
    <p><strong>Vive en Palmira:</strong> " . (isset($datos['vive_palmira']) && $datos['vive_palmira'] ? 'Sí' : 'No') . "</p>
    <p><strong>Método de Pago:</strong> {$datos['metodo_pago']}</p>
    
    <h3>Resumen Financiero:</h3>
    <p><strong>Costo Base:</strong> $" . number_format($datos['costo_base'], 0, ',', '.') . " COP</p>
    <p><strong>Descuento Fidelización:</strong> $" . number_format($datos['descuento_fidelizacion'], 0, ',', '.') . " COP</p>
    <p><strong>Descuento Cumpleaños:</strong> $" . number_format($datos['descuento_cumpleanios'], 0, ',', '.') . " COP</p>
    <p><strong>Descuento Promocional:</strong> $" . number_format($datos['descuento_promocional'], 0, ',', '.') . " COP</p>
    <p><strong>Total Final:</strong> $" . number_format($datos['total'], 0, ',', '.') . " COP</p>
    
    <p>Por favor, revisa esta solicitud en el panel de administración.</p>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@mysuitecartagena.com" . "\r\n";
    
    // Para desarrollo, solo log
    error_log("Email de reserva #$reserva_id: " . $mensaje);
}

/**
 * Obtener estadísticas de reservas
 */
function getEstadisticasReservas() {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        return [
            'total_reservas' => 0,
            'reservas_pendientes' => 0,
            'reservas_aprobadas' => 0,
            'reservas_rechazadas' => 0,
            'reservas_canceladas' => 0,
            'ingresos_totales' => 0
        ];
    }
    
    try {
        $estadisticas = [];
        
        // Total de reservas
        $query_total = "SELECT COUNT(*) as total FROM reservas";
        $stmt = $db->prepare($query_total);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $estadisticas['total_reservas'] = (int)$result['total'];
        
        // Reservas pendientes
        $query_pendientes = "SELECT COUNT(*) as total FROM reservas WHERE estado = 'pendiente'";
        $stmt = $db->prepare($query_pendientes);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $estadisticas['reservas_pendientes'] = (int)$result['total'];
        
        // Reservas aprobadas
        $query_aprobadas = "SELECT COUNT(*) as total FROM reservas WHERE estado = 'aprobada'";
        $stmt = $db->prepare($query_aprobadas);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $estadisticas['reservas_aprobadas'] = (int)$result['total'];
        
        // Reservas rechazadas
        $query_rechazadas = "SELECT COUNT(*) as total FROM reservas WHERE estado = 'rechazada'";
        $stmt = $db->prepare($query_rechazadas);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $estadisticas['reservas_rechazadas'] = (int)$result['total'];
        
        // Reservas canceladas
        $query_canceladas = "SELECT COUNT(*) as total FROM reservas WHERE estado = 'cancelada'";
        $stmt = $db->prepare($query_canceladas);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $estadisticas['reservas_canceladas'] = (int)$result['total'];
        
        // Ingresos totales (solo reservas aprobadas)
        $query_ingresos = "SELECT SUM(total) as ingresos FROM reservas WHERE estado = 'aprobada'";
        $stmt = $db->prepare($query_ingresos);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $estadisticas['ingresos_totales'] = (float)($result['ingresos'] ?? 0);
        
        // Métodos de pago
        $query_tarjeta = "SELECT COUNT(*) as total FROM reservas WHERE metodo_pago = 'tarjeta_credito'";
        $stmt = $db->prepare($query_tarjeta);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $estadisticas['metodo_tarjeta'] = (int)$result['total'];
        
        $query_efectivo = "SELECT COUNT(*) as total FROM reservas WHERE metodo_pago = 'transferencia'";
        $stmt = $db->prepare($query_efectivo);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $estadisticas['metodo_efectivo'] = (int)$result['total'];
        
        return $estadisticas;
    } catch (Exception $e) {
        error_log("Error en getEstadisticasReservas: " . $e->getMessage());
        return [
            'total_reservas' => 0,
            'reservas_pendientes' => 0,
            'reservas_aprobadas' => 0,
            'reservas_rechazadas' => 0,
            'reservas_canceladas' => 0,
            'ingresos_totales' => 0
        ];
    }
}

/**
 * Obtener reservas recientes
 */
function getReservasRecientes($limite = 10) {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        return [];
    }
    
    try {
        $query = "
            SELECT r.*, a.nombre as apartamento_nombre 
            FROM reservas r 
            LEFT JOIN apartamentos a ON r.id_apartamento = a.id_apartamento 
            ORDER BY r.creado_en DESC 
            LIMIT :limite
        ";
        
        $stmt = $db->prepare($query);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error en getReservasRecientes: " . $e->getMessage());
        return [];
    }
}
?>

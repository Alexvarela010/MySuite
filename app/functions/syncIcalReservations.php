<?php
/**
 * syncIcalReservations.php
 *
 * Sincroniza reservas desde feeds iCal de Airbnb / Booking.com
 * directamente en la tabla `reservas`, en lugar de bloquear días
 * manualmente en `fechas_bloqueadas`.
 *
 * Uso:
 *   require_once 'syncIcalReservations.php';
 *   $resultado = syncIcalReservations($apartamento_id);
 *
 * También puede ejecutarse como CLI / cron:
 *   php syncIcalReservations.php [apartamento_id]
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/CalendarIntegrator.php';

/**
 * Devuelve las URLs iCal configuradas para un apartamento.
 * Reutiliza la misma lógica de getIcalFeedUrls() en functions.php
 * para no duplicar código (pero no la llama directamente para
 * mantener este archivo auto-contenido).
 */
function _ical_sync_getFeedUrls(int $apartamento_id): array
{
    $configPath = __DIR__ . '/../../config/ical_feeds.php';
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
    return array_values(array_filter($feeds, static function ($url) {
        return is_string($url) && filter_var($url, FILTER_VALIDATE_URL);
    }));
}

/**
 * Detecta la plataforma a partir de la URL del feed iCal.
 *
 * @return string  'airbnb' | 'booking' | 'ical_externo'
 */
function _ical_sync_detectPlatform(string $url): string
{
    $url_lower = strtolower($url);
    if (str_contains($url_lower, 'airbnb')) {
        return 'airbnb';
    }
    if (str_contains($url_lower, 'booking.com') || str_contains($url_lower, 'booking')) {
        return 'booking';
    }
    return 'ical_externo';
}

/**
 * Verifica si una reserva con el mismo UID ya existe.
 * Como fallback para registros migrados sin UID, compara por fechas.
 */
function _ical_sync_reservaExiste(PDO $db, int $apartamento_id, string $fecha_entrada, string $fecha_salida, string $ical_uid = ''): bool
{
    if ($ical_uid !== '') {
        $sql = "SELECT COUNT(*) FROM reservas WHERE ical_uid = :uid AND id_apartamento = :apt AND estado NOT IN ('cancelada','rechazada')";
        $stmt = $db->prepare($sql);
        $stmt->execute([':uid' => $ical_uid, ':apt' => $apartamento_id]);
        $count = (int) $stmt->fetchColumn();
        if ($count > 0) {
            return true;
        }
    }

    $sql = "
        SELECT COUNT(*) 
        FROM reservas
        WHERE id_apartamento = :apt
          AND fecha_entrada   = :entrada
          AND fecha_salida    = :salida
          AND estado NOT IN ('cancelada','rechazada')
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':apt'    => $apartamento_id,
        ':entrada' => $fecha_entrada,
        ':salida'  => $fecha_salida,
    ]);
    return (int) $stmt->fetchColumn() > 0;
}

/**
 * Inserta una reserva procedente de iCal en la tabla `reservas`.
 *
 * Campos notables:
 *  - estado         → 'aprobada'  (ya confirmada en plataforma externa)
 *  - metodo_pago    → 'transferencia' (pago gestionado por la plataforma)
 *  - nombre/apellido → se extraen del SUMMARY cuando es posible;
 *                      si no, se usa el nombre de la plataforma.
 *  - id_usuario     → NULL (no hay cuenta local)
 *
 * @param PDO    $db
 * @param int    $apartamento_id
 * @param string $fecha_entrada   'Y-m-d'
 * @param string $fecha_salida    'Y-m-d'
 * @param string $summary         Texto del campo SUMMARY del evento iCal
 * @param string $platform        'airbnb' | 'booking' | 'ical_externo'
 * @return int   ID de la reserva insertada
 */
function _ical_sync_insertReserva(
    PDO    $db,
    int    $apartamento_id,
    string $fecha_entrada,
    string $fecha_salida,
    string $summary,
    string $platform,
    string $ical_uid = ''
): int {
    // ── Extraer nombre del huésped desde SUMMARY ───────────────────────────
    // Airbnb usa  "Reservation - FirstName LastName (HMXXXXX)"
    // Booking usa "Guest: FirstName LastName"  o simplemente el nombre
    $nombre   = ucfirst($platform);   // fallback
    $apellido = 'iCal';

    $summary_clean = trim($summary);
    if ($summary_clean !== '' && $summary_clean !== 'Blocked') {
        // Quitar prefijos comunes
        $summary_clean = preg_replace('/^(Reservation\s*-\s*|Guest:\s*)/i', '', $summary_clean);
        // Quitar código de reserva entre paréntesis: "(HMXXXXX)"
        $summary_clean = preg_replace('/\s*\([^)]*\)\s*/', '', $summary_clean);
        $summary_clean = trim($summary_clean);

        if ($summary_clean !== '') {
            $parts = explode(' ', $summary_clean, 2);
            $nombre   = $parts[0]            ?: $nombre;
            $apellido = $parts[1] ?? 'iCal';
        }
    }

    // Calcular noches para el costo_base estimado (se deja en 0; el admin lo ajusta)
    $inicio = new DateTime($fecha_entrada);
    $fin    = new DateTime($fecha_salida);
    $noches = max(1, (int) $inicio->diff($fin)->days);

    $sql = "
        INSERT INTO reservas (
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
            estado,
            comentario,
            ical_uid,
            ical_platform
        ) VALUES (
            :apartamento_id,
            NULL,
            :nombre,
            :apellido,
            :correo,
            '',
            NULL,
            :fecha_entrada,
            :fecha_salida,
            1,
            0,
            0,
            'transferencia',
            0,
            0,
            0,
            0,
            0,
            'aprobada',
            :notas,
            :ical_uid,
            :ical_platform
        )
    ";

    $notas = "Importada automáticamente desde {$platform} · SUMMARY: {$summary}";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':apartamento_id' => $apartamento_id,
        ':nombre'         => $nombre,
        ':apellido'       => $apellido,
        ':correo'         => "sync@{$platform}.auto",
        ':fecha_entrada'  => $fecha_entrada,
        ':fecha_salida'   => $fecha_salida,
        ':notas'          => $notas,
        ':ical_uid'       => $ical_uid !== '' ? $ical_uid : null,
        ':ical_platform'  => $platform,
    ]);

    return (int) $db->lastInsertId();
}

/**
 * Cancela reservas sync que ya no aparecen en el feed iCal actual.
 *
 * Busca reservas con ical_platform = :platform cuyo ical_uid no esté
 * en la lista de UIDs vigentes. Las marca como 'cancelada' en lugar de
 * borrarlas, para tener trazabilidad.
 *
 * @param PDO    $db
 * @param int    $apartamento_id
 * @param string $platform       'airbnb' | 'booking' | 'ical_externo'
 * @param array  $validUids      Lista de UIDs que SÍ están vigentes
 * @return int   Cantidad de reservas canceladas
 */
function _ical_sync_cleanupRemovedReservations(PDO $db, int $apartamento_id, string $platform, array $validUids): int
{
    if (empty($validUids)) {
        return 0;
    }

    $placeholders = [];
    $params = [':platform' => $platform, ':apt' => $apartamento_id];
    foreach ($validUids as $i => $uid) {
        $key = ":uid{$i}";
        $placeholders[] = $key;
        $params[$key] = $uid;
    }
    $inClause = implode(',', $placeholders);

    $sql = "
        UPDATE reservas
        SET estado = 'cancelada',
            comentario = CONCAT(comentario, ' | Cancelada automáticamente por sync iCal: ya no aparece en el feed')
        WHERE id_apartamento = :apt
          AND ical_platform  = :platform
          AND ical_uid IS NOT NULL
          AND ical_uid NOT IN ({$inClause})
          AND fecha_salida >= CURDATE()
          AND estado NOT IN ('cancelada', 'rechazada')
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Función principal de sincronización.
 *
 * @param int $apartamento_id
 * @return array {
 *   int    creadas,
 *   int    omitidas,
 *   int    errores,
 *   array  detalle   — log de cada evento procesado
 * }
 */
function syncIcalReservations(int $apartamento_id = 1): array
{
    $result = [
        'creadas'  => 0,
        'omitidas' => 0,
        'errores'  => 0,
        'detalle'  => [],
    ];

    // ── Conexión a BD ──────────────────────────────────────────────────────
    $database = new Database();
    $db = $database->getConnection();
    if (!$db) {
        $result['errores']++;
        $result['detalle'][] = '[ERROR] No se pudo conectar a la base de datos.';
        return $result;
    }

    // ── Feeds iCal ─────────────────────────────────────────────────────────
    $feedUrls = _ical_sync_getFeedUrls($apartamento_id);
    if (empty($feedUrls)) {
        $result['detalle'][] = '[INFO] No hay feeds iCal configurados para el apartamento ' . $apartamento_id;
        return $result;
    }

    $integrator = new CalendarIntegrator();

    foreach ($feedUrls as $url) {
        $platform = _ical_sync_detectPlatform($url);
        $result['detalle'][] = "[INFO] Procesando feed {$platform}: {$url}";

        // Descargar iCal
        try {
            $icalContent = $integrator->fetchIcalUrl($url);
        } catch (Throwable $e) {
            $result['errores']++;
            $result['detalle'][] = "[ERROR] No se pudo descargar el feed: " . $e->getMessage();
            continue;
        }

        if (!$icalContent) {
            $result['errores']++;
            $result['detalle'][] = "[ERROR] Respuesta vacía del feed: {$url}";
            continue;
        }

        // Parsear eventos
        $events = $integrator->parseIcalData($icalContent);

        // ── Colectar UIDs vigentes en el feed para limpieza posterior ─
        $validUids = [];

        foreach ($events as $event) {
            // Necesitamos DTSTART y DTEND para crear la reserva
            if (empty($event['DTSTART']) || empty($event['DTEND'])) {
                $result['omitidas']++;
                $result['detalle'][] = "[SKIP] Evento sin DTSTART/DTEND: " . json_encode($event);
                continue;
            }

            try {
                $startDt = new DateTime($event['DTSTART']);
                $endDt   = new DateTime($event['DTEND']);
            } catch (Throwable $e) {
                $result['errores']++;
                $result['detalle'][] = "[ERROR] Fecha inválida en evento: " . json_encode($event);
                continue;
            }

            $fecha_entrada = $startDt->format('Y-m-d');
            // En iCal la DTEND es exclusiva (el huésped se va ese día),
            // así que la fecha_salida del calendario = DTEND tal cual.
            $fecha_salida  = $endDt->format('Y-m-d');

            // Descartar eventos en el pasado (opcional — puedes quitarlo si quieres histórico)
            if ($fecha_salida < date('Y-m-d')) {
                $result['omitidas']++;
                $result['detalle'][] = "[SKIP] Evento ya pasado: {$fecha_entrada} → {$fecha_salida}";
                continue;
            }

            // Descartar bloqueos genéricos sin rango real (misma fecha inicio y fin)
            if ($fecha_entrada === $fecha_salida) {
                $result['omitidas']++;
                $result['detalle'][] = "[SKIP] Evento con duración 0 días: {$fecha_entrada}";
                continue;
            }

            $summary = $event['SUMMARY'] ?? '';
            $icalUid = $event['UID'] ?? '';

            // Registrar UID si el evento tiene uno
            if ($icalUid !== '') {
                $validUids[] = $icalUid;
            }

            // ── Verificar duplicado en BD (primero por UID, luego por fechas) ──
            if (_ical_sync_reservaExiste($db, $apartamento_id, $fecha_entrada, $fecha_salida, $icalUid)) {
                $result['omitidas']++;
                $result['detalle'][] = "[SKIP] Ya existe reserva para {$fecha_entrada} → {$fecha_salida} (UID: {$icalUid})";
                continue;
            }

            // ── Crear la reserva ───────────────────────────────────────────
            try {
                $db->beginTransaction();
                $id = _ical_sync_insertReserva(
                    $db,
                    $apartamento_id,
                    $fecha_entrada,
                    $fecha_salida,
                    $summary,
                    $platform,
                    $icalUid
                );
                $db->commit();

                $result['creadas']++;
                $result['detalle'][] = "[OK] Reserva #{$id} creada: {$fecha_entrada} → {$fecha_salida} ({$platform}) UID: {$icalUid}";

            } catch (Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $result['errores']++;
                $result['detalle'][] = "[ERROR] No se pudo insertar reserva {$fecha_entrada} → {$fecha_salida}: " . $e->getMessage();
            }
        }

        // ── Limpiar reservas que ya no están en el feed ────────────────
        $limpiadas = _ical_sync_cleanupRemovedReservations($db, $apartamento_id, $platform, $validUids);
        if ($limpiadas > 0) {
            $result['detalle'][] = "[CLEANUP] {$limpiadas} reserva(s) eliminada(s) del feed {$platform} — ya no aparecen en el iCal";
        }
    }

    return $result;
}

// ── Ejecución directa (CLI / cron) ─────────────────────────────────────────
if (PHP_SAPI === 'cli' || (isset($argv) && isset($argv[0]) && realpath($argv[0]) === __FILE__)) {
    $apt_id = isset($argv[1]) ? (int) $argv[1] : 1;
    echo "Sincronizando iCal para apartamento #{$apt_id}...\n\n";

    $r = syncIcalReservations($apt_id);

    foreach ($r['detalle'] as $linea) {
        echo $linea . "\n";
    }

    echo "\n──────────────────────────────\n";
    echo "Creadas : {$r['creadas']}\n";
    echo "Omitidas: {$r['omitidas']}\n";
    echo "Errores : {$r['errores']}\n";
}

<?php
/**
 * debug_ical_sync.php
 *
 * Script de diagnóstico completo para la sincronización iCal.
 * Ejecutar desde CLI:
 *   php debug_ical_sync.php
 *
 * O desde navegador (protegido con token):
 *   https://tudominio.com/debug_ical_sync.php?token=DEBUG_TOKEN
 */

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ── Rutas ── ajusta si tu estructura difiere ────────────────────────────────
// Este script se asume en: public_html/debug_ical_sync.php
$BASE_DIR = __DIR__;

$CALENDAR_INTEGRATOR = $BASE_DIR . '/includes/CalendarIntegrator.php';
$DATABASE_CONFIG      = $BASE_DIR . '/config/database.php';
$ICAL_FEEDS_CONFIG    = $BASE_DIR . '/config/ical_feeds.php';
$SYNC_FILE            = $BASE_DIR . '/app/functions/syncIcalReservations.php';

// URLs de prueba (fallback si el config no existe)
$BOOKING_URL = 'https://ical.booking.com/v1/export?t=b5694e69-7846-40fe-97ca-6fbd57f2dcd5';
$AIRBNB_URL  = 'https://www.airbnb.com.co/calendar/ical/1302398377373776230.ics?s=14b0fbf2ce3f9a577984f237647b8186';
$APARTAMENTO_ID = 1;

// ═══════════════════════════════════════════════════════════════════════════
function ln(string $s = ''): void  { echo $s . "\n"; }
function ok(string $s): void       { echo "[  OK  ] " . $s . "\n"; }
function er(string $s): void       { echo "[ FAIL ] " . $s . "\n"; }
function nf(string $s): void       { echo "[ INFO ] " . $s . "\n"; }
function sep(): void               { echo str_repeat('─', 60) . "\n"; }
// ═══════════════════════════════════════════════════════════════════════════

sep();
ln("DEBUG ICAL SYNC  —  " . date('Y-m-d H:i:s'));
sep();

// ── 1. Entorno PHP ──────────────────────────────────────────────────────────
ln("\n[1] ENTORNO PHP");
nf("PHP version      : " . PHP_VERSION);
nf("SAPI             : " . PHP_SAPI);
nf("Timezone         : " . date_default_timezone_get());
nf("cURL             : " . (function_exists('curl_init') ? 'disponible' : '!! NO DISPONIBLE !!'));
nf("allow_url_fopen  : " . (ini_get('allow_url_fopen') ? 'On' : 'Off'));
nf("Script dir       : " . $BASE_DIR);

// ── 2. Archivos requeridos ──────────────────────────────────────────────────
ln("\n[2] ARCHIVOS REQUERIDOS");
foreach ([
             'CalendarIntegrator.php' => $CALENDAR_INTEGRATOR,
             'database.php'           => $DATABASE_CONFIG,
             'ical_feeds.php'         => $ICAL_FEEDS_CONFIG,
             'syncIcalReservations.php' => $SYNC_FILE,
         ] as $label => $path) {
    if (file_exists($path)) {
        ok("{$label}  →  {$path}");
    } else {
        er("{$label}  NO encontrado  →  {$path}");
    }
}

// ── 3. Cargar clases ────────────────────────────────────────────────────────
ln("\n[3] CARGA DE CLASES");

if (!file_exists($CALENDAR_INTEGRATOR)) {
    er("No se puede continuar sin CalendarIntegrator.php — revisa la ruta.");
    exit(1);
}
require_once $CALENDAR_INTEGRATOR;
ok("CalendarIntegrator cargado.");

if (file_exists($DATABASE_CONFIG)) {
    require_once $DATABASE_CONFIG;
    ok("database.php cargado.");
} else {
    er("database.php no encontrado — las pruebas de BD fallarán.");
}

// ── 4. Feeds configurados ───────────────────────────────────────────────────
ln("\n[4] FEEDS ICAL CONFIGURADOS");
$feedsFromConfig = [];
if (file_exists($ICAL_FEEDS_CONFIG)) {
    $config = require $ICAL_FEEDS_CONFIG;
    $feedsFromConfig = $config[$APARTAMENTO_ID] ?? $config[(string)$APARTAMENTO_ID] ?? [];
    nf("Feeds en config para apartamento {$APARTAMENTO_ID}: " . count($feedsFromConfig));
    foreach ($feedsFromConfig as $i => $url) {
        nf("  [{$i}] {$url}");
    }
} else {
    er("ical_feeds.php no encontrado → usando URLs hardcodeadas para el test.");
}

$testUrls = !empty($feedsFromConfig) ? $feedsFromConfig : [$BOOKING_URL, $AIRBNB_URL];

ln();
nf("URLs que se van a probar:");
foreach ($testUrls as $url) {
    nf("  → {$url}");
}

// ── 5. Descarga de feeds ────────────────────────────────────────────────────
ln("\n[5] DESCARGA DE FEEDS ICAL");

$integrator = new CalendarIntegrator();
$allEvents  = [];

foreach ($testUrls as $idx => $url) {
    sep();
    $platform = stripos($url, 'airbnb')  !== false ? 'Airbnb'
        : (stripos($url, 'booking') !== false ? 'Booking.com' : 'iCal externo');
    nf("Feed #{$idx} — {$platform}");
    nf("URL: {$url}");

    // Probar cURL directamente
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; CalendarSync/1.0)',
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $rawContent = curl_exec($ch);
    $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError  = curl_error($ch);
    curl_close($ch);

    nf("HTTP status      : {$httpCode}");
    nf("cURL error       : " . ($curlError ?: 'ninguno'));
    nf("Bytes recibidos  : " . strlen((string)$rawContent));

    if ($httpCode >= 200 && $httpCode < 300 && $rawContent) {
        ok("Descarga exitosa.");
        $lines = array_slice(explode("\n", $rawContent), 0, 12);
        nf("Primeras líneas del iCal:");
        foreach ($lines as $l) {
            nf("  " . rtrim($l));
        }
    } else {
        er("Descarga fallida (HTTP {$httpCode}).");
        continue;
    }

    // Parsear
    $events = $integrator->parseIcalData($rawContent);
    nf("Eventos parseados: " . count($events));

    if (empty($events)) {
        er("No se parseó ningún evento.");
        // Mostrar todas las líneas DTSTART para diagnóstico de formato
        preg_match_all('/^DTSTART.*$/m', $rawContent, $matches);
        nf("Líneas DTSTART en el raw iCal (" . count($matches[0]) . " encontradas):");
        foreach (array_slice($matches[0], 0, 10) as $l) {
            nf("  " . rtrim($l));
        }
        // También VEVENT count
        $veventCount = substr_count($rawContent, 'BEGIN:VEVENT');
        nf("BEGIN:VEVENT encontrados en raw: {$veventCount}");
    } else {
        ok("Eventos parseados correctamente.");
        foreach (array_slice($events, 0, 5) as $i => $ev) {
            nf("  Evento {$i}: DTSTART={$ev['DTSTART']}  DTEND={$ev['DTEND']}  SUMMARY=" . ($ev['SUMMARY'] ?? '(sin summary)'));
        }
        if (count($events) > 5) {
            nf("  ... y " . (count($events) - 5) . " eventos más.");
        }
        foreach ($events as $e) {
            $e['_platform'] = $platform;
            $allEvents[] = $e;
        }
    }
}

// ── 6. Eventos candidatos ───────────────────────────────────────────────────
sep();
ln("\n[6] EVENTOS CANDIDATOS A SINCRONIZAR");
nf("Total eventos (todos los feeds): " . count($allEvents));

$hoy     = date('Y-m-d');
$futuros = array_filter($allEvents, fn($e) => isset($e['DTEND']) && $e['DTEND'] > $hoy);
nf("Eventos futuros (DTEND > {$hoy}): " . count($futuros));

foreach ($futuros as $ev) {
    nf("  [{$ev['_platform']}] {$ev['DTSTART']} → {$ev['DTEND']}  SUMMARY: " . ($ev['SUMMARY'] ?? '-'));
}

// ── 7. Conexión a BD ────────────────────────────────────────────────────────
ln("\n[7] CONEXIÓN A BASE DE DATOS");
$db = null;
if (class_exists('Database')) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        if ($db) {
            ok("Conexión exitosa.");
            $stmt = $db->query("SHOW TABLES LIKE 'reservas'");
            if ($stmt->rowCount() > 0) {
                ok("Tabla 'reservas' existe.");
                $cols = $db->query("SHOW COLUMNS FROM reservas")->fetchAll(PDO::FETCH_COLUMN);
                nf("Columnas: " . implode(', ', $cols));

                // Verificar si tiene columna comentario
                if (!in_array('comentario', $cols)) {
                    er("La columna 'comentario' NO existe en 'reservas'.");
                    nf("Solución: agrega la columna o edita _ical_sync_insertReserva() en syncIcalReservations.php para quitarla del INSERT.");
                    nf("SQL para agregar: ALTER TABLE reservas ADD COLUMN comentario TEXT NULL AFTER estado;");
                } else {
                    ok("Columna 'comentario' existe.");
                }
            } else {
                er("Tabla 'reservas' NO existe.");
            }
        } else {
            er("getConnection() devolvió null.");
        }
    } catch (Throwable $e) {
        er("Excepción al conectar: " . $e->getMessage());
    }
} else {
    er("Clase Database no disponible — revisa que database.php esté cargado.");
}

// ── 8. Dry run ──────────────────────────────────────────────────────────────
ln("\n[8] SIMULACIÓN — DRY RUN (no inserta nada)");
if (!$db) {
    er("Sin conexión a BD — saltando dry run.");
} elseif (empty($futuros)) {
    nf("No hay eventos futuros para simular.");
} else {
    foreach ($futuros as $ev) {
        try {
            $startDt = new DateTime($ev['DTSTART']);
            $endDt   = new DateTime($ev['DTEND']);
        } catch (Throwable $e) {
            er("Fecha inválida: " . json_encode($ev));
            continue;
        }
        $fe = $startDt->format('Y-m-d');
        $fs = $endDt->format('Y-m-d');

        if ($fe === $fs) {
            nf("[DRY SKIP] Duración 0: {$fe}");
            continue;
        }
        $stmt = $db->prepare("
            SELECT id_reserva, estado
            FROM reservas
            WHERE id_apartamento = :apt
              AND fecha_entrada   = :entrada
              AND fecha_salida    = :salida
        ");
        $stmt->execute([':apt' => $APARTAMENTO_ID, ':entrada' => $fe, ':salida' => $fs]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            nf("[DRY EXISTE] Reserva #{$existing['id_reserva']} ({$existing['estado']}): {$fe} → {$fs}");
        } else {
            ok("[DRY NUEVA ] SE CREARÍA: {$fe} → {$fs}  [{$ev['_platform']}]  SUMMARY: " . ($ev['SUMMARY'] ?? '-'));
        }
    }
}

// ── 9. Cron ─────────────────────────────────────────────────────────────────
ln("\n[9] REVISIÓN DEL CRON");
$phpBin = trim((string)(shell_exec('which php 2>/dev/null') ?: ''));
nf("PHP binario detectado : " . ($phpBin ?: 'no encontrado con which'));
nf("Tu cron actual        : * * * * * php /home/mysgd5s3m2re/public_html/app/functions/syncIcalReservations.php 1");
nf("Cron recomendado      : */15 * * * * /usr/local/bin/php -q /home/mysgd5s3m2re/public_html/app/functions/syncIcalReservations.php 1 >> /home/mysgd5s3m2re/public_html/app/api/logs/ical_sync.log 2>&1");

if (file_exists($SYNC_FILE)) {
    ok("syncIcalReservations.php encontrado.");
} else {
    er("syncIcalReservations.php NO encontrado en: {$SYNC_FILE}");
    nf("Ruta que busca el cron: /home/mysgd5s3m2re/public_html/app/functions/syncIcalReservations.php");
}

// ── 10. Ejecución real ──────────────────────────────────────────────────────
ln("\n[10] SINCRONIZACIÓN REAL");
if (!file_exists($SYNC_FILE)) {
    er("syncIcalReservations.php no encontrado — no se puede ejecutar la sincronización real.");
} else {
    nf("Ejecutando syncIcalReservations({$APARTAMENTO_ID})...");
    require_once $SYNC_FILE;
    $resultado = syncIcalReservations($APARTAMENTO_ID);
    sep();
    ok("Sincronización completada.");
    nf("Creadas : {$resultado['creadas']}");
    nf("Omitidas: {$resultado['omitidas']}");
    nf("Errores : {$resultado['errores']}");
    ln();
    nf("Detalle:");
    foreach ($resultado['detalle'] as $linea) {
        nf("  " . $linea);
    }
}

sep();
ln("FIN DEL DEBUG — " . date('Y-m-d H:i:s'));
sep();
<?php
/**
 * api/sync_ical.php
 *
 * Endpoint HTTP para disparar la sincronización iCal.
 * Puede llamarse desde:
 *   - Un cron job externo vía wget / curl
 *   - El panel de administración
 *   - Un cron interno de PHP
 *
 * Protección mínima con token secreto en la cabecera o query string.
 * Define ICAL_SYNC_TOKEN en config o como variable de entorno.
 *
 * Ejemplos de llamada:
 *   curl "https://tudominio.com/app/api/sync_ical.php?&apartamento_id=1"
 *   wget -q -O- "https://tudominio.com/app/api/sync_ical.php?"
 */

header('Content-Type: application/json; charset=utf-8');

// ── Parámetros ──────────────────────────────────────────────────────────────
$apartamento_id = isset($_GET['apartamento_id']) ? (int) $_GET['apartamento_id'] : 1;

// ── Ejecutar sincronización ─────────────────────────────────────────────────
require_once __DIR__ . '/../../app/functions/syncIcalReservations.php';

$resultado = syncIcalReservations($apartamento_id);

echo json_encode([
    'success'      => true,
    'apartamento'  => $apartamento_id,
    'creadas'      => $resultado['creadas'],
    'omitidas'     => $resultado['omitidas'],
    'errores'      => $resultado['errores'],
    'detalle'      => $resultado['detalle'],
    'timestamp'    => date('Y-m-d H:i:s'),
]);

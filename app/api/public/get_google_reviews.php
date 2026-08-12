<?php
/**
 * Endpoint publico para obtener resenas con cache.
 * 
 * Parámetros:
 * - limit: número de reseñas (default: 6)
 * - all: si es 1, trae todas las reseñas disponibles
 * - forceRefresh: si es 1, fuerza actualización desde Google
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../services/google_reviews_service.php';

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
$all = isset($_GET['all']) && $_GET['all'] == 1;
$forceRefresh = isset($_GET['forceRefresh']) && $_GET['forceRefresh'] == 1;

if ($all) {
    $limit = -1;
}

$service = new GoogleReviewsService();
$result = $service->getReviews($forceRefresh, $limit);

http_response_code($result['success'] ? 200 : 503);
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


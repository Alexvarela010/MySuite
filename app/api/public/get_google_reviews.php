<?php
/**
 * Endpoint publico para obtener resenas con cache.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../services/google_reviews_service.php';

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
$forceRefresh = false;

$service = new GoogleReviewsService();
$result = $service->getReviews($forceRefresh, $limit);

http_response_code($result['success'] ? 200 : 503);
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


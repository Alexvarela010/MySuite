<?php
/**
 * Endpoint para refrescar manualmente el caché de reseñas de Google
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../services/google_reviews_service.php';

$cachePath = dirname(__DIR__, 2) . '/logs/cache/google_reviews.json';

// Eliminar el archivo de caché si existe
if (file_exists($cachePath)) {
    @unlink($cachePath);
    error_log("Caché eliminado: " . $cachePath);
}

$service = new GoogleReviewsService();
// Siempre traer TODAS sin limit
$result = $service->getReviews(true, -1);

http_response_code($result['success'] ? 200 : 503);
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

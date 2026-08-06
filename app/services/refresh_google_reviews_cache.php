<?php
/**
 * Runner CLI para forzar actualizacion de cache de Google Reviews.
 * Uso: php app/services/refresh_google_reviews_cache.php
 */

require_once __DIR__ . '/google_reviews_service.php';

$service = new GoogleReviewsService();
$result = $service->getReviews(true, 10);

if (!empty($result['success'])) {
    echo "Cache de resenas actualizado. Fuente: " . ($result['source'] ?? 'google') . PHP_EOL;
    echo "Cantidad: " . ($result['count'] ?? 0) . PHP_EOL;
    echo "Fecha cache: " . ($result['cached_at'] ?? '') . PHP_EOL;
    exit(0);
}

echo "Error actualizando cache: " . ($result['message'] ?? 'Error desconocido') . PHP_EOL;
exit(1);


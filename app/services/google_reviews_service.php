<?php
/**
 * Servicio para obtener resenas de Google Business Profile con cache local.
 */
class GoogleReviewsService
{
    private $config;
    private $cachePath;

    public function __construct(array $config = [])
    {
        if (empty($config)) {
            $configFile = dirname(__DIR__, 2) . '/config/google_reviews.php';
            $config = file_exists($configFile) ? include $configFile : [];
        }

        $this->config = $config;
        $this->cachePath = dirname(__DIR__, 2) . '/logs/cache/google_reviews.json';
    }

    public function getReviews($forceRefresh = false, $limit = null)
    {
        $limit = $this->sanitizeLimit($limit);
        $cache = $this->readCache();

        if (!$forceRefresh && $cache && !$this->isCacheExpired($cache['fetched_at'] ?? null)) {
            return $this->buildSuccessResponse('cache', $cache['reviews'], $cache['fetched_at']);
        }

        $fresh = $this->fetchFromGoogle($limit);
        if ($fresh['success']) {
            $payload = [
                'fetched_at' => gmdate('c'),
                'reviews' => $fresh['reviews'],
            ];
            $this->writeCache($payload);
            return $this->buildSuccessResponse('google', $payload['reviews'], $payload['fetched_at']);
        }

        if ($cache && !empty($cache['reviews'])) {
            $response = $this->buildSuccessResponse('stale_cache', $cache['reviews'], $cache['fetched_at']);
            $response['warning'] = 'No se pudo actualizar desde Google, se sirve cache anterior.';
            return $response;
        }

        return [
            'success' => false,
            'message' => $fresh['message'] ?: 'No fue posible cargar resenas de Google.',
            'reviews' => [],
        ];
    }

    private function sanitizeLimit($limit)
    {
        if ($limit === null || (int)$limit <= 0) {
            $limit = (int)($this->config['default_limit'] ?? 6);
        }
        return max(1, min(12, (int)$limit));
    }

    private function fetchFromGoogle($limit)
    {
        $required = ['client_id', 'client_secret', 'refresh_token', 'account_id', 'location_id'];
        foreach ($required as $key) {
            if (empty($this->config[$key])) {
                return [
                    'success' => false,
                    'message' => 'Falta configurar Google Reviews: ' . $key,
                ];
            }
        }

        $tokenResult = $this->requestAccessToken();
        if (!$tokenResult['success']) {
            return [
                'success' => false,
                'message' => $tokenResult['message'],
            ];
        }

        $url = sprintf(
            'https://mybusiness.googleapis.com/v4/accounts/%s/locations/%s/reviews?pageSize=%d',
            rawurlencode($this->config['account_id']),
            rawurlencode($this->config['location_id']),
            (int)$limit
        );

        $response = $this->curlRequest($url, 'GET', null, [
            'Authorization: Bearer ' . $tokenResult['access_token'],
            'Accept: application/json',
        ]);

        if (!$response['success']) {
            return [
                'success' => false,
                'message' => 'Error consultando resenas: ' . $response['message'],
            ];
        }

        $data = json_decode($response['body'], true);
        if (!is_array($data)) {
            return [
                'success' => false,
                'message' => 'Respuesta invalida desde Google Reviews API.',
            ];
        }

        $reviews = [];
        if (!empty($data['reviews']) && is_array($data['reviews'])) {
            foreach ($data['reviews'] as $review) {
                $reviews[] = $this->normalizeReview($review);
            }
        }

        usort($reviews, function ($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });

        return [
            'success' => true,
            'reviews' => array_slice($reviews, 0, $limit),
            'message' => '',
        ];
    }

    private function requestAccessToken()
    {
        $payload = http_build_query([
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'refresh_token' => $this->config['refresh_token'],
            'grant_type' => 'refresh_token',
        ]);

        $response = $this->curlRequest(
            'https://oauth2.googleapis.com/token',
            'POST',
            $payload,
            ['Content-Type: application/x-www-form-urlencoded']
        );

        if (!$response['success']) {
            return [
                'success' => false,
                'message' => 'No se pudo renovar access token: ' . $response['message'],
            ];
        }

        $data = json_decode($response['body'], true);
        if (!is_array($data) || empty($data['access_token'])) {
            return [
                'success' => false,
                'message' => 'Google no devolvio access_token valido.',
            ];
        }

        return [
            'success' => true,
            'access_token' => $data['access_token'],
        ];
    }

    private function normalizeReview(array $review)
    {
        $ratingMap = [
            'ONE' => 1,
            'TWO' => 2,
            'THREE' => 3,
            'FOUR' => 4,
            'FIVE' => 5,
        ];

        $rawComment = trim((string)($review['comment'] ?? ''));
        $comment = $this->stripTranslatedBlock($rawComment);

        $reviewReply = '';
        if (!empty($review['reviewReply']['comment'])) {
            $reviewReply = trim((string)$review['reviewReply']['comment']);
        }

        $starLabel = strtoupper((string)($review['starRating'] ?? ''));

        return [
            'id' => (string)($review['reviewId'] ?? ''),
            'author_name' => (string)($review['reviewer']['displayName'] ?? 'Huesped'),
            'author_photo_url' => (string)($review['reviewer']['profilePhotoUrl'] ?? ''),
            'rating' => (int)($ratingMap[$starLabel] ?? 0),
            'comment' => $comment,
            'reply_comment' => $reviewReply,
            'created_at' => (string)($review['createTime'] ?? $review['updateTime'] ?? ''),
            'updated_at' => (string)($review['updateTime'] ?? ''),
        ];
    }

    private function stripTranslatedBlock($comment)
    {
        if ($comment === '') {
            return '';
        }

        $parts = preg_split('/\n\s*\n\(Translated by Google\)/i', $comment);
        if (is_array($parts) && isset($parts[0])) {
            return trim($parts[0]);
        }

        return trim($comment);
    }

    private function isCacheExpired($fetchedAt)
    {
        if (empty($fetchedAt)) {
            return true;
        }

        $ts = strtotime($fetchedAt);
        if ($ts === false) {
            return true;
        }

        $ttl = (int)($this->config['cache_ttl_seconds'] ?? 86400);
        return (time() - $ts) >= max(300, $ttl);
    }

    private function readCache()
    {
        if (!file_exists($this->cachePath)) {
            return null;
        }

        $raw = @file_get_contents($this->cachePath);
        if ($raw === false || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['reviews']) || !is_array($data['reviews'])) {
            return null;
        }

        return $data;
    }

    private function writeCache(array $payload)
    {
        $dir = dirname($this->cachePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        @file_put_contents(
            $this->cachePath,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    private function buildSuccessResponse($source, array $reviews, $cachedAt)
    {
        return [
            'success' => true,
            'source' => $source,
            'cached_at' => $cachedAt,
            'count' => count($reviews),
            'reviews' => $reviews,
        ];
    }

    private function curlRequest($url, $method = 'GET', $body = null, array $headers = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body ?? '');
        }

        $bodyResponse = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($bodyResponse === false) {
            return [
                'success' => false,
                'message' => $curlError ?: 'Error de conexion cURL.',
                'status' => $httpCode,
                'body' => '',
            ];
        }

        if ($httpCode >= 400) {
            return [
                'success' => false,
                'message' => 'HTTP ' . $httpCode,
                'status' => $httpCode,
                'body' => $bodyResponse,
            ];
        }

        return [
            'success' => true,
            'message' => '',
            'status' => $httpCode,
            'body' => $bodyResponse,
        ];
    }
}


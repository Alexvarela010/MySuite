<?php
$secretsFile = '/home/mysgd5s3m2re/secure/google_reviews_secrets.php';
$secrets = file_exists($secretsFile) ? include $secretsFile : [];

return [
    'client_id' => $secrets['client_id'] ?? getenv('GOOGLE_BUSINESS_CLIENT_ID') ?: '',
    'client_secret' => $secrets['client_secret'] ?? getenv('GOOGLE_BUSINESS_CLIENT_SECRET') ?: '',
    'refresh_token' => $secrets['refresh_token'] ?? getenv('GOOGLE_BUSINESS_REFRESH_TOKEN') ?: '',
    'account_id' => $secrets['account_id'] ?? getenv('GOOGLE_BUSINESS_ACCOUNT_ID') ?: '',
    'location_id' => $secrets['location_id'] ?? getenv('GOOGLE_BUSINESS_LOCATION_ID') ?: '',
    'cache_ttl_seconds' => 86400,
    'default_limit' => 6,
];
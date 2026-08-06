<?php
// db.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$host = 'localhost'; // MAMP local host
$db   = 'mysgd5s3m2re_mysuitei_reservas';
$user = 'mysgd5s3m2re_mysuitei_user';
$pass = 'MyS2.025InCartagena';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Le agregamos $e->getMessage() para que nos dé el error exacto
     echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
     exit;
}
?>

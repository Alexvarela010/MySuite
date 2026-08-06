<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/database.php';

$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

try {
    $sql = "SELECT * FROM descuentos_config WHERE activo = 1 ORDER BY tipo_descuento";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $descuentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convertir a formato más fácil de usar
    $descuentosFormatted = [];
    foreach ($descuentos as $descuento) {
        $descuentosFormatted[$descuento['tipo_descuento']] = [
            'porcentaje' => floatval($descuento['porcentaje']),
            'activo' => (bool)$descuento['activo']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'descuentos' => $descuentosFormatted
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}
?>

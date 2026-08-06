<?php
// settings.php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->query('SELECT * FROM GlobalSettings WHERE id = "global"');
        $settings = $stmt->fetch();
        
        if (!$settings) {
            $settings = [
                'appointmentDuration' => '60',
                'breakStartTime' => null,
                'breakEndTime' => null
            ];
            
            $stmt = $pdo->prepare('INSERT INTO GlobalSettings (id, appointmentDuration) VALUES ("global", "60")');
            $stmt->execute();
        }
        
        echo json_encode([
            'appointmentDuration' => $settings['appointmentDuration'],
            'breakStartTime' => $settings['breakStartTime'],
            'breakEndTime' => $settings['breakEndTime']
        ]);
    } catch (\PDOException $e) {
        echo json_encode(['error' => 'Error al obtener configuraciones: ' . $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Auth Check
    $password = $_GET['password'] ?? '';
    if ($password !== 'admin') {
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }

    $appointmentDuration = $data['appointmentDuration'] ?? '60';
    $breakStartTime = $data['breakStartTime'] ?? null;
    $breakEndTime = $data['breakEndTime'] ?? null;

    try {
        $stmt = $pdo->prepare('SELECT id FROM GlobalSettings WHERE id = "global"');
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare('UPDATE GlobalSettings SET appointmentDuration = ?, breakStartTime = ?, breakEndTime = ? WHERE id = "global"');
            $stmt->execute([$appointmentDuration, $breakStartTime, $breakEndTime]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO GlobalSettings (id, appointmentDuration, breakStartTime, breakEndTime) VALUES ("global", ?, ?, ?)');
            $stmt->execute([$appointmentDuration, $breakStartTime, $breakEndTime]);
        }
        
        echo json_encode(['success' => true]);
    } catch (\PDOException $e) {
        echo json_encode(['error' => 'Error al guardar configuración: ' . $e->getMessage()]);
    }
}
?>

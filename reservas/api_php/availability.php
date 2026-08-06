<?php
// availability.php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? null;
    
    if ($action === 'rules') {
        try {
            $stmt = $pdo->query('SELECT * FROM AvailabilityRule ORDER BY dayOfWeek ASC');
            $rules = $stmt->fetchAll();
            
            // Si no hay reglas, creamos las predeterminadas (L-V 9 a 17)
            if (empty($rules)) {
                $defaultRules = [
                    [0, '09:00', '17:00', 0], // Domingo
                    [1, '09:00', '17:00', 1], // Lunes
                    [2, '09:00', '17:00', 1], // Martes
                    [3, '09:00', '17:00', 1], // Miércoles
                    [4, '09:00', '17:00', 1], // Jueves
                    [5, '09:00', '17:00', 1], // Viernes
                    [6, '09:00', '17:00', 0], // Sábado
                ];
                
                $insertStmt = $pdo->prepare('INSERT INTO AvailabilityRule (id, dayOfWeek, startTime, endTime, isActive) VALUES (?, ?, ?, ?, ?)');
                foreach ($defaultRules as $rule) {
                    $insertStmt->execute([uniqid('rule_'), $rule[0], $rule[1], $rule[2], $rule[3]]);
                }
                
                $stmt = $pdo->query('SELECT * FROM AvailabilityRule ORDER BY dayOfWeek ASC');
                $rules = $stmt->fetchAll();
            }
            
            echo json_encode($rules);
        } catch (\PDOException $e) {
            echo json_encode(['error' => 'Error al obtener reglas: ' . $e->getMessage()]);
        }
    } elseif ($action === 'overrides') {
        try {
            $stmt = $pdo->query('SELECT * FROM SpecificDateOverride ORDER BY date ASC');
            $overrides = $stmt->fetchAll();
            echo json_encode($overrides);
        } catch (\PDOException $e) {
            echo json_encode(['error' => 'Error al obtener excepciones: ' . $e->getMessage()]);
        }
    } elseif ($action === 'reset_rules') {
        try {
            $pdo->exec('DELETE FROM AvailabilityRule');
            
            $defaultRules = [
                [0, '09:00', '17:00', 0], // Domingo
                [1, '09:00', '17:00', 1], // Lunes
                [2, '09:00', '17:00', 1], // Martes
                [3, '09:00', '17:00', 1], // Miércoles
                [4, '09:00', '17:00', 1], // Jueves
                [5, '09:00', '17:00', 1], // Viernes
                [6, '09:00', '17:00', 0], // Sábado
            ];
            
            $insertStmt = $pdo->prepare('INSERT INTO AvailabilityRule (id, dayOfWeek, startTime, endTime, isActive) VALUES (?, ?, ?, ?, ?)');
            foreach ($defaultRules as $rule) {
                $insertStmt->execute([uniqid('rule_'), $rule[0], $rule[1], $rule[2], $rule[3]]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Reglas reiniciadas correctamente']);
        } catch (\PDOException $e) {
            echo json_encode(['error' => 'Error al reiniciar reglas: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Acción no válida']);
    }
} elseif ($method === 'POST') {
    $action = $_GET['action'] ?? null;
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($action === 'rule') {
        $id = $data['id'] ?? uniqid('rule_');
        $dayOfWeek = $data['dayOfWeek'] ?? 0;
        $startTime = $data['startTime'] ?? '09:00';
        $endTime = $data['endTime'] ?? '17:00';
        $breakStartTime = $data['breakStartTime'] ?? null;
        $breakEndTime = $data['breakEndTime'] ?? null;
        $isActive = isset($data['isActive']) ? (int)$data['isActive'] : 1;
        
        try {
            // Check if exists
            $stmt = $pdo->prepare('SELECT * FROM AvailabilityRule WHERE id = ?');
            $stmt->execute([$id]);
            $existing = $stmt->fetch();
            if ($existing) {
                // If dayOfWeek is not passed in the request body, preserve the existing value in database
                $dayOfWeek = isset($data['dayOfWeek']) ? $data['dayOfWeek'] : $existing['dayOfWeek'];
                $startTime = isset($data['startTime']) ? $data['startTime'] : $existing['startTime'];
                $endTime = isset($data['endTime']) ? $data['endTime'] : $existing['endTime'];
                $isActive = isset($data['isActive']) ? (int)$data['isActive'] : (int)$existing['isActive'];

                $stmt = $pdo->prepare('UPDATE AvailabilityRule SET dayOfWeek = ?, startTime = ?, endTime = ?, isActive = ? WHERE id = ?');
                $stmt->execute([$dayOfWeek, $startTime, $endTime, $isActive, $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO AvailabilityRule (id, dayOfWeek, startTime, endTime, isActive) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$id, $dayOfWeek, $startTime, $endTime, $isActive]);
            }
            echo json_encode(['success' => true]);
        } catch (\PDOException $e) {
            echo json_encode(['error' => 'Error al guardar regla: ' . $e->getMessage()]);
        }
    } elseif ($action === 'override') {
        $id = $data['id'] ?? uniqid('over_');
        $date = $data['date'] ?? null;
        $startTime = $data['startTime'] ?? '09:00';
        $endTime = $data['endTime'] ?? '17:00';
        $isActive = isset($data['isActive']) ? (int)$data['isActive'] : 1;
        
        if (!$date) {
            echo json_encode(['error' => 'Fecha requerida']);
            exit;
        }
        
        try {
             // Check if exists by date
            $stmt = $pdo->prepare('SELECT id FROM SpecificDateOverride WHERE date = ?');
            $stmt->execute([$date]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                $stmt = $pdo->prepare('UPDATE SpecificDateOverride SET startTime = ?, endTime = ?, isActive = ? WHERE date = ?');
                $stmt->execute([$startTime, $endTime, $isActive, $date]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO SpecificDateOverride (id, date, startTime, endTime, isActive) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$id, $date, $startTime, $endTime, $isActive]);
            }
            echo json_encode(['success' => true]);
        } catch (\PDOException $e) {
            echo json_encode(['error' => 'Error al guardar excepción: ' . $e->getMessage()]);
        }
    }
} elseif ($method === 'DELETE') {
    $action = $_GET['action'] ?? null;
    $id = $_GET['id'] ?? null;
    
    if ($action === 'rule' && $id) {
        try {
            $stmt = $pdo->prepare('DELETE FROM AvailabilityRule WHERE id = ?');
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        } catch (\PDOException $e) {
            echo json_encode(['error' => 'Error al eliminar regla: ' . $e->getMessage()]);
        }
    } elseif ($action === 'override' && $id) {
        try {
            $stmt = $pdo->prepare('DELETE FROM SpecificDateOverride WHERE id = ?');
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        } catch (\PDOException $e) {
            echo json_encode(['error' => 'Error al eliminar excepción: ' . $e->getMessage()]);
        }
    }
}
?>

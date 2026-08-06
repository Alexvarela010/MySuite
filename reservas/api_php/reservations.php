<?php
// reservations.php
require_once 'db.php';
require_once 'email.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $action = $_GET['action'] ?? null;
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Handle cancellation from client
    if ($action === 'cancel') {
        $id = $data['id'] ?? null;
        if ($id) {
            try {
                $stmt = $pdo->prepare('UPDATE Reservation SET status = "CANCELLED" WHERE id = ?');
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            } catch (\PDOException $e) {
                echo json_encode(['error' => 'Error al cancelar reserva: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'No ID provided']);
        }
        exit;
    }
    
    // Create reservation
    $date = $data['date'] ?? null;
    $name = $data['name'] ?? null;
    $email = $data['email'] ?? null;
    $phone = $data['phone'] ?? null;
    $serviceId = $data['serviceId'] ?? null;
    
    if (!$date || !$name || !$phone) {
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    try {
        // Generar un ID simple en lugar de cuid
        $id = uniqid('res_');
        $status = 'CONFIRMED';
        $createdAt = date('Y-m-d H:i:s');
        $updatedAt = $createdAt;
        
        $stmt = $pdo->prepare('INSERT INTO Reservation (id, date, name, email, phone, status, serviceId, createdAt, updatedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$id, $date, $name, $email, $phone, $status, $serviceId, $createdAt, $updatedAt]);
        
        if ($email) {
            sendReservationEmail($email, $name, $date, "Servicio de Reserva", $id);
        }
        
        echo json_encode(['success' => true, 'id' => $id]);
    } catch (\PDOException $e) {
        echo json_encode(['error' => 'Error al guardar la reserva: ' . $e->getMessage()]);
    }
} elseif ($method === 'GET') {
    // Get reservations
    try {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM Reservation WHERE id = ?');
            $stmt->execute([$id]);
            $reservations = $stmt->fetchAll();
        } else {
            $stmt = $pdo->query('SELECT * FROM Reservation ORDER BY date DESC');
            $reservations = $stmt->fetchAll();
        }
        echo json_encode($reservations);
    } catch (\PDOException $e) {
        echo json_encode(['error' => 'Error al obtener reservas: ' . $e->getMessage()]);
    }
}
?>

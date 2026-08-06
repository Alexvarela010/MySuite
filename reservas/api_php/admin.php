<?php
// admin.php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $action = $_GET['action'] ?? null;
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($action === 'login') {
        $password = $data['password'] ?? '';
        // Contraseña simple harcodeada por ahora, idealmente debería estar en DB o env
        if ($password === 'admin') { // Cambiar por tu contraseña de admin
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    } elseif ($action === 'cancel_reservation') {
        $id = $data['id'] ?? null;
        if ($id) {
            try {
                $stmt = $pdo->prepare('UPDATE Reservation SET status = "CANCELLED" WHERE id = ?');
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            } catch (\PDOException $e) {
                echo json_encode(['error' => 'Error al cancelar reserva: ' . $e->getMessage()]);
            }
        }
    } elseif ($action === 'delete_reservation') {
        $id = $data['id'] ?? null;
        if ($id) {
            try {
                $stmt = $pdo->prepare('DELETE FROM Reservation WHERE id = ?');
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            } catch (\PDOException $e) {
                echo json_encode(['error' => 'Error al eliminar reserva: ' . $e->getMessage()]);
            }
        }
    }
}
?>

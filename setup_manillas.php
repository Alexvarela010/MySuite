<?php
/**
 * Script para ejecutar la migración de la tabla de manillas
 * Ejecutar: http://tudominio.com/setup_manillas.php
 */

require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Error de conexión a la base de datos");
    }
    
    // Crear tabla manillas_tarifas
    $query = "
        CREATE TABLE IF NOT EXISTS manillas_tarifas (
          id_tarifa INT AUTO_INCREMENT PRIMARY KEY,
          personas_desde INT NOT NULL,
          personas_hasta INT,
          precio DECIMAL(10,2) NOT NULL,
          activo BOOLEAN DEFAULT TRUE,
          creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          UNIQUE KEY unique_rango (personas_desde, personas_hasta)
        )
    ";
    
    $db->exec($query);
    
    // Insertar datos iniciales si la tabla está vacía
    $checkQuery = "SELECT COUNT(*) as count FROM manillas_tarifas";
    $stmt = $db->prepare($checkQuery);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        $insertQuery = "
            INSERT INTO manillas_tarifas (personas_desde, personas_hasta, precio, activo) 
            VALUES 
              (1, 7, 70000, TRUE),
              (8, NULL, 90000, TRUE)
        ";
        $db->exec($insertQuery);
        echo "✓ Tabla 'manillas_tarifas' creada e inicializada correctamente con datos de ejemplo.<br>";
    } else {
        echo "✓ Tabla 'manillas_tarifas' ya existe y contiene datos.<br>";
    }
    
    echo "<br><strong>La migración se complet correctamente.</strong><br>";
    echo "Puedes acceder a la gestión de manillas en: <a href='/admin/manillas.php'>/admin/manillas.php</a>";
    
} catch (Exception $e) {
    echo "✗ Error: " . htmlspecialchars($e->getMessage());
    error_log("Error en setup_manillas.php: " . $e->getMessage());
}
?>


<?php
/**
 * Verificación de la funcionalidad de Manillas
 * Ejecutar: http://tudominio.com/verify_manillas.php
 */

require_once __DIR__ . '/config/database.php';

echo "<h1>Verificación de Funcionalidad de Manillas</h1>";
echo "<hr>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    echo "<h2>✓ Conexión a Base de Datos</h2>";
    echo "<p>Conexión establecida correctamente</p>";
    
    // Verificar tabla
    echo "<h2>✓ Tabla manillas_tarifas</h2>";
    
    $checkTableQuery = "SHOW TABLES LIKE 'manillas_tarifas'";
    $stmt = $db->prepare($checkTableQuery);
    $stmt->execute();
    $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($tableExists) {
        echo "<p>La tabla existe en la base de datos</p>";
        
        // Contar registros
        $countQuery = "SELECT COUNT(*) as count FROM manillas_tarifas";
        $stmt = $db->prepare($countQuery);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Registros en la tabla: <strong>" . $result['count'] . "</strong></p>";
        
        // Mostrar datos
        echo "<h3>Datos Actuales:</h3>";
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Personas Desde</th><th>Personas Hasta</th><th>Precio (COP)</th><th>Activo</th></tr>";
        
        $dataQuery = "SELECT * FROM manillas_tarifas ORDER BY personas_desde";
        $stmt = $db->prepare($dataQuery);
        $stmt->execute();
        $tarifas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($tarifas as $tarifa) {
            $personasHasta = $tarifa['personas_hasta'] ?? 'Sin límite';
            $estado = $tarifa['activo'] ? 'Sí' : 'No';
            echo "<tr>";
            echo "<td>" . $tarifa['id_tarifa'] . "</td>";
            echo "<td>" . $tarifa['personas_desde'] . "</td>";
            echo "<td>" . $personasHasta . "</td>";
            echo "<td>$" . number_format($tarifa['precio'], 0, ',', '.') . "</td>";
            echo "<td>" . $estado . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        throw new Exception("La tabla manillas_tarifas no existe");
    }
    
    // Verificar archivos
    echo "<h2>✓ Archivos Creados</h2>";
    
    $archivos = [
        '/admin/manillas.php' => 'Página de administración de manillas',
        '/app/api/admin/get_manillas_tarifas.php' => 'API para obtener tarifas',
        '/app/api/admin/save_manillas_tarifa.php' => 'API para guardar tarifas',
        '/app/api/admin/delete_manillas_tarifa.php' => 'API para eliminar tarifas'
    ];
    
    $basePath = __DIR__;
    foreach ($archivos as $ruta => $descripcion) {
        $rutaCompleta = $basePath . $ruta;
        $existe = file_exists($rutaCompleta);
        $icono = $existe ? "✓" : "✗";
        $estado = $existe ? "Existe" : "NO ENCONTRADO";
        echo "<p>$icono <strong>$ruta</strong> - $descripcion - <em>$estado</em></p>";
    }
    
    echo "<hr>";
    echo "<h2>✓ Verificación Completada</h2>";
    echo "<p>La funcionalidad de Manillas está lista para usar.</p>";
    echo "<p><a href='/admin/manillas.php'>→ Acceder a la gestión de Manillas</a></p>";
    
} catch (Exception $e) {
    echo "<h2>✗ Error</h2>";
    echo "<p><strong style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</strong></p>";
    echo "<p>Por favor ejecuta primero: <code>php setup_manillas.php</code></p>";
    error_log("Error en verify_manillas.php: " . $e->getMessage());
}
?>


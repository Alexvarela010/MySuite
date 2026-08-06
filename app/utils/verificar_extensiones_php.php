<?php
/**
 * Verificador de Extensiones PHP Requeridas
 * Script para verificar si las extensiones necesarias están habilitadas
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificador de Extensiones PHP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #2470dc;
            padding-bottom: 10px;
        }
        .extension {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid;
        }
        .extension.ok {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .extension.error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .instructions {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .instructions h3 {
            margin-top: 0;
            color: #856404;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .php-info {
            margin-top: 30px;
            padding: 15px;
            background: #e9ecef;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificador de Extensiones PHP Requeridas</h1>
        
        <?php
        $required_extensions = [
            'zip' => 'Necesaria para leer archivos Excel (.xlsx)',
            'xml' => 'Necesaria para procesar archivos XML',
            'gd' => 'Necesaria para procesamiento de imágenes (opcional)'
        ];
        
        $all_ok = true;
        
        foreach ($required_extensions as $ext => $description) {
            $loaded = extension_loaded($ext);
            $class = $loaded ? 'ok' : 'error';
            $icon = $loaded ? '✅' : '❌';
            
            if (!$loaded) {
                $all_ok = false;
            }
            
            echo "<div class='extension $class'>";
            echo "<strong>$icon Extensión: $ext</strong><br>";
            echo "<small>$description</small><br>";
            echo "Estado: " . ($loaded ? "<strong>HABILITADA</strong>" : "<strong>NO HABILITADA</strong>");
            echo "</div>";
        }
        
        // Verificar ZipArchive específicamente
        $ziparchive_ok = class_exists('ZipArchive');
        $class = $ziparchive_ok ? 'ok' : 'error';
        $icon = $ziparchive_ok ? '✅' : '❌';
        
        if (!$ziparchive_ok) {
            $all_ok = false;
        }
        
        echo "<div class='extension $class'>";
        echo "<strong>$icon Clase: ZipArchive</strong><br>";
        echo "<small>Clase PHP necesaria para manejar archivos ZIP</small><br>";
        echo "Estado: " . ($ziparchive_ok ? "<strong>DISPONIBLE</strong>" : "<strong>NO DISPONIBLE</strong>");
        echo "</div>";
        
        if ($all_ok && $ziparchive_ok) {
            echo "<div class='extension ok'>";
            echo "<strong>✅ ¡Todo está correcto!</strong><br>";
            echo "Todas las extensiones necesarias están habilitadas.";
            echo "</div>";
        } else {
            echo "<div class='instructions'>";
            echo "<h3>📝 Instrucciones para Habilitar Extensiones en XAMPP</h3>";
            echo "<ol>";
            echo "<li>Localice el archivo <code>php.ini</code> en: <strong>" . php_ini_loaded_file() . "</strong></li>";
            echo "<li>Abra el archivo con un editor de texto (como Notepad++)</li>";
            echo "<li>Busque las siguientes líneas (pueden estar comentadas con <code>;</code>):";
            echo "<ul>";
            echo "<li><code>;extension=zip</code> → Cambiar a <code>extension=zip</code></li>";
            echo "<li><code>;extension=xml</code> → Cambiar a <code>extension=xml</code> (si existe)</li>";
            echo "<li><code>;extension=gd</code> → Cambiar a <code>extension=gd</code> (opcional)</li>";
            echo "</ul>";
            echo "</li>";
            echo "<li>Si no encuentra las líneas, agréguelas al final del archivo:</li>";
            echo "<pre>extension=zip\nextension=xml\nextension=gd</pre>";
            echo "<li><strong>Guarde el archivo</strong></li>";
            echo "<li><strong>Reinicie Apache</strong> desde el panel de control de XAMPP</li>";
            echo "<li>Vuelva a ejecutar este script para verificar que las extensiones estén habilitadas</li>";
            echo "</ol>";
            echo "</div>";
        }
        ?>
        
        <div class="php-info">
            <h3>ℹ️ Información del Sistema</h3>
            <p><strong>Versión de PHP:</strong> <?php echo PHP_VERSION; ?></p>
            <p><strong>Archivo php.ini usado:</strong> <?php echo php_ini_loaded_file(); ?></p>
            <p><strong>Archivos adicionales .ini:</strong> <?php echo implode(', ', php_ini_scanned_files() ?: ['ninguno']); ?></p>
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="javascript:location.reload()" style="background: #2470dc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                🔄 Recargar Verificación
            </a>
        </div>
    </div>
</body>
</html>

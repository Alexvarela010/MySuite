<?php
header("Content-Type: application/json");

// Validar parámetros
if (!isset($_POST["orderId"], $_POST["currency"], $_POST['amount'])) {
    echo json_encode(["error" => "faltan parametros"]);
    exit;
}

$identificador = trim($_POST['orderId']);
$monto = trim($_POST['amount']); //
$divisa = trim($_POST['currency']);

$llaveSecreta = "kpnruQ3ec0Bfs4wGCYAkhw";

// Concatenación EXACTA
$cadena_concatenada = $identificador . $monto . $divisa . $llaveSecreta;
error_log("Cadena recibida: " . $cadena_concatenada);

$hash = hash("sha256", $cadena_concatenada);

echo json_encode(["hash" => $hash]);

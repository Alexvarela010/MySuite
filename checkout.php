<?php
// checkout.php
require_once __DIR__ . '/config/database.php';

/*
 * ======================================================================
 * PASO 1: DEFINIR LLAVES Y DATOS DE ENTORNO
 * ======================================================================
 */

// ¡MUY IMPORTANTE! Estas llaves deben ser las CORRECTAS (Prueba o Producción).
// Deben ser el mismo par de llaves que usaste en la respuesta anterior.

// Esta es tu Llave de Identidad (va en el JS)
$apiKey = "7FA8bRNE1KvrXfwLiAnibCGonyYSZCXJ1WvbXHgPnGo";

// Esta es tu Llave Secreta (NUNCA debe estar en JS, solo en PHP)
$llaveSecreta = "kpnruQ3ec0Bfs4wGCYAkhw";

// La URL a la que Bold redigirá al usuario después del pago
$redirectionUrl = "https://mysuiteincartagena.com.co/index.php"; // Puedes cambiarla a una página de "gracias"

// La URL a la que Bold redigirá cuando se cancele
$originUrl = "https://mysuiteincartagena.com.co/pago_cancelado.php"; // Puedes cambiarla a una página de "gracias"


/*
 * ======================================================================
 * PASO 2: OBTENER Y VALIDAR DATOS DE LA URL
 * ======================================================================
 */

// Recibimos los datos del correo.
$orderId_from_email = $_GET['orderId'] ?? null;
$percentage_to_calculate = $_GET['percent'] ?? null;
$reservaId_from_email = $_GET['reserva'] ?? null;
$currency_from_email = $_GET['currency'] ?? 'COP'; // Asumimos COP

// --- ¡¡¡INICIO DE SECCIÓN DE SEGURIDAD CRÍTICA!!! ---

// NO confíes en el '?amount=' de la URL. Un usuario podría cambiarlo.
// DEBEMOS consultar la base de datos para obtener el valor real.

if (!$reservaId_from_email || !$orderId_from_email) {
    die("Error: Faltan datos esenciales (ID de reserva o ID de orden) para procesar el pago.");
}

// 1. CONECTA A TU BASE DE DATOS
$reserva_encontrada = false;
$total_de_la_reserva_db = 0;

try {
    // 3. CONECTA A TU BASE DE DATOS (Usando tu método)
    $database = new Database();
    $db = $database->getConnection();

    // 4. PREPARA Y EJECUTA LA CONSULTA
    // (Asegúrate que la tabla 'reservas' y columnas 'total' e 'id_reserva' sean correctas)
    $query = "SELECT total FROM reservas WHERE id_reserva = :id_reserva";
    $stmt = $db->prepare($query);

    // Usamos bindParam para seguridad contra Inyección SQL
    $stmt->bindParam(':id_reserva', $reservaId_from_email);
    $stmt->execute();

    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    // 5. VERIFICA SI SE ENCONTRÓ
    if ($reserva && isset($reserva['total'])) {
        $reserva_encontrada = true;
        $total_de_la_reserva_db = $reserva['total'];
    }

} catch (Exception $e) {
    // Maneja cualquier error de base de datos
    error_log("Error de BD en checkout.php: " . $e->getMessage());
    die("Error: No se pudo verificar la reserva. Contacta a soporte.");
}


if (!$reserva_encontrada) {
    die("Error: No se encontró la reserva #" . htmlspecialchars($reservaId_from_email));
}

// 2. CALCULA EL MONTO CORRECTO
//    Usamos los datos de la BD, no de la URL.
$total_num = (float)$total_de_la_reserva_db; // O $reserva['total']
$amount_a_pagar = (int)round($total_num * $percentage_to_calculate);
$amount_str = (string)$amount_a_pagar; // Bold lo necesita como string

// --- ¡¡¡FIN DE SECCIÓN DE SEGURIDAD!!! ---


/*
 * ======================================================================
 * PASO 3: GENERAR EL HASH DE INTEGRIDAD (en el servidor)
 * ======================================================================
 */

// Usamos los datos verificados (orderId del email, amount calculado, currency)
$cadena_concatenada = $orderId_from_email . $amount_str . $currency_from_email . $llaveSecreta;
$hashHex = hash("sha256", $cadena_concatenada);


/*
 * ======================================================================
 * PASO 4: PREPARAR DATOS PARA JAVASCRIPT
 * ======================================================================
 */

// Creamos un objeto con TODOS los datos que el constructor de Bold necesita.
$checkout_data = [
    "orderId" => $orderId_from_email,
    "amount" => $amount_str,
    "currency" => $currency_from_email,
    "apiKey" => $apiKey,
    "integritySignature" => $hashHex,
    "description" => "Anticipo Reserva #" . htmlspecialchars($reservaId_from_email),
    "redirectionUrl" => $redirectionUrl,
    "originUrl" => $originUrl
];

// Convertimos el array de PHP a un objeto JSON para JavaScript
$json_checkout_data = json_encode($checkout_data);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesando Pago Seguro</title>
    <script src="https://checkout.bold.co/library/boldPaymentButton.js"></script>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f4f4; margin: 0; }
        .loader { text-align: center; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .spinner { border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 15px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        h2 { margin: 0; color: #333; }
        p { color: #666; }
    </style>
</head>
<body>

<div class="loader">
    <div class="spinner"></div>
    <h2>Procesando pago seguro...</h2>
    <p>Por favor, no cierres esta ventana.</p>
</div>

<script>
    try {
        // 1. Tomamos los datos seguros que PHP imprimió
        const paymentData = <?php echo $json_checkout_data; ?>;

        // 2. Creamos la instancia del checkout
        const checkout = new BoldCheckout(paymentData);

        // 3. Abrimos la pasarela de pago INMEDIATAMENTE
        checkout.open();

    } catch (err) {
        // Si algo falla, mostramos un error
        console.error("Error al iniciar Bold Checkout:", err);
        document.body.innerHTML = '<h2>Hubo un error al preparar tu pago.</h2><p>Por favor, intenta de nuevo desde el correo o contacta a soporte.</p>';
    }
</script>

</body>
</html>
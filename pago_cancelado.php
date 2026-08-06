<!DOCTYPE html>
<html>
<head>
    <title>Pago Cancelado | Proceso Finalizado</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f0f4f7; /* Fondo muy claro */
            text-align: center;
        }
        .card {
            padding: 40px;
            border-radius: 12px;
            background-color: white;
            box-shadow: 0 10px 30px rgba(30, 60, 114, 0.15); /* Sombra sutil con el color de tu marca */
            max-width: 400px;
            width: 90%;
            border-top: 5px solid #d9534f; /* Línea roja para indicar cancelación */
        }
        /* Icono de X (Cancelación) */
        .icon {
            color: #d9534f; /* Color Rojo/Cancelación */
            font-size: 50px;
            line-height: 50px;
            margin-bottom: 20px;
            display: inline-block;
            font-weight: bold;
        }
        /* Título */
        h1 {
            color: #1e3c72; /* Color Azul de tu marca */
            font-size: 24px;
            margin-top: 0;
            margin-bottom: 10px;
        }
        /* Mensaje Secundario */
        p {
            color: #666;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2a5298;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        a:hover {
            background-color: #1e3c72;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="icon">❌</div>
    <h1>¡Pago Cancelado!</h1>
    <p>Has cerrado o cancelado el proceso de pago. Tu reserva sigue <strong>pendiente de confirmación</strong>.</p>
    <p>Para completar tu reserva, utiliza el enlace <strong>Pagar Ahora</strong> que recibiste en tu correo electrónico.</p>
       <p style="font-size: small; color: #999;">Puedes cerrar esta pestaña ahora.</p>
</div>
</body>
</html>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso restringido por horario</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
    body {
        font-family: 'DM Sans', sans-serif;
        background: #f8fafc;
        color: #0f172a;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        margin: 0;
    }

    .card {
        background: #fff;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 10px 20px rgba(2, 6, 23, 0.08);
        max-width: 480px;
        width: 92%;
    }

    h1 {
        font-size: 22px;
        margin: 0 0 8px;
    }

    p {
        margin: 0 0 12px;
        line-height: 1.5;
    }

    .time {
        font-weight: 700;
    }
    </style>
</head>

<body>
    <div class="card">
        <h1>Acceso restringido</h1>
        <p>El panel está disponible solo entre <span class="time">{{ sprintf('%02d:00', $start) }}</span> y <span
                class="time">{{ sprintf('%02d:00', $end) }}</span>.</p>
        <p>Contacte al administrador si necesita ampliar su horario.</p>
    </div>
</body>

</html>

<?php
/*
 * VISTA DEL DASHBOARD DE ADMIN (Corregida: Sin Clientes en el menÃº)
 * (Tu HTML + PHP dinÃ¡mico + GrÃ¡ficas)
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $titulo; ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
    body {
        font-family: 'Poppins', Arial, sans-serif;
        background: #f4f8ff; /* Un fondo mÃ¡s suave */
        margin: 0;
        overflow-x: hidden;
    }

    /* â Sidebar */
    .sidebar {
        width: 240px;
        height: 100vh;
        background: #7F00FF;
        position: fixed;
        top: 0;
        left: 0;
        padding-top: 25px;
        color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .sidebar img {
        width: 120px;
        margin-bottom: 25px;
    }
    .sidebar a {
        width: 100%;
        text-decoration: none;
        padding: 14px 20px;
        color: white;
        font-weight: bold;
        text-align: left; /* Alineado a la izquierda */
        transition: 0.3s;
        font-size: 17px;
        padding-left: 30px; /* Espacio para Ã­conos */
    }
    .sidebar a i {
        margin-right: 10px;
    }
    .sidebar a:hover, .sidebar a.active {
        background: #6200c4;
    }
    .sidebar .btn-logout {
        background: #00d8ff;
        color: black;
        font-weight: bold;
        border-radius: 8px;
        width: 80%;
        margin-top: auto;
        margin-bottom: 20px;
        text-align: center;
        padding-left: 14px; /* Resetear padding */
    }
    .sidebar .btn-logout:hover {
        background: #fff;
    }

    /* â Contenido */
    .content {
        margin-left: 240px;
        padding: 30px;
    }
    .title {
        font-size: 32px;
        font-weight: bold;
        color: #7F00FF;
    }

    /* â Cards de EstadÃ­sticas (Nuevas) */
    .stat-card {
        background: #fff;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 0 12px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
    }
    .stat-card-icon {
        font-size: 2.5rem;
        padding: 15px;
        border-radius: 10px;
        margin-right: 15px;
    }
    .stat-card-info h5 {
        font-size: 1rem;
        color: #888;
        font-weight: 600;
        margin-bottom: 5px;
    }
    .stat-card-info .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
    }
    /* Colores de Ã­conos */
    .icon-clientes { background: #e0f7fa; color: #00838f; }
    .icon-ingresos { background: #e8f5e9; color: #2e7d32; }
    .icon-pendientes { background: #fff8e1; color: #f9a825; }

    /* â Cards de GrÃ¡ficas y Tablas */
    .card-section {
        background: #ffffff;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 0 12px rgba(0,0,0,0.08);
        border: none;
        height: 350px; /* MÃ¡s altura para grÃ¡ficas */
    }
    .card-section h5 {
        color: #7F00FF;
        font-weight: bold;
    }
    /* Estilo para la lista de prÃ³ximas reservas */
    .reservas-list {
        list-style: none;
        padding: 0;
        max-height: 280px;
        overflow-y: auto;
    }
    .reservas-list li {
        display: flex;
        justify-content: space-between;
        padding: 12px 5px;
        border-bottom: 1px solid #eee;
    }
    .reservas-list li .nombre { font-weight: 600; color: #333; }
    .reservas-list li .fecha { font-weight: 500; color: var(--rosa); }

</style>
</head>
<body>

<div class="sidebar">
    <img src="<?php echo URL_ROOT; ?>/img/logo_happy_contorno.png" alt="Logo">

    <a href="<?php echo URL_ROOT; ?>/admin" class="active"><i class="bi bi-house-door-fill"></i> Dashboard</a>
    <a href="<?php echo URL_ROOT; ?>/admin/reservas"><i class="bi bi-calendar-fill"></i> Reservas</a>
    <a href="<?php echo URL_ROOT; ?>/admin/codigos"><i class="bi bi-ticket-perforated-fill"></i> Códigos</a>
    <a href="<?php echo URL_ROOT; ?>/admin/notificaciones"><i class="bi bi-bell-fill"></i> Notificaciones</a>

    <a href="<?php echo URL_ROOT; ?>/usuarios/logout" class="btn btn-logout">Cerrar sesión</a>
</div>

<div class="content">
    <p class="title">Panel de Control</p>
    <p class="text-muted fs-5">Bienvenido de nuevo, <?php echo $_SESSION['usuario_nombre']; ?>.</p>

    <div class="row g-4 mt-2">
        <div class="col-lg-4 col-md-6">
            <div class="stat-card">
                <div class="stat-card-icon icon-ingresos"><i class="bi bi-cash-coin"></i></div>
                <div class="stat-card-info">
                    <h5>Ingresos Totales</h5>
                    <span class="stat-number">S/ <?php echo number_format($ingresosTotales, 2); ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stat-card">
                <div class="stat-card-icon icon-clientes"><i class="bi bi-people-fill"></i></div>
                <div class="stat-card-info">
                    <h5>Total Clientes</h5>
                    <span class="stat-number"><?php echo $totalClientes; ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stat-card">
                <div class="stat-card-icon icon-pendientes"><i class="bi bi-clock-history"></i></div>
                <div class="stat-card-info">
                    <h5>Reservas Pendientes</h5>
                    <span class="stat-number"><?php echo $reservasPendientes; ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-3">
        <div class="col-lg-8">
            <div class="card-section">
                <h5>Ingresos (Últimos 7 días)</h5>
                <canvas id="ingresosChart"></canvas>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-section">
                <h5>Próximas Reservas (Confirmadas)</h5>
                <ul class="reservas-list">
                    <?php if (empty($proximasReservas)): ?>
                        <p class="text-muted mt-3">No hay reservas confirmadas próximas.</p>
                    <?php else: ?>
                        <?php foreach($proximasReservas as $reserva): ?>
                            <li>
                                <span class="nombre"><?php echo $reserva->nombre_cumpleanero; ?></span>
                                <span class="fecha"><?php echo date('d/m/Y', strtotime($reserva->fecha)); ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // --- LÃ³gica de la GrÃ¡fica ---
    const ctx = document.getElementById('ingresosChart');
    
    // Obtenemos los datos que el Controlador PHP convirtiÃ³ a JSON
    const labels = <?php echo $chartLabels; ?>;
    const dataValues = <?php echo $chartData; ?>;

    new Chart(ctx, {
        type: 'line', // Tipo de grÃ¡fica
        data: {
            labels: labels,
            datasets: [{
                label: 'Ingresos (S/)',
                data: dataValues,
                fill: true,
                backgroundColor: 'rgba(127, 0, 255, 0.1)',
                borderColor: '#7F00FF',
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        // Formatear el eje Y como Soles (S/)
                        callback: function(value, index, values) {
                            return 'S/ ' + value;
                        }
                    }
                }
            }
        }
    });
</script>

</body>
</html>
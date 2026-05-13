<?php
/*
 * VISTA DE PERFIL (Carga su propio CSS)
 */

// 1. Cargar el header estándar (que ahora carga style.css)
require APP_ROOT . '/views/includes/header.php';
?>

<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/perfil.css">

<div class="container mt-5 mb-5">
    
    <div class="profile-header">
        <h2>¡Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?>!</h2>
        <p class="lead">Aquí puedes ver el estado de todas tus reservas.</p>
    </div>

    <h3>Tus Reservas</h3>
    <hr>

    <?php if (empty($reservas)): ?>
        
        <div class="alert alert-info fs-5" role="alert">
            <i class="bi bi-info-circle-fill"></i> Aún no tienes ninguna reserva registrada.
            <a href="<?php echo URL_ROOT; ?>/reservas/paso1" class="alert-link">¡Haz tu primera reserva aquí!</a>
        </div>

    <?php else: ?>

        <?php foreach($reservas as $reserva): ?>
            
            <?php
                $status_class = '';
                $status_text = '';
                switch ($reserva->estado) {
                    case 'pendiente':
                        $status_class = 'status-pendiente';
                        $status_text = 'Pendiente';
                        break;
                    case 'confirmada':
                        $status_class = 'status-confirmada';
                        $status_text = 'Confirmada';
                        break;
                    case 'cancelada':
                        $status_class = 'status-cancelada';
                        $status_text = 'Cancelada';
                        break;
                    default:
                        $status_class = 'status-pendiente';
                        $status_text = 'Pendiente';
                }
            ?>

            <div class="reserva-card <?php echo $status_class; ?>">
                <div class="reserva-card-body">
                    <h5><?php echo $reserva->paquete_nombre; ?></h5>
                    <p>
                        <strong>Fecha:</strong> 
                        <?php 
                            $fechaObj = new DateTime($reserva->fecha . ' ' . $reserva->hora_inicio);
                            echo $fechaObj->format('d/m/Y \a \l\a\s h:i A');
                        ?>
                    </p>
                    <p><strong>Cumpleañero:</strong> <?php echo $reserva->nombre_cumpleanero; ?> (Cumple <?php echo $reserva->edad_cumpleanero; ?>)</p>
                    <p><strong>Invitados:</strong> <?php echo $reserva->cantidad_personas; ?> personas</p>
                </div>
                <div class="reserva-card-status">
                    <span class="status-badge <?php echo $status_class; ?>">
                        <?php echo $status_text; ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>


    <div class="text-center mt-5">
        <a href="<?php echo URL_ROOT; ?>/usuarios/logout" class="btn-logout">
            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
        </a>
    </div>

</div>

<?php
// Cargar el footer estándar
require APP_ROOT . '/views/includes/footer.php';
?>
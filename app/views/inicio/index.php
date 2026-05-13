<?php 
// VISTA DE INICIO (PÁGINA PRINCIPAL)
// (El header ya se cargó en el paso anterior)
require_once APP_ROOT . '/views/includes/header.php'; 
?>

<section class="hero">
    <div class="hero-content">
        <h1 class="fuente_bouncy">¡HOLAAAAAAAAAAAAAAAAA hacia la diversión!</h1>
        <p>Descubre la emoción sin límites en Happy&Jumping, el lugar donde la alegría nunca se detiene.</p>
    </div>
</section>

<section id="cumple" class="info-section container position-relative">
    <div class="info-card flex-row-reverse">
        <img src="<?php echo URL_ROOT; ?>/img/CUMPLEHJ.jpg" alt="Cumpleaños Happy Jumping">
        <div class="text">
            <h3 class="fuente_bouncy">¡Celebra tu día con estilo!</h3>
            <p>Vive un cumpleaños inolvidable con nuestro <strong>Paquete Jumping Party</strong>. Incluye decoración temática, zona exclusiva, animación, bebidas y pastel personalizado. Un ambiente lleno de color, risas y saltos que harán brillar tu día especial.</p>
            <a href="<?php echo URL_ROOT; ?>/paquetes/cumpleanos" class="btn">Ver paquetes</a>
        </div>
    </div>
</section>

<section id="entradas" class="info-section container position-relative" style="background-color:#fdf9ff;">
    <div class="info-card">
        <img src="<?php echo URL_ROOT; ?>/img/HAPPY INICIO 1.png" alt="Entradas Happy Jumping">
        <div class="text">
            <h3 class="fuente_bouncy">¡Ven a vivir la experiencia Happy&Jumping!</h3>
            <p>Disfruta de un día lleno de energía, saltos y diversión. Observa los detalles de tu entrada y siente la emoción de saltar en un ambiente seguro, lleno de color y buena vibra. ¡Perfecto para grandes y pequeños!</p>
            <a href="<?php echo URL_ROOT; ?>/paquetes/entradas" class="btn">Ver Entradas</a>
        </div>
    </div>
</section>

<?php 
// Carga el footer
require_once APP_ROOT . '/views/includes/footer.php'; 
?>
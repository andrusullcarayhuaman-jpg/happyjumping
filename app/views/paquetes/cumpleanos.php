<?php 
/*
 * VISTA PÚBLICA DE CUMPLEAÑOS
 * (¡CON BUCLE DINÁMICO Y ENLACES CORREGIDOS!)
 */

// 1. Carga el Header (contiene el <head> y el <nav>)
require_once APP_ROOT . '/views/includes/header.php'; 
?>

<header class="header-cumpleanos">
    <h1 class="fuente_bouncy">Paquetes de Cumpleaños</h1>
</header>

<section style="padding-top: 80px; position: relative;">

    <div class="container">

        <?php 
        // Definimos las imágenes estáticas para cada paquete (ya que no están en la BD)
        $imagenesPaquetes = [
            1 => 'trampolin.jpeg',
            2 => 'trampolin2.jpeg',
            3 => 'pared_escalar.jpeg',
            4 => 'HJ1.jpg'
        ];
        
        // Variable para alternar el 'flex-md-row-reverse'
        $alternar = false; 

        foreach($paquetes as $paquete): 
            
            // Asignamos la imagen correspondiente
            $imagen = isset($imagenesPaquetes[$paquete->id_paquete]) ? $imagenesPaquetes[$paquete->id_paquete] : 'default.jpg';
            
            // Alternamos la clase de CSS
            $row_class = $alternar ? 'flex-md-row-reverse' : '';
        ?>

        <div class="package-card">
            <div class="row g-0 align-items-center <?php echo $row_class; ?>">
                
                <div class="col-md-5 package-img" style="background-image: url('<?php echo URL_ROOT; ?>/img/<?php echo $imagen; ?>');"></div>
                
                <div class="col-md-7 package-body">
                    <h3><?php echo $paquete->nombre; ?></h3>
                    
                    <?php if ($paquete->id_paquete == 1): ?>
                        <ul>
                            <li>2 horas de uso del local.</li>
                            <li>Pulsera de 1 hora en camas saltarinas.</li>
                            <li>Dinámicas con premios a cargo de nuestras anfitrionas.</li>
                        </ul>
                    <?php elseif ($paquete->id_paquete == 2): ?>
                        <ul>
                            <li>2 horas y media de diversión total.</li>
                            <li>Pulsera de 1 hora en trampolines.</li>
                            <li>Dinámicas con premios a cargo de nuestras anfitrionas.</li>
                            <li>Glitter Bar y tatuajes neón durante 1 hora.</li>
                            <li>Combo Happy: Popcorn + agua mineral.</li>
                        </ul>
                    <?php elseif ($paquete->id_paquete == 3): ?>
                        <ul>
                            <li>3 horas de uso completo del local.</li>
                            <li>1 hora y media de trampolines, pared de escalar y tirolesa.</li>
                            <li>Dinámicas con premios por anfitrionas.</li>
                            <li>Maquillaje neón y Glitter.</li>
                            <li>Combo Happy: Popcorn + bebida (frugos, agua o gaseosa).</li>
                        </ul>
                    <?php elseif ($paquete->id_paquete == 4): ?>
                        <ul>
                            <li>5 horas de uso exclusivo del local.</li>
                            <li>Acceso a trampolines, tirolesa y pared de escalar.</li>
                            <li>Dinámicas con premios y muñecos inflables.</li>
                            <li>Maquillaje neón y Glitter.</li>
                            <li>Combo Happy: Popcorn + bebida (gaseosa, frugos o agua) + pan con hotdog.</li>
                        </ul>
                    <?php endif; ?>

                    <p class="price">
                        S/<?php echo number_format($paquete->precio_semana, 2); ?> Lunes a Viernes — 
                        S/<?php echo number_format($paquete->precio_fin_semana, 2); ?> Sábado y Domingo (Por persona)
                    </p>
                    
                    <a href="<?php echo URL_ROOT; ?>/reservas/paso1?paquete=<?php echo $paquete->id_paquete; ?>">
                        <button class="btn-contratar mt-2">Reservar Paquete</button>
                    </a>
                </div>
            </div>
        </div>

        <?php 
            // Alternamos la variable para la siguiente tarjeta
            $alternar = !$alternar; 
        endforeach; 
        ?>
        <div class="extra-card text-center text-white p-4 my-5 mx-auto">
            <h4 class="fw-bold mb-2">Cuartos de Experiencia</h4>
            <p class="mb-0">
                Si deseas que tu paquete de cumpleaños incluya nuestros 
                <strong>cuartos de experiencia (cuarto de pintura o cuarto de destrucción)</strong>, 
                solo agregarías <strong>S/. 15 soles MÁS</strong> por cada cuarto al precio del paquete que escojas.
            </p>
        </div>

        <div class="mt-4 mb-5">
            <p class="text-muted" style="font-size: 0.95rem;">
                * Al adquirir cualquiera de nuestros paquetes queda totalmente PROHIBIDO traer bebidas embotelladas.<br>
                * Reserva con mínimo 3 días de anticipación.<br>
                * Los horarios para cumpleaños privados se confirman según disponibilidad del local.<br>
                * Cualquiera de los paquetes puede ser modificado según lo que desee el cliente.
            </p>
        </div>
    </div>
</section>

<?php 
// 2. Carga el Footer
require_once APP_ROOT . '/views/includes/footer.php'; 
?>
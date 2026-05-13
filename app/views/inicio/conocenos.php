<?php 
// 1. Carga el Header (contiene el <head> y el <nav>)
// La variable $datos['active_page'] = 'conocenos' marcará el link como activo
require_once APP_ROOT . '/views/includes/header.php'; 
?>

<header class="header-conocenos">
    <h1 class="fuente_bouncy">Nosotros</h1>
</header>

<section>
    <br>
    <div class="container">
        <div class="about-text">
            <h2 class="fuente_bouncy">Misión</h2>
            <p>Brindar una experiencia recreativa única, segura y divertida que promueva el bienestar físico y 
               emocional a través del juego y la actividad física en trampolines. Nos comprometemos a ofrecer 
               un entorno inclusivo y emocionante para todas las edades, donde las personas puedan mejorar su 
               salud, liberar su energía y crear recuerdos inolvidables con amigos y familiares, siempre bajo 
               los más altos estándares de seguridad y calidad.</p>
        </div>
        <br>
        <div class="about-text">
            <h2 class="fuente_bouncy">Visión</h2>
            <p>Un centro recreativo de trampolines, con una atmósfera energizante y un diseño innovador, 
               destinado a fomentar el ejercicio físico, la creatividad y el entretenimiento a través 
               del salto. El parque será un espacio inclusivo para todas las edades, ofreciendo áreas 
               diferenciadas por niveles de habilidad, así como zonas temáticas para maximizar la diversión.</p>
        </div>
        <br>

        <div class="map-container">
            <h2 class="fuente_bouncy">¡Ubícanos!</h2>
            <iframe
              src="https://www.google.com/maps?q=-6.5058575638378375, -76.35724119120785&z=15&output=embed">
            </iframe>
        </div>
    </div>
</section>

<?php 
// 2. Carga el Footer (contiene el <footer> y los <scripts>)
require_once APP_ROOT . '/views/includes/footer.php'; 
?>
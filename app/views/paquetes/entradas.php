<?php 
// 1. Carga el Header (contiene el <head> y el <nav>)
require_once APP_ROOT . '/views/includes/header.php'; 
?>

<section class="container position-relative py-5">

    <div class="row g-2 justify-content-center">
        <div class="col-md-6">
            <div class="card-grande">
                <img src="<?php echo URL_ROOT; ?>/img/precios.jpeg" alt="Imagen Precios">
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-grande">
                <img src="<?php echo URL_ROOT; ?>/img/promos.jpeg" alt="Imagen Promos">
            </div>
        </div>
    </div>
</section>

<?php 
// 2. Carga el Footer (contiene el <footer> y los <scripts>)
require_once APP_ROOT . '/views/includes/footer.php'; 
?>
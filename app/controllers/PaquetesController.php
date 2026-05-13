<?php
/*
|--------------------------------------------------------------------------
| Controlador de Paquetes (Público)
|--------------------------------------------------------------------------
|
| Muestra las páginas públicas de "Cumpleaños" y "Entradas".
|
*/
class PaquetesController extends Controller {

    private $paqueteModel;

    public function __construct() {
        // Cargamos el modelo que lee la tabla 'paquetes'
        $this->paqueteModel = $this->model('PaqueteModel');
    }

    /**
     * Muestra la página de "Cumpleaños"
     * URL: /paquetes/cumpleanos
     */
    public function cumpleanos() {
        
        // 1. Obtenemos los paquetes desde la BD
        $paquetesDesdeDB = $this->paqueteModel->obtenerPaquetesActivos();
        
        $datos = [
            'titulo' => 'Paquetes de Cumpleaños - Happy&Jumping',
            'paquetes' => $paquetesDesdeDB // Pasamos los paquetes a la vista
        ];

        $this->view('paquetes/cumpleanos', $datos);
    }

    /**
     * Muestra la página de "Entradas"
     * URL: /paquetes/entradas
     */
    public function entradas() {
        $datos = [
            'titulo' => 'Entradas y Promociones - Happy&Jumping'
        ];
        
        // (Asumo que esta es la vista que coincide con tu style.css)
        $this->view('paquetes/entradas', $datos); 
    }
}
?>
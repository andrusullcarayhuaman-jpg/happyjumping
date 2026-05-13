<?php
/*
|--------------------------------------------------------------------------
| Controlador de Inicio (Página principal)
|--------------------------------------------------------------------------
*/

class InicioController extends Controller {
    
    public function __construct() {
        // ... (tu constructor actual)
    }

    /**
     * Método por defecto (index).
     */
    public function index() {
        $datos = [
            'titulo' => 'Happy&Jumping - Diversión sin límites',
            'active_page' => 'inicio' // Para el link activo del nav
        ];

        // Carga la vista 'inicio/index'
        $this->view('inicio/index', $datos);
    }

    /*
     * ======================================================
     * AÑADE ESTE NUEVO MÉTODO PARA "CONÓCENOS"
     * ======================================================
     */
    public function conocenos() {
        $datos = [
            'titulo' => 'Conócenos - Happy&Jumping',
            'active_page' => 'conocenos' // Para el link activo del nav
        ];

        // Carga la vista 'app/views/inicio/conocenos.php'
        $this->view('inicio/conocenos', $datos);
    }
}
?>
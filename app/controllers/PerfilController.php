<?php
/*
|--------------------------------------------------------------------------
| Controlador de Perfil
|--------------------------------------------------------------------------
*/

class PerfilController extends Controller {

    private $perfilModel;

    public function __construct() {
        // 1. Proteger la página: Si no está logueado, ¡fuera!
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: ' . URL_ROOT . '/usuarios/login');
            exit();
        }
        
        // 2. Cargar el modelo
        $this->perfilModel = $this->model('PerfilModel');
    }

    /**
     * Muestra la página principal del perfil (Mis Reservas)
     * URL: /perfil
     */
    public function index() {
        
        // 1. Obtener las reservas del usuario (usando el ID de la sesión)
        $reservas = $this->perfilModel->getReservasPorUsuario($_SESSION['id_usuario']);

        // 2. Preparar los datos para la vista
        $datos = [
            'titulo' => 'Mi Perfil - Happy&Jumping',
            'reservas' => $reservas
        ];

        // 3. Cargar la vista (¡esta sí usa header y footer!)
        $this->view('perfil/index', $datos);
    }

    // (Aquí podríamos añadir más funciones como 'editarDatos', etc.)
}
?>
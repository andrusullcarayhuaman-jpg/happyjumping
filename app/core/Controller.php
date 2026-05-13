<?php
/*
|--------------------------------------------------------------------------
| Controlador Base (¡VERSIÓN CORREGIDA!)
|--------------------------------------------------------------------------
|
| Este es el controlador principal del que todos los demás heredan.
|
*/

class Controller {

    /**
     * Carga el modelo correspondiente.
     */
    public function model($model) {
        // Comprobar si el archivo del modelo existe
        if (file_exists(APP_ROOT . '/models/' . $model . '.php')) {
            require_once APP_ROOT . '/models/' . $model . '.php';
            // Instanciar y devolver el modelo
            return new $model();
        } else {
            die('El modelo "' . $model . '" no existe.');
        }
    }

    /**
     * Carga la vista correspondiente.
     * (¡ESTA ES LA FUNCIÓN QUE ESTABA FALLANDO!)
     */
    public function view($view, $datos = []) {
        
        // --- LA LÍNEA MÁGICA ---
        // Esto toma el array $datos (ej. $datos['paquetes'])
        // y lo convierte en una variable normal (ej. $paquetes)
        // para que la vista pueda usarla.
        extract($datos);
        // -----------------------

        // Comprobar si el archivo de la vista existe
        if (file_exists(APP_ROOT . '/views/' . $view . '.php')) {
            require_once APP_ROOT . '/views/' . $view . '.php';
        } else {
            die('La vista no existe: ' . $view);
        }
    }
}
?>
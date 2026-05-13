<?php
/*
|--------------------------------------------------------------------------
| Clase Núcleo (App Router)
|--------------------------------------------------------------------------
|
| Esta clase es el enrutador principal.
| Lee la URL y carga el controlador y método correspondientes.
| URL: /controlador/metodo/parametros
|
*/

class App {
    
    protected $currentController = 'InicioController';
    protected $currentMethod = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // 2. Buscar el Controlador
        if (isset($url[0]) && file_exists(APP_ROOT . '/controllers/' . ucwords($url[0]) . 'Controller.php')) {
            $this->currentController = ucwords($url[0]) . 'Controller';
            unset($url[0]);
        }

        // 3. Cargar el Controlador
        require_once APP_ROOT . '/controllers/' . $this->currentController . '.php';
        $this->currentController = new $this->currentController;

        // 4. Buscar el Método
        // Convierte kebab-case a camelCase: "codigos-promocion" → "codigosPromocion"
        if (isset($url[1])) {
            $methodName = $this->toCamelCase($url[1]);
            if (method_exists($this->currentController, $methodName)) {
                $this->currentMethod = $methodName;
                unset($url[1]);
            }
        }

        // 5. Obtener los Parámetros
        $this->params = $url ? array_values($url) : [];

        // 6. Ejecutar
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    /**
     * Convierte kebab-case a camelCase
     * Ej: "codigos-promocion" → "codigosPromocion"
     */
    protected function toCamelCase($str) {
        return lcfirst(str_replace('-', '', ucwords($str, '-')));
    }

    protected function parseUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        return [];
    }
}
?>
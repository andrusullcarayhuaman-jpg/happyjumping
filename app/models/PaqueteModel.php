<?php
/*
|--------------------------------------------------------------------------
| Modelo de Paquetes
|--------------------------------------------------------------------------
| IMPORTANTE: Contiene la función obtenerPaquetesActivos()
*/

class PaqueteModel extends Model {

    public function __construct() {
        parent::__construct(); // Conecta a la BD
    }

    /**
     * Obtiene todos los paquetes que están 'activos'
     */
    public function obtenerPaquetesActivos() {
        // Esta es la consulta que trae los paquetes
        $this->query("SELECT * FROM paquetes WHERE estado = 'activo' ORDER BY id_paquete ASC");
        
        $resultados = $this->resultSet();
        
        return $resultados;
    }
}

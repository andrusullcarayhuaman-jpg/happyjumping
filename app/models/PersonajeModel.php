<?php

class PersonajeModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    public function obtenerPersonajes() {
        $this->query("SELECT id_personaje, nombre, descripcion FROM personajes");
        return $this->resultSet();
    }
}
?>
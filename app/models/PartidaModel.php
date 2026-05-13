<?php

class PartidaModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    // Guardar una partida nueva
    public function guardarPartida($id_usuario, $id_personaje, $juego, $puntaje) {
        $this->query("
            INSERT INTO partidas (id_usuario, id_personaje, juego, puntaje)
            VALUES (:id_usuario, :id_personaje, :juego, :puntaje)
        ");
        $this->bind(':id_usuario',   $id_usuario);
        $this->bind(':id_personaje', $id_personaje);
        $this->bind(':juego',        $juego);
        $this->bind(':puntaje',      $puntaje);

        return $this->execute();
    }

    // Obtener puntos totales de un usuario
    public function obtenerPuntosTotales($id_usuario) {
        $this->query("
            SELECT COALESCE(SUM(puntaje), 0) AS puntos_totales
            FROM partidas
            WHERE id_usuario = :id_usuario
        ");
        $this->bind(':id_usuario', $id_usuario);

        $row = $this->single();
        return (int) $row->puntos_totales;
    }

    // Obtener historial de partidas de un usuario
    public function obtenerHistorial($id_usuario) {
        $this->query("
            SELECT p.*, pe.nombre AS nombre_personaje
            FROM partidas p
            JOIN personajes pe ON pe.id_personaje = p.id_personaje
            WHERE p.id_usuario = :id_usuario
            ORDER BY p.fecha_jugada DESC
        ");
        $this->bind(':id_usuario', $id_usuario);

        return $this->resultSet();
    }
}
?>
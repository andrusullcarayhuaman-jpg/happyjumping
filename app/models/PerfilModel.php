<?php
/*
|--------------------------------------------------------------------------
| Modelo de Perfil
|--------------------------------------------------------------------------
|
| Este modelo obtiene los datos del perfil del usuario,
| principalmente sus reservas.
|
*/

class PerfilModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Obtiene todas las reservas de un usuario específico
     */
    public function getReservasPorUsuario($id_usuario) {
        
        // Hacemos un JOIN con 3 tablas para obtener toda la info
        $this->query("
            SELECT 
                r.id_reserva, 
                pg.estado, 
                r.cantidad_personas, 
                r.nombre_cumpleanero, 
                r.edad_cumpleanero,
                p.nombre as paquete_nombre, 
                h.fecha, 
                h.hora_inicio
            FROM reservas as r
            JOIN paquetes as p ON r.id_paquete = p.id_paquete
            JOIN horarios_disponibles as h ON r.id_horario = h.id_horario
            JOIN pagos as pg ON r.id_reserva = pg.id_reserva
            WHERE r.id_usuario = :id_usuario
            ORDER BY h.fecha DESC
        ");
        
        $this->bind(':id_usuario', $id_usuario);
        
        $reservas = $this->resultSet();
        return $reservas;
    }
}
?>
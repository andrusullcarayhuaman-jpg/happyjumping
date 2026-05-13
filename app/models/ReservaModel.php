<?php
/*
|--------------------------------------------------------------------------
| Modelo de Reservas (¡SINTAXIS CORREGIDA!)
|--------------------------------------------------------------------------
|
| Se corrigió $this.bind a $this->bind (flecha).
|
*/

class ReservaModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Crea la reserva completa usando una transacción
     */
    public function crearReservaCompleta($datos) {
        
        try {
            // 1. Iniciar Transacción
            $this->dbh->beginTransaction();

            // 2. Calcular Hora Fin
            $horaInicio = $datos['hora_inicio'];
            $duracionMin = $datos['duracion_minutos'];
            $horaFin = date('H:i:s', strtotime("+$duracionMin minutes", strtotime($horaInicio)));

            // 3. Insertar en 'horarios_disponibles' (para marcarlo como ocupado)
            $this->query("INSERT INTO horarios_disponibles (fecha, hora_inicio, hora_fin, disponible) 
                          VALUES (:fecha, :hora_inicio, :hora_fin, 0)"); // 0 = Ocupado
            $this->bind(':fecha', $datos['fecha']);
            $this->bind(':hora_inicio', $datos['hora_inicio']);
            $this->bind(':hora_fin', $horaFin);
            $this->execute();
            
            $id_horario_nuevo = $this->dbh->lastInsertId();

            // 5. Insertar en 'reservas'
            $this->query("INSERT INTO reservas (id_usuario, id_paquete, id_horario, cantidad_personas, 
                                            extra_pintura, extra_destruccion, nombre_cumpleanero, edad_cumpleanero, observaciones)
                          VALUES (:id_usuario, :id_paquete, :id_horario, :cantidad, 
                                  :extra_pintura, :extra_destruccion, :nombre_cumple, :edad_cumple, :observaciones)");
            
            /*
             * ======================================================
             * ¡AQUÍ ESTABA EL ERROR! (Corregido a ->)
             * ======================================================
             */
            $this->bind(':id_usuario', $datos['id_usuario']);
            $this->bind(':id_paquete', $datos['id_paquete']);
            $this->bind(':id_horario', $id_horario_nuevo);
            $this->bind(':cantidad', $datos['cantidad']);
            $this->bind(':extra_pintura', $datos['extra_pintura'] ? 1 : 0);
            $this->bind(':extra_destruccion', $datos['extra_destruccion'] ? 1 : 0);
            $this->bind(':nombre_cumple', $datos['nombre_cumpleanero']);
            $this->bind(':edad_cumple', $datos['edad_cumpleanero']);
            $this->bind(':observaciones', $datos['observaciones']);
            
            $this->execute();
            
            $id_reserva_nueva = $this->dbh->lastInsertId();

            // 7. Insertar en 'pagos'
            $this->query("INSERT INTO pagos (id_reserva, monto, estado, ruta_captura)
                          VALUES (:id_reserva, :monto, 'pendiente', :ruta_captura)");
                          
            $this->bind(':id_reserva', $id_reserva_nueva);
            $this->bind(':monto', $datos['total_calculado']);
            $this->bind(':ruta_captura', $datos['ruta_captura']);
            $this->execute();

            // 8. ¡Éxito! Confirmar la transacción
            return $this->dbh->commit();

        } catch (Exception $e) {
            // 9. ¡Error! Revertir todo
            $this->dbh->rollBack();
            
            // Muéstrame el error real de la BD
            die('Error de Base de Datos: ' . $e->getMessage()); 
        }
    }
}
?>
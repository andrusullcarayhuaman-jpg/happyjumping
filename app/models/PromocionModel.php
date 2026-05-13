<?php

class PromocionModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    // Obtener todas las promociones
    public function obtenerPromociones() {
        $this->query("
            SELECT id_promocion, nombre, descripcion, puntos_necesarios
            FROM promociones
            ORDER BY puntos_necesarios ASC
        ");
        return $this->resultSet();
    }

    // Canjear una promoción — genera un código único
    public function canjearPromocion($id_usuario, $id_promocion) {

        // 1. Verificar que la promoción exista
        $this->query("SELECT * FROM promociones WHERE id_promocion = :id_promocion");
        $this->bind(':id_promocion', $id_promocion);
        $promo = $this->single();

        if (!$promo) return false;

        // 2. Verificar puntos del usuario — consulta directa, sin instanciar PartidaModel
        $this->query("
            SELECT COALESCE(SUM(puntaje), 0) AS puntos_totales
            FROM partidas
            WHERE id_usuario = :id_usuario
        ");
        $this->bind(':id_usuario', $id_usuario);
        $row    = $this->single();
        $puntos = $row ? (int) $row->puntos_totales : 0;

        if ($puntos < $promo->puntos_necesarios) return false;

        // 3. Generar código único
        $codigo = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

        // 4. Guardar el código
        $this->query("
            INSERT INTO codigos_promocion (id_promocion, id_usuario, codigo)
            VALUES (:id_promocion, :id_usuario, :codigo)
        ");
        $this->bind(':id_promocion', $id_promocion);
        $this->bind(':id_usuario',   $id_usuario);
        $this->bind(':codigo',       $codigo);

        if ($this->execute()) {
            return [
                'codigo'       => $codigo,
                'id_codigo'    => (int) $this->dbh->lastInsertId(),
                'id_promocion' => $id_promocion,
                'id_usuario'   => $id_usuario,
                'estado'       => 'disponible',
                'nombre'       => $promo->nombre,
            ];
        }

        return false;
    }

    // Obtener códigos de un usuario
    public function obtenerCodigosDeUsuario($id_usuario) {
        $this->query("
            SELECT c.*, p.nombre, p.puntos_necesarios
            FROM codigos_promocion c
            JOIN promociones p ON p.id_promocion = c.id_promocion
            WHERE c.id_usuario = :id_usuario
            ORDER BY c.fecha_generacion DESC
        ");
        $this->bind(':id_usuario', $id_usuario);

        return $this->resultSet();
    }
}
?>
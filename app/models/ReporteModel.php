<?php
class ReporteModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Reservas con todos los datos relevantes.
     * Acepta filtros opcionales: estado y rango de fechas.
     */
    public function getReservasParaReporte($estado = 'all', $fecha_desde = '', $fecha_hasta = '') {
        $sql = "SELECT
                    r.id_reserva,
                    h.fecha,
                    h.hora_inicio,
                    h.hora_fin,
                    r.nombre_cumpleanero,
                    r.edad_cumpleanero,
                    r.cantidad_personas,
                    r.observaciones,
                    p.nombre           AS paquete,
                    u.nombre           AS cliente,
                    u.correo           AS correo_cliente,
                    pg.monto,
                    COALESCE(NULLIF(pg.estado,''), 'pendiente') AS estado_pago
                FROM reservas r
                INNER JOIN usuarios u             ON r.id_usuario  = u.id_usuario
                INNER JOIN paquetes p             ON r.id_paquete  = p.id_paquete
                INNER JOIN pagos pg               ON r.id_reserva  = pg.id_reserva
                INNER JOIN horarios_disponibles h ON r.id_horario  = h.id_horario
                WHERE 1=1";

        if ($estado !== 'all' && $estado !== '') {
            $sql .= " AND COALESCE(NULLIF(pg.estado,''), 'pendiente') = :estado";
        }
        if ($fecha_desde !== '') {
            $sql .= " AND h.fecha >= :fecha_desde";
        }
        if ($fecha_hasta !== '') {
            $sql .= " AND h.fecha <= :fecha_hasta";
        }

        $sql .= " ORDER BY h.fecha ASC, h.hora_inicio ASC";

        $this->query($sql);

        if ($estado !== 'all' && $estado !== '') {
            $this->bind(':estado', $estado);
        }
        if ($fecha_desde !== '') {
            $this->bind(':fecha_desde', $fecha_desde);
        }
        if ($fecha_hasta !== '') {
            $this->bind(':fecha_hasta', $fecha_hasta);
        }

        return $this->resultSet();
    }

    /**
     * Resumen de ingresos agrupados por mes.
     */
    public function getResumenIngresosPorMes() {
        $this->query("SELECT
                        DATE_FORMAT(h.fecha, '%Y-%m') AS mes,
                        DATE_FORMAT(h.fecha, '%M %Y') AS mes_nombre,
                        COUNT(r.id_reserva)            AS total_reservas,
                        SUM(pg.monto)                  AS total_ingresos
                      FROM reservas r
                      INNER JOIN pagos pg               ON r.id_reserva = pg.id_reserva
                      INNER JOIN horarios_disponibles h ON r.id_horario  = h.id_horario
                      WHERE pg.estado = 'confirmada'
                      GROUP BY DATE_FORMAT(h.fecha, '%Y-%m')
                      ORDER BY mes ASC");
        return $this->resultSet();
    }

    /**
     * Paquete más solicitado con conteo.
     */
    public function getResumenPorPaquete() {
        $this->query("SELECT
                        p.nombre              AS paquete,
                        COUNT(r.id_reserva)   AS total_reservas,
                        SUM(pg.monto)         AS total_ingresos
                      FROM reservas r
                      INNER JOIN paquetes p ON r.id_paquete = p.id_paquete
                      INNER JOIN pagos pg   ON r.id_reserva = pg.id_reserva
                      GROUP BY p.id_paquete, p.nombre
                      ORDER BY total_reservas DESC");
        return $this->resultSet();
    }

    /**
     * Totales generales para el encabezado del reporte.
     */
    public function getTotalesGenerales() {
        $this->query("SELECT
                        COUNT(r.id_reserva)  AS total_reservas,
                        SUM(CASE WHEN COALESCE(NULLIF(pg.estado,''),'pendiente') = 'confirmada' THEN pg.monto ELSE 0 END) AS ingresos_confirmados,
                        SUM(CASE WHEN COALESCE(NULLIF(pg.estado,''),'pendiente') = 'pendiente'  THEN 1 ELSE 0 END) AS pendientes,
                        SUM(CASE WHEN COALESCE(NULLIF(pg.estado,''),'pendiente') = 'cancelada'  THEN 1 ELSE 0 END) AS canceladas
                      FROM reservas r
                      INNER JOIN pagos pg ON r.id_reserva = pg.id_reserva");
        return $this->single();
    }

    /**
     * Códigos de promoción para el reporte.
     */
    public function getCodigosParaReporte($estado = 'all') {
        $sql = "SELECT
                    c.id_codigo,
                    c.codigo,
                    c.estado,
                    c.fecha_generacion,
                    c.fecha_uso,
                    p.nombre          AS nombre_promocion,
                    p.puntos_necesarios,
                    u.nombre          AS nombre_usuario,
                    u.correo          AS correo_usuario
                FROM codigos_promocion c
                INNER JOIN promociones p ON p.id_promocion = c.id_promocion
                INNER JOIN usuarios    u ON u.id_usuario   = c.id_usuario
                WHERE 1=1";

        if ($estado !== 'all' && $estado !== '') {
            $sql .= " AND c.estado = :estado";
        }
        $sql .= " ORDER BY c.fecha_generacion DESC";

        $this->query($sql);
        if ($estado !== 'all' && $estado !== '') {
            $this->bind(':estado', $estado, PDO::PARAM_STR);
        }
        return $this->resultSet();
    }

    /**
     * Totales resumen de códigos.
     */
    public function getTotalesCodigos() {
        $this->query("SELECT
                        COUNT(*)                                                AS total,
                        SUM(CASE WHEN estado = 'disponible' THEN 1 ELSE 0 END) AS disponibles,
                        SUM(CASE WHEN estado = 'usado'      THEN 1 ELSE 0 END) AS usados
                      FROM codigos_promocion");
        return $this->single();
    }

}

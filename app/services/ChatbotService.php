<?php
/**
 * ChatbotService
 * ------------------------------------------------------------------
 * Servicio principal del chatbot admin de Happy Jumping Peru.
 *
 * Flujo por cada pregunta:
 *   1. Analiza la pregunta para detectar qué datos necesita de la BD.
 *   2. Consulta las tablas reales (reservas, pagos, paquetes, etc.).
 *   3. Envía pregunta + datos reales a Claude vía cURL.
 *   4. Devuelve la respuesta lista para mostrar en el chat.
 *
 * Uso desde el controlador:
 *   $svc = new ChatbotService($this->dbh);
 *   $respuesta = $svc->responder($pregunta, $historial);
 */
class ChatbotService
{
    private PDO    $pdo;
    private string $apiKey;
    private string $apiUrl  = 'https://api.anthropic.com/v1/messages';
    private string $modelo  = 'claude-sonnet-4-6';

    public function __construct(PDO $pdo)
    {
        $this->pdo    = $pdo;
        $this->apiKey = getenv('ANTHROPIC_API_KEY');
    }

    /**
     * Punto de entrada principal.
     *
     * @param  string $pregunta   Mensaje que escribió el admin.
     * @param  array  $historial  [['role'=>'user','content'=>'...'], ...] de la sesión.
     * @return array  ['respuesta' => string, 'contexto' => array]
     */
    public function responder(string $pregunta, array $historial = []): array
    {
        $contexto = $this->obtenerContexto($pregunta);
        $respuesta = $this->llamarClaude($pregunta, $contexto, $historial);

        return [
            'respuesta' => $respuesta,
            'contexto'  => $contexto,
        ];
    }

    // ------------------------------------------------------------------
    // CONTEXTO: detecta qué datos pedir a la BD según la pregunta
    // ------------------------------------------------------------------
    private function obtenerContexto(string $pregunta): array
    {
        $p   = mb_strtolower($pregunta);
        $ctx = [];

        // Reservas / horarios
        if ($this->contiene($p, ['reserva', 'reservas', 'reservado', 'booking',
                                  'fin de semana', 'semana', 'mañana', 'hoy',
                                  'mes', 'fecha', 'próximo', 'siguiente'])) {
            $ctx['reservas_recientes']  = $this->getReservasRecientes();
            $ctx['reservas_hoy']        = $this->getReservasHoy();
            $ctx['reservas_finde']      = $this->getReservasFinDeSemana();
            $ctx['totales']             = $this->getTotalesGenerales();
        }

        // Pagos / ingresos / dinero
        if ($this->contiene($p, ['pago', 'pagos', 'ingreso', 'ingresos', 'monto',
                                  'pendiente', 'confirmad', 'cobr', 'dinero',
                                  'ganancia', 'factura'])) {
            $ctx['totales']             = $this->getTotalesGenerales();
            $ctx['ingresos_por_mes']    = $this->getIngresosPorMes();
            $ctx['pagos_pendientes']    = $this->getPagosPendientes();
        }

        // Paquetes
        if ($this->contiene($p, ['paquete', 'paquetes', 'popular', 'vendido',
                                  'más pedido', 'producto'])) {
            $ctx['paquetes']            = $this->getPaquetesResumen();
        }

        // Clientes / usuarios
        if ($this->contiene($p, ['cliente', 'clientes', 'usuario', 'usuarios',
                                  'persona', 'quién', 'quien'])) {
            $ctx['clientes_frecuentes'] = $this->getClientesFrecuentes();
        }

        // Códigos de descuento
        if ($this->contiene($p, ['código', 'codigos', 'descuento', 'promocion',
                                  'promoción', 'cupon', 'cupón'])) {
            $ctx['codigos']             = $this->getCodigosResumen();
        }

        // Puntos de fidelidad
        if ($this->contiene($p, ['punto', 'puntos', 'fidelidad', 'partida'])) {
            $ctx['puntos']              = $this->getPuntosResumen();
        }

        // Si no detectó nada específico, da un resumen general
        if (empty($ctx)) {
            $ctx['totales']             = $this->getTotalesGenerales();
            $ctx['reservas_recientes']  = $this->getReservasRecientes(5);
        }

        return $ctx;
    }

    // ------------------------------------------------------------------
    // LLAMADA A CLAUDE
    // ------------------------------------------------------------------
    private function llamarClaude(string $pregunta, array $contexto, array $historial): string
    {
        $systemPrompt =
            "Eres el asistente interno de Happy Jumping Peru, una empresa peruana de alquiler " .
            "de inflables y organización de fiestas infantiles. Solo hablas con el administrador " .
            "del negocio. Respondes en español, de forma directa, clara y concisa. " .
            "Cuando el admin pregunta sobre datos del negocio (reservas, pagos, clientes, etc.), " .
            "tienes acceso a los datos reales de la base de datos que se te proporcionan en cada " .
            "mensaje. Usa esos datos para responder con precisión. " .
            "Si el admin pide redactar un mensaje o correo, hazlo directamente. " .
            "Si no tienes suficientes datos para responder algo, dilo claramente. " .
            "No inventes números ni datos que no estén en el contexto proporcionado. " .
            "Respuestas cortas (2-4 líneas) salvo que pidan algo extenso como un correo.";

        // Construir el historial de mensajes
        $messages = [];
        foreach ($historial as $h) {
            $messages[] = ['role' => $h['role'], 'content' => $h['content']];
        }

        // Mensaje actual con el contexto de BD pegado
        $contenidoUsuario = $pregunta;
        if (!empty($contexto)) {
            $contenidoUsuario .= "\n\n[Datos reales de la BD en este momento]\n" .
                                 json_encode($contexto, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        $messages[] = ['role' => 'user', 'content' => $contenidoUsuario];

        $payload = [
            'model'      => $this->modelo,
            'max_tokens' => 800,
            'system'     => $systemPrompt,
            'messages'   => $messages,
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT    => 30,
        ]);

        $resp    = curl_exec($ch);
        $errCurl = curl_error($ch);
        $http    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errCurl) {
            return 'Error de conexión con la IA: ' . $errCurl;
        }

        $data = json_decode($resp, true);

        if ($http !== 200 || empty($data['content'][0]['text'])) {
            $detalle = $data['error']['message'] ?? 'Error HTTP ' . $http;
            return 'Error de la IA: ' . $detalle;
        }

        return trim($data['content'][0]['text']);
    }

    // ------------------------------------------------------------------
    // QUERIES — datos reales de la BD
    // Las tablas y columnas coinciden exactamente con ReporteModel.php
    // ------------------------------------------------------------------

    private function getReservasRecientes(int $limite = 10): array
    {
        $stmt = $this->pdo->prepare("
            SELECT r.id_reserva, h.fecha, h.hora_inicio, h.hora_fin,
                   r.nombre_cumpleanero, r.cantidad_personas,
                   p.nombre AS paquete, u.nombre AS cliente, u.correo,
                   pg.monto,
                   COALESCE(NULLIF(pg.estado,''), 'pendiente') AS estado_pago
            FROM reservas r
            INNER JOIN usuarios u             ON r.id_usuario = u.id_usuario
            INNER JOIN paquetes p             ON r.id_paquete = p.id_paquete
            INNER JOIN pagos pg               ON r.id_reserva = pg.id_reserva
            INNER JOIN horarios_disponibles h ON r.id_horario = h.id_horario
            ORDER BY h.fecha DESC, h.hora_inicio DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getReservasHoy(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT r.id_reserva, h.fecha, h.hora_inicio, h.hora_fin,
                   r.nombre_cumpleanero, p.nombre AS paquete,
                   u.nombre AS cliente, pg.monto,
                   COALESCE(NULLIF(pg.estado,''), 'pendiente') AS estado_pago
            FROM reservas r
            INNER JOIN usuarios u             ON r.id_usuario = u.id_usuario
            INNER JOIN paquetes p             ON r.id_paquete = p.id_paquete
            INNER JOIN pagos pg               ON r.id_reserva = pg.id_reserva
            INNER JOIN horarios_disponibles h ON r.id_horario = h.id_horario
            WHERE h.fecha = CURDATE()
            ORDER BY h.hora_inicio ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getReservasFinDeSemana(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT r.id_reserva, h.fecha, h.hora_inicio, h.hora_fin,
                   r.nombre_cumpleanero, p.nombre AS paquete,
                   u.nombre AS cliente, pg.monto,
                   COALESCE(NULLIF(pg.estado,''), 'pendiente') AS estado_pago
            FROM reservas r
            INNER JOIN usuarios u             ON r.id_usuario = u.id_usuario
            INNER JOIN paquetes p             ON r.id_paquete = p.id_paquete
            INNER JOIN pagos pg               ON r.id_reserva = pg.id_reserva
            INNER JOIN horarios_disponibles h ON r.id_horario = h.id_horario
            WHERE YEARWEEK(h.fecha, 1) = YEARWEEK(CURDATE(), 1)
              AND DAYOFWEEK(h.fecha) IN (1, 7)
            ORDER BY h.fecha ASC, h.hora_inicio ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTotalesGenerales(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(r.id_reserva) AS total_reservas,
                SUM(CASE WHEN COALESCE(NULLIF(pg.estado,''),'pendiente') = 'confirmada'
                         THEN pg.monto ELSE 0 END) AS ingresos_confirmados,
                SUM(CASE WHEN COALESCE(NULLIF(pg.estado,''),'pendiente') = 'confirmada'
                         THEN 1 ELSE 0 END) AS confirmadas,
                SUM(CASE WHEN COALESCE(NULLIF(pg.estado,''),'pendiente') = 'pendiente'
                         THEN 1 ELSE 0 END) AS pendientes,
                SUM(CASE WHEN COALESCE(NULLIF(pg.estado,''),'pendiente') = 'cancelada'
                         THEN 1 ELSE 0 END) AS canceladas
            FROM reservas r
            INNER JOIN pagos pg ON r.id_reserva = pg.id_reserva
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function getIngresosPorMes(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT DATE_FORMAT(h.fecha, '%Y-%m') AS mes,
                   DATE_FORMAT(h.fecha, '%M %Y') AS mes_nombre,
                   COUNT(r.id_reserva)            AS reservas,
                   SUM(pg.monto)                  AS ingresos
            FROM reservas r
            INNER JOIN pagos pg               ON r.id_reserva = pg.id_reserva
            INNER JOIN horarios_disponibles h ON r.id_horario  = h.id_horario
            WHERE pg.estado = 'confirmada'
              AND h.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(h.fecha, '%Y-%m')
            ORDER BY mes DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getPagosPendientes(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT r.id_reserva, h.fecha, u.nombre AS cliente,
                   u.correo, p.nombre AS paquete, pg.monto
            FROM reservas r
            INNER JOIN usuarios u             ON r.id_usuario = u.id_usuario
            INNER JOIN paquetes p             ON r.id_paquete = p.id_paquete
            INNER JOIN pagos pg               ON r.id_reserva = pg.id_reserva
            INNER JOIN horarios_disponibles h ON r.id_horario  = h.id_horario
            WHERE COALESCE(NULLIF(pg.estado,''), 'pendiente') = 'pendiente'
            ORDER BY h.fecha ASC
            LIMIT 20
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getPaquetesResumen(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.nombre AS paquete,
                   COUNT(r.id_reserva) AS total_reservas,
                   SUM(pg.monto)       AS total_ingresos
            FROM reservas r
            INNER JOIN paquetes p ON r.id_paquete = p.id_paquete
            INNER JOIN pagos pg   ON r.id_reserva = pg.id_reserva
            GROUP BY p.id_paquete, p.nombre
            ORDER BY total_reservas DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getClientesFrecuentes(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT u.nombre, u.correo,
                   COUNT(r.id_reserva) AS total_reservas,
                   SUM(pg.monto)       AS total_gastado
            FROM reservas r
            INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
            INNER JOIN pagos pg   ON r.id_reserva = pg.id_reserva
            WHERE COALESCE(NULLIF(pg.estado,''), 'pendiente') = 'confirmada'
            GROUP BY u.id_usuario, u.nombre, u.correo
            ORDER BY total_reservas DESC
            LIMIT 10
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getCodigosResumen(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) AS total,
                   SUM(CASE WHEN estado = 'disponible' THEN 1 ELSE 0 END) AS disponibles,
                   SUM(CASE WHEN estado = 'usado'      THEN 1 ELSE 0 END) AS usados
            FROM codigos_promocion
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function getPuntosResumen(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT u.nombre, u.correo, SUM(pt.puntaje) AS puntos_totales
            FROM partidas pt
            INNER JOIN usuarios u ON pt.id_usuario = u.id_usuario
            GROUP BY u.id_usuario, u.nombre, u.correo
            ORDER BY puntos_totales DESC
            LIMIT 10
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ------------------------------------------------------------------
    // HELPERS
    // ------------------------------------------------------------------
    private function contiene(string $texto, array $palabras): bool
    {
        foreach ($palabras as $p) {
            if (str_contains($texto, $p)) {
                return true;
            }
        }
        return false;
    }
}

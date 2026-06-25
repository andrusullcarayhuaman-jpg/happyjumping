<?php
/**
 * ChatbotController
 * ------------------------------------------------------------------
 * Maneja el endpoint AJAX del chatbot admin.
 *
 * Rutas:
 *   POST /chatbot/enviar   → recibe pregunta, devuelve JSON con respuesta
 *   GET  /chatbot/historial → últimas N conversaciones guardadas
 *
 * Copiar a: app/controllers/ChatbotController.php
 * Agregar al tope: require_once APP_ROOT . '/services/ChatbotService.php';
 */
class ChatbotController extends Controller
{
    private $chatbotModel; // Para guardar historial

    public function __construct()
    {
        // Solo admins pueden usar el chatbot
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autorizado']);
            exit();
        }

        require_once APP_ROOT . '/services/ChatbotService.php';
    }

    /**
     * POST /chatbot/enviar
     * Body JSON: { "pregunta": "...", "historial": [...] }
     */
    public function enviar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $body     = json_decode(file_get_contents('php://input'), true);
        $pregunta = trim($body['pregunta'] ?? '');
        $historial = $body['historial'] ?? [];

        if ($pregunta === '') {
            echo json_encode(['error' => 'Pregunta vacía']);
            exit();
        }

        // Limitar historial a últimas 6 rondas para no inflar el prompt
        $historial = array_slice($historial, -12);

        // Obtener la conexión PDO desde el model base
        // (ChatbotService necesita PDO directo, no el wrapper Model)
        $tempModel = $this->model('ReporteModel');
        $pdo       = $tempModel->dbh; // dbh es protected pero heredado → accesible desde subclase

        $svc       = new ChatbotService($pdo);
        $resultado = $svc->responder($pregunta, $historial);

        // Guardar en historial (tabla chatbot_historial)
        $pdo->prepare("
            INSERT INTO chatbot_historial (pregunta, respuesta, datos_contexto, fecha)
            VALUES (:pregunta, :respuesta, :contexto, NOW())
        ")->execute([
            'pregunta'  => $pregunta,
            'respuesta' => $resultado['respuesta'],
            'contexto'  => json_encode($resultado['contexto'], JSON_UNESCAPED_UNICODE),
        ]);

        echo json_encode([
            'respuesta' => $resultado['respuesta'],
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * GET /chatbot/historial
     * Devuelve las últimas 20 conversaciones guardadas.
     */
    public function historial(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $tempModel = $this->model('ReporteModel');
        $stmt      = $tempModel->dbh->prepare("
            SELECT pregunta, respuesta, fecha
            FROM chatbot_historial
            ORDER BY fecha DESC
            LIMIT 20
        ");
        $stmt->execute();
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['historial' => $filas], JSON_UNESCAPED_UNICODE);
        exit();
    }
}

<?php
/**
 * ChatbotController — Happy Jumping Peru
 * Rutas:
 *   POST /chatbot/enviar     → recibe pregunta, devuelve JSON
 *   GET  /chatbot/historial  → últimas conversaciones
 */
class ChatbotController extends Controller
{
    public function __construct()
    {
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
     */
    public function enviar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $body      = json_decode(file_get_contents('php://input'), true);
        $pregunta  = trim($body['pregunta'] ?? '');
        $historial = $body['historial'] ?? [];

        if ($pregunta === '') {
            echo json_encode(['error' => 'Pregunta vacía']);
            exit();
        }

        // Limitar historial a últimas 6 rondas
        $historial = array_slice($historial, -12);

        // Crear PDO directo usando las constantes globales (evita problema de dbh protected)
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        $pdo->exec("SET time_zone = '-05:00'");

        $svc       = new ChatbotService($pdo);
        $resultado = $svc->responder($pregunta, $historial);

        // Guardar en historial
        try {
            $pdo->prepare("
                INSERT INTO chatbot_historial (pregunta, respuesta, datos_contexto, fecha)
                VALUES (:pregunta, :respuesta, :contexto, NOW())
            ")->execute([
                'pregunta'  => $pregunta,
                'respuesta' => $resultado['respuesta'],
                'contexto'  => json_encode($resultado['contexto'], JSON_UNESCAPED_UNICODE),
            ]);
        } catch (Exception $e) {
            // Si la tabla no existe aún, no bloquear la respuesta
        }

        echo json_encode(['respuesta' => $resultado['respuesta']], JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * GET /chatbot/historial
     */
    public function historial(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        $pdo->exec("SET time_zone = '-05:00'");

        try {
            $stmt = $pdo->prepare("
                SELECT pregunta, respuesta, fecha
                FROM chatbot_historial
                ORDER BY fecha DESC LIMIT 20
            ");
            $stmt->execute();
            $filas = $stmt->fetchAll();
        } catch (Exception $e) {
            $filas = [];
        }

        echo json_encode(['historial' => $filas], JSON_UNESCAPED_UNICODE);
        exit();
    }
}

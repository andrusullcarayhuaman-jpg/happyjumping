<?php
class AdminController extends Controller {

    private $adminModel;

    public function __construct() {
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
            if (isset($_SESSION['id_usuario'])) {
                header('Location: ' . URL_ROOT . '/perfil');
            } else {
                header('Location: ' . URL_ROOT . '/usuarios/login');
            }
            exit();
        }
        $this->adminModel = $this->model('AdminModel');
    }

    public function index() {
        $ingresosChart = $this->adminModel->getIngresosUltimos7Dias();
        $chartLabels = [];
        $chartData   = [];
        foreach ($ingresosChart as $dia) {
            $chartLabels[] = date('d/m', strtotime($dia->dia));
            $chartData[]   = $dia->total_dia;
        }
        $datos = [
            'titulo'             => 'Dashboard - Admin',
            'totalClientes'      => $this->adminModel->contarTotalClientes(),
            'ingresosTotales'    => $this->adminModel->sumarIngresosTotales(),
            'reservasPendientes' => $this->adminModel->contarReservasPendientes(),
            'proximasReservas'   => $this->adminModel->getProximasReservas(),
            'chartLabels'        => json_encode($chartLabels),
            'chartData'          => json_encode($chartData)
        ];
        $this->view('admin/index', $datos);
    }

    public function reservas() {
        $mensaje = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['id_reserva'])
            && isset($_POST['estado'])) {

            $id_reserva   = (int) $_POST['id_reserva'];
            $nuevo_estado = trim($_POST['estado']);
            $estados_ok   = ['pendiente', 'confirmada', 'cancelada'];

            if ($id_reserva > 0 && in_array($nuevo_estado, $estados_ok)) {

                $ok = $this->adminModel->actualizarEstadoReserva($id_reserva, $nuevo_estado);

                if ($ok) {
                    $mensaje = [
                        'tipo'  => 'success',
                        'texto' => 'Reserva #' . $id_reserva . ' cambiada a <strong>' . strtoupper($nuevo_estado) . '</strong>.'
                    ];

                    if ($nuevo_estado === 'confirmada') {
                        $reserva = $this->adminModel->getReservaPorId($id_reserva);
                        if ($reserva) {
                            require_once APP_ROOT . '/core/Mailer.php';
                            $enviado = Mailer::enviarConfirmacion(
                                $reserva,
                                $reserva->correo_cliente,
                                $reserva->nombre_cliente
                            );
                            if ($enviado) {
                                $mensaje['texto'] .= ' <small class="ms-2">✉️ Correo enviado al cliente.</small>';
                            } else {
                                $mensaje['texto'] .= ' <small class="ms-2 text-warning">⚠️ No se pudo enviar el correo.</small>';
                            }
                        }
                    }

                } else {
                    $mensaje = [
                        'tipo'  => 'danger',
                        'texto' => 'No se pudo guardar en la base de datos.'
                    ];
                }

            } else {
                $mensaje = [
                    'tipo'  => 'warning',
                    'texto' => 'Datos invalidos — ID: ' . $id_reserva . ', Estado: "' . htmlspecialchars($nuevo_estado) . '"'
                ];
            }
        }

        $estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : 'all';
        $reservas      = $this->adminModel->getReservasFiltradas($estado_filtro);

        $datos = [
            'titulo'        => 'Gestionar Reservas',
            'reservas'      => $reservas,
            'estado_filtro' => $estado_filtro,
            'mensaje'       => $mensaje
        ];
        $this->view('admin/reservas', $datos);
    }

    public function actualizarEstadoReserva($id_reserva = null) {
        header('Location: ' . URL_ROOT . '/admin/reservas');
        exit;
    }

    // ── CÓDIGOS DE PROMOCIÓN ─────────────────────────────────────────────────
    public function codigos() {
        $mensaje = null;

        // POST: cambiar estado de un código
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['id_codigo'])
            && isset($_POST['estado'])) {

            $id_codigo    = (int) $_POST['id_codigo'];
            $nuevo_estado = trim($_POST['estado']);
            $estados_ok   = ['disponible', 'usado'];

            if ($id_codigo > 0 && in_array($nuevo_estado, $estados_ok)) {
                $ok = $this->adminModel->actualizarEstadoCodigo($id_codigo, $nuevo_estado);
                if ($ok) {
                    $mensaje = [
                        'tipo'  => 'success',
                        'texto' => 'Código #' . $id_codigo . ' marcado como <strong>' . strtoupper($nuevo_estado) . '</strong>.'
                    ];
                } else {
                    $mensaje = [
                        'tipo'  => 'danger',
                        'texto' => 'No se pudo actualizar el código.'
                    ];
                }
            } else {
                $mensaje = [
                    'tipo'  => 'warning',
                    'texto' => 'Datos inválidos.'
                ];
            }
        }

        $estado_filtro      = isset($_GET['estado']) ? $_GET['estado']       : 'all';
        $buscar             = isset($_GET['buscar'])  ? trim($_GET['buscar']) : '';
        $codigo_filtro      = isset($_GET['codigo'])  ? trim(strtoupper($_GET['codigo'])) : '';
        $codigos            = $this->adminModel->getCodigosFiltrados($estado_filtro, $buscar, $codigo_filtro);

        $datos = [
            'titulo'        => 'Códigos Canjeados - Admin',
            'codigos'       => $codigos,
            'estado_filtro' => $estado_filtro,
            'buscar'        => $buscar,
            'codigo_filtro' => $codigo_filtro,
            'mensaje'       => $mensaje,
        ];
        $this->view('admin/codigos', $datos);
    }
    // ────────────────────────────────────────────────────────────────────────

    public function notificaciones() {
        $resultado       = null;
        $mensajeAnterior = '';

        $plantillas = [
            ['emoji' => '❤️',  'titulo' => 'San Valentín',  'mensaje' => '¡Hoy por San Valentín, 2x1 en entradas! ❤️'],
            ['emoji' => '🎉',  'titulo' => 'Promo del día',  'mensaje' => '¡Hoy lunes: 2x1 en todas las tarifas! 🎉'],
            ['emoji' => '🎂',  'titulo' => 'Cumpleaños',     'mensaje' => '¡Celebra tu cumple con nosotros y obten descuentos! 🎂'],
            ['emoji' => '🏆',  'titulo' => 'Fin de semana',  'mensaje' => '¡Viernes! 50% de descuento en nuestras tarifas. 🏆'],
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['mensaje'] ?? ''))) {
            $mensaje = trim(strip_tags($_POST['mensaje']));

            if (strlen($mensaje) > 200) {
                $resultado = ['tipo' => 'warning', 'texto' => 'El mensaje no puede superar los 200 caracteres.'];
            } else {
                require_once APP_ROOT . '/../vendor/firebase/Firebase.php';
                require_once APP_ROOT . '/config/firebase.php';

                $firebase = new Firebase(FIREBASE_DB_URL, FIREBASE_CREDENTIALS);

                $ok = $firebase->push('notificaciones', [
                    'mensaje'   => $mensaje,
                    'timestamp' => time(),
                    'leido'     => false,
                ]);

                if ($ok) {
                    $this->adminModel->guardarNotificacion($mensaje, $_SESSION['usuario_id'] ?? 0);
                    $resultado = ['tipo' => 'success', 'texto' => '✅ Notificación enviada correctamente a la app móvil.'];
                } else {
                    $mensajeAnterior = $mensaje;
                    $resultado = ['tipo' => 'danger', 'texto' => '❌ Error al conectar con Firebase. Revisá las credenciales.'];
                }
            }
        }

        $datos = [
            'titulo'          => 'Notificaciones - Admin',
            'plantillas'      => $plantillas,
            'historial'       => $this->adminModel->getHistorialNotificaciones(),
            'resultado'       => $resultado,
            'mensajeAnterior' => $mensajeAnterior,
        ];

        $this->view('admin/notificaciones', $datos);
    }

    // ── CORREOS MASIVOS ──────────────────────────────────────────────────────
    public function correos() {
        require_once APP_ROOT . '/core/Mailer.php';

        $resultado = null;

        // ─── POST: enviar correos ────────────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plantilla'])) {

            $plantilla          = trim($_POST['plantilla']);
            $destinatarios_ids  = isset($_POST['destinatarios']) ? (array)$_POST['destinatarios'] : [];

            // Si se marcó "todos", obtener todos los correos
            if (isset($_POST['todos']) && $_POST['todos'] === '1') {
                $clientes = $this->adminModel->getClientesParaCorreo('');
            } else {
                if (empty($destinatarios_ids)) {
                    $resultado = ['tipo' => 'warning', 'texto' => '⚠️ Debes seleccionar al menos un destinatario.'];
                    $clientes = [];
                } else {
                    $clientes_todos = $this->adminModel->getClientesParaCorreo('');
                    $clientes = array_filter($clientes_todos, function($c) use ($destinatarios_ids) {
                        return in_array($c->id_usuario, $destinatarios_ids);
                    });
                }
            }

            if ($resultado === null && empty($clientes)) {
                $resultado = ['tipo' => 'warning', 'texto' => '⚠️ No hay destinatarios para enviar.'];
            }

            if ($resultado === null) {

                $enviados   = 0;
                $fallidos   = 0;
                $asunto_log = '';

                foreach ($clientes as $cliente) {
                    try {
                        $ok = false;

                        switch ($plantilla) {

                            case 'recordatorio':
                                $proximas = $this->adminModel->getClientesConReservaProxima();
                                $reserva  = null;
                                foreach ($proximas as $p) {
                                    if ($p->id_usuario == $cliente->id_usuario) { $reserva = $p; break; }
                                }
                                if ($reserva) {
                                    $ok = Mailer::enviarRecordatorioReserva($cliente->correo, $cliente->nombre, $reserva);
                                    $asunto_log = '🎂 Recordatorio de reserva próxima';
                                }
                                break;

                            case 'promo':
                                $detalle = isset($_POST['detalle_promo']) ? trim($_POST['detalle_promo']) : '';
                                if (empty($detalle)) { $detalle = '¡Oferta especial disponible esta semana!'; }
                                $ok = Mailer::enviarPromoEspecial($cliente->correo, $cliente->nombre, $detalle);
                                $asunto_log = '🎉 Promoción especial';
                                break;

                            case 'codigo':
                                $codigo   = isset($_POST['codigo_descuento'])   ? strtoupper(trim($_POST['codigo_descuento'])) : 'HAPPY10';
                                $desc_cod = isset($_POST['descripcion_codigo']) ? trim($_POST['descripcion_codigo'])  : '10% de descuento en tu próxima reserva.';
                                $ok = Mailer::enviarCodigoDescuento($cliente->correo, $cliente->nombre, $codigo, $desc_cod);
                                $asunto_log = '🎁 Código de descuento: ' . $codigo;
                                break;

                            case 'puntos':
                                $puntos = isset($cliente->puntos) ? $cliente->puntos : 0;
                                $ok = Mailer::enviarRecordatorioPuntos($cliente->correo, $cliente->nombre, $puntos);
                                $asunto_log = '🏆 Recordatorio de puntos acumulados';
                                break;

                            case 'personalizado':
                                $asunto_custom = isset($_POST['asunto_custom']) ? trim($_POST['asunto_custom']) : 'Mensaje de Happy Jumping Peru';
                                $cuerpo_custom = isset($_POST['cuerpo_custom'])  ? trim($_POST['cuerpo_custom'])  : '';
                                if (empty($cuerpo_custom)) {
                                    $resultado = ['tipo' => 'warning', 'texto' => '⚠️ El cuerpo del mensaje no puede estar vacío.'];
                                    break;
                                }
                                $ok = Mailer::enviarMensajePersonalizado($cliente->correo, $cliente->nombre, $asunto_custom, $cuerpo_custom);
                                $asunto_log = $asunto_custom;
                                break;
                        }

                        if ($ok) $enviados++; else $fallidos++;

                    } catch (Exception $e) {
                        $fallidos++;
                    }
                }

                if ($resultado === null) {
                    if ($enviados > 0 && !empty($asunto_log)) {
                        $this->adminModel->guardarHistorialCorreo(
                            $_SESSION['usuario_id'] ?? 0,
                            $plantilla,
                            $enviados,
                            $asunto_log
                        );
                    }

                    if ($enviados > 0 && $fallidos === 0) {
                        $resultado = ['tipo' => 'success', 'texto' => "✅ Se enviaron <strong>{$enviados}</strong> correos correctamente."];
                    } elseif ($enviados > 0) {
                        $resultado = ['tipo' => 'warning', 'texto' => "⚠️ Se enviaron <strong>{$enviados}</strong> correos. Fallaron <strong>{$fallidos}</strong>."];
                    } else {
                        $resultado = ['tipo' => 'danger', 'texto' => "❌ No se pudo enviar ningún correo. Revisa la configuración SMTP."];
                    }
                }
            }
        }

        $buscar_clientes = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        $clientes_lista  = $this->adminModel->getClientesParaCorreo($buscar_clientes);
        $historial       = $this->adminModel->getHistorialCorreos();

        $datos = [
            'titulo'    => 'Correos Masivos — Admin',
            'clientes'  => $clientes_lista,
            'historial' => $historial,
            'buscar'    => $buscar_clientes,
            'resultado' => $resultado,
        ];

        $this->view('admin/correos', $datos);
    }
}
<?php
class UsuariosController extends Controller {

    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = $this->model('UsuarioModel');
    }

    public function login() {

        // Si ya está logueado, redirigir según rol
        if (isset($_SESSION['id_usuario'])) {
            if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin') {
                header('Location: ' . URL_ROOT . '/admin');
            } else {
                header('Location: ' . URL_ROOT . '/perfil');
            }
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $datos = [
                'titulo'         => 'Iniciar Sesion - Happy&Jumping',
                'correo'         => trim($_POST['correo']),
                'password'       => trim($_POST['password']),
                'correo_error'   => '',
                'password_error' => ''
            ];

            if (empty($datos['correo']))   $datos['correo_error']   = 'Por favor, ingresa tu correo.';
            elseif (strtolower(trim($datos['correo'])) !== 'admin@happyjumping.com' && !str_ends_with(strtolower($datos['correo']), '@gmail.com')) $datos['correo_error'] = 'Solo se aceptan correos @gmail.com.';
            if (empty($datos['password'])) $datos['password_error'] = 'Por favor, ingresa tu contrasena.';

            if (empty($datos['correo_error']) && empty($datos['password_error'])) {

                $loggedInUser = $this->usuarioModel->login($datos['correo'], $datos['password']);

                if ($loggedInUser) {
                    $this->createUsuarioSession($loggedInUser);

                    if ($loggedInUser->rol === 'admin') {
                        header('Location: ' . URL_ROOT . '/admin');
                    } else {
                        header('Location: ' . URL_ROOT . '/perfil');
                    }
                    exit();
                } else {
                    $datos['password_error'] = 'Correo o contrasena incorrectos. Intenta de nuevo.';
                    $this->view('usuarios/login', $datos);
                }

            } else {
                $this->view('usuarios/login', $datos);
            }

        } else {
            $datos = [
                'titulo'         => 'Iniciar Sesion - Happy&Jumping',
                'correo'         => '', 'password'       => '',
                'correo_error'   => '', 'password_error' => ''
            ];
            $this->view('usuarios/login', $datos);
        }
    }

    public function register() {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $datos = [
                'titulo'                 => 'Crear Cuenta - Happy&Jumping',
                'nombre'                 => trim($_POST['nombre']),
                'correo'                 => trim($_POST['correo']),
                'password'               => trim($_POST['password']),
                'confirm_password'       => trim($_POST['confirm_password']),
                'nombre_error'           => '', 'correo_error'           => '',
                'password_error'         => '', 'confirm_password_error' => ''
            ];

            if (empty($datos['nombre'])) $datos['nombre_error'] = 'Por favor, ingresa tu nombre.';

            if (empty($datos['correo']))                                        $datos['correo_error'] = 'Por favor, ingresa tu correo.';
            elseif (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL))       $datos['correo_error'] = 'El correo no es valido.';
            elseif (!str_ends_with(strtolower($datos['correo']), '@gmail.com'))   $datos['correo_error'] = 'Solo se aceptan correos @gmail.com.';
            elseif ($this->usuarioModel->findUserByEmail($datos['correo']))     $datos['correo_error'] = 'Este correo ya esta registrado.';

            if (empty($datos['password']))               $datos['password_error'] = 'Por favor, ingresa una contrasena.';
            elseif (strlen($datos['password']) < 8)      $datos['password_error'] = 'La contrasena debe tener al menos 8 caracteres.';

            if (empty($datos['confirm_password']))        $datos['confirm_password_error'] = 'Por favor, confirma la contrasena.';
            elseif ($datos['password'] != $datos['confirm_password']) $datos['confirm_password_error'] = 'Las contrasenas no coinciden.';

            if (empty($datos['nombre_error']) && empty($datos['correo_error']) && empty($datos['password_error']) && empty($datos['confirm_password_error'])) {
                $datos['password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
                if ($this->usuarioModel->register($datos)) {
                    header('Location: ' . URL_ROOT . '/usuarios/login');
                    exit();
                } else {
                    die('Algo salio mal.');
                }
            } else {
                $this->view('usuarios/register', $datos);
            }

        } else {
            $datos = [
                'titulo' => 'Crear Cuenta - Happy&Jumping',
                'nombre' => '', 'correo' => '', 'password' => '', 'confirm_password' => '',
                'nombre_error' => '', 'correo_error' => '', 'password_error' => '', 'confirm_password_error' => ''
            ];
            $this->view('usuarios/register', $datos);
        }
    }

    public function recover() {
        $datos = ['titulo' => 'Recuperar Contrasena - Happy&Jumping'];
        $this->view('usuarios/recover', $datos);
    }

    public function createUsuarioSession($user) {
        $_SESSION['id_usuario']     = $user->id_usuario;
        $_SESSION['usuario_correo'] = $user->correo;
        $_SESSION['usuario_nombre'] = $user->nombre;
        $_SESSION['usuario_rol']    = $user->rol;
    }

    public function logout() {
        unset($_SESSION['id_usuario']);
        unset($_SESSION['usuario_correo']);
        unset($_SESSION['usuario_nombre']);
        unset($_SESSION['usuario_rol']);
        session_destroy();
        header('Location: ' . URL_ROOT);
        exit();
    }
}
?>

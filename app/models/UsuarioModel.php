<?php
/*
|--------------------------------------------------------------------------
| Modelo de Usuario (El "Verificador" de Contraseñas)
|--------------------------------------------------------------------------
*/
class UsuarioModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Función de Login
     * Busca un usuario por email y verifica su contraseña.
     */
    public function login($correo, $password) {
        $this->query("SELECT * FROM usuarios WHERE correo = :correo");
        $this->bind(':correo', $correo);

        $row = $this->single(); // Obtiene el usuario

        // 1. ¿Encontramos al usuario por su correo?
        if ($row) {
            $hashed_password = $row->password;
            
            // 2. ¿La contraseña escrita ($password) coincide con la de la BD ($hashed_password)?
            if (password_verify($password, $hashed_password)) {
                return $row; // ¡Éxito! Devuelve los datos del usuario
            } else {
                return false; // Contraseña incorrecta
            }
        } else {
            return false; // Correo no encontrado
        }
    }

    /**
     * Función de Registro
     */
    public function register($datos) {
        $this->query("INSERT INTO usuarios (nombre, correo, password) VALUES (:nombre, :correo, :password)");
        
        $this->bind(':nombre', $datos['nombre']);
        $this->bind(':correo', $datos['correo']);
        $this->bind(':password', $datos['password']);

        if ($this->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Busca un usuario por su email
     */
    public function findUserByEmail($correo) {
        $this->query("SELECT * FROM usuarios WHERE correo = :correo");
        $this->bind(':correo', $correo);

        $row = $this->single();

        if ($this->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
}
?>
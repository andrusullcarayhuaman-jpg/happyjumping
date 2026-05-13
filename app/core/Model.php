<?php
/*
|--------------------------------------------------------------------------
| Modelo Base (¡VERSIÓN CORREGIDA CON 'PROTECTED'!)
|--------------------------------------------------------------------------
|
| Esto soluciona el error 'Call to a member function on null'.
|
*/

class Model {
    
    /*
     * ======================================================
     * ¡AQUÍ ESTÁ EL CAMBIO!
     * Cambiamos 'private' por 'protected' para que las clases
     * "hijas" (como ReservaModel) puedan heredar la conexión.
     * ======================================================
     */
    protected $dbh; // Database Handler
    protected $stmt; // Statement
    protected $error;

    public function __construct() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        ];

        try {
            // Ahora $this->dbh será visible para ReservaModel
            $this->dbh = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            die('Error de conexión a la BD: ' . $this->error);
        }
    }
    
    /** Prepara la consulta SQL */
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }

    /** Vincula los valores (para prevenir inyección SQL) */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    /** Ejecuta la consulta preparada */
    public function execute() {
        return $this->stmt->execute();
    }

    /** Obtiene un solo registro */
    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    /** Obtiene el número de filas afectadas */
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    /** Obtiene TODOS los registros como resultado */
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
?>
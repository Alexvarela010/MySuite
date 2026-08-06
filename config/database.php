<?php
/**
 * Configuración de Base de Datos - Sistema de Reservas
 * My Suite In Cartagena
 */

class Database {
    private $host = 'mysuiteincartagena.com.co';
    private $db_name = 'mysgd5s3m2re_MySuiteBD';
    private $username = 'mysgd5s3m2re';
    private $password = 'MyS2.025InCartagena';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            // Solo log, no imprimir (evita output antes del JSON)
            error_log("Error de conexión a la base de datos: " . $exception->getMessage());
            $this->conn = null;
        }
        
        return $this->conn;
    }
}
?>

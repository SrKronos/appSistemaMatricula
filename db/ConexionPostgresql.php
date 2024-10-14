<?php
require __DIR__ . '/../vendor/autoload.php'; // Ajusta la ruta al autoloader

use Dotenv\Dotenv;

class ConexionPostgresql {
    private $conexion;

    public function __construct() {
        // Cargar las variables de entorno desde el archivo .env
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../'); // Esto debería funcionar

        $dotenv->load();

        // Obtener las variables de entorno
        $host = "localhost";
        $port = "5432";
        $db = "sistema_matricula";
        $user = "postgres";
        $pass = "admin123";

        // Comentario para no imprimir la contraseña por seguridad
        // echo "Password: $pass\n";

        // Formar el DSN correctamente
        $dsn = "pgsql:host={$host};port={$port};dbname={$db}";

        try {
            $this->conexion = new PDO($dsn, $user, $pass);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }

    public function obtenerConexion() {
        return $this->conexion;
    }

    public function cerrarConexion() {
        $this->conexion = null;
    }
}
?>

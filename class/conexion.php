<?php
/**
 * Conexion simple con MySQL usando mysqli.
 * Las credenciales se cargan desde la configuracion local del entorno.
 */
class Conexion
{
    private string $host;
    private string $db;
    private string $user;
    private string $pass;
    private string $charset;

    private ?mysqli $conexion = null;

    public function getConexion(): mysqli
    {
        if ($this->conexion instanceof mysqli) {
            return $this->conexion;
        }

        $configPath = __DIR__ . '/conexion_local.php';
        if (!is_file($configPath)) {
            throw new RuntimeException('Falta la configuracion local de base de datos: class/conexion_local.php.');
        }

        $config = require $configPath;
        foreach (['host', 'db', 'user', 'pass', 'charset'] as $key) {
            if (!is_array($config) || !isset($config[$key]) || !is_string($config[$key])) {
                throw new RuntimeException('La configuracion local de base de datos no es valida.');
            }
            $this->$key = $config[$key];
        }

        mysqli_report(MYSQLI_REPORT_OFF);

        $this->conexion = @new mysqli(
            $this->host,
            $this->user,
            $this->pass,
            $this->db
        );

        if ($this->conexion->connect_error) {
            throw new RuntimeException(
                'No fue posible conectar con la base de datos.'
            );
        }

        if (!$this->conexion->set_charset($this->charset)) {
            throw new RuntimeException('No fue posible configurar el charset de la conexion MySQL.');
        }

        return $this->conexion;
    }
}

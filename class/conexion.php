<?php
/**
 * Conexion simple con MySQL usando mysqli.
 * Mas adelante conviene migrar estas credenciales a variables de entorno.
 */
class Conexion
{
    private string $host = 'localhost';
    private string $db = 'qaseduc_colegio_spablo';
    private string $user = 'qaseduc_ucomun';
    private string $pass = 'jorquera86;';
    private string $charset = 'utf8mb4';
        // $this->conexion = mysqli_connect('localhost', 'qaseduc_ucomun', 'jorquera86;', "qaseduc_calculo_horario");
        // $this->conexion = mysqli_connect('localhost', 'qaseduc_ucomun', 'jorquera86;', "qaseduc_calculo_horario");

    private ?mysqli $conexion = null;

    public function getConexion(): mysqli
    {
        if ($this->conexion instanceof mysqli) {
            return $this->conexion;
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
                'No fue posible conectar con la base de datos: ' . $this->conexion->connect_error
            );
        }

        if (!$this->conexion->set_charset($this->charset)) {
            throw new RuntimeException('No fue posible configurar el charset de la conexion MySQL.');
        }

        return $this->conexion;
    }
}

<?php

use Utilidades\Output;

abstract class BaseController implements IBaseController
{
    protected $dbConnection;

    public function __construct(IDbConnection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function get(string $query, string $classDTO): never
    {
        $mysqli = $this->dbConnection->conectarBD();
        $resultado = $mysqli->query($query);
        if ($resultado === false) {
            Output::outputError(500, "Falló la consulta: " . $mysqli->error);
        }
        $ret = [];
        while ($fila = $resultado->fetch_assoc()) {
            $ret[] = new $classDTO($fila);
        }
        $resultado->free_result();
        $mysqli->close();
        Output::outputJson($ret);
    }
    public function getConParametros(string $query, string $classDTO)
    {
        $mysqli = $this->dbConnection->conectarBD();
        $resultado = $mysqli->query($query);
        if ($resultado === false) {
            Output::outputError(500, "Falló la consulta al querer obtener un usuario por id: " . $mysqli->error);
            die;
        }
        if ($resultado->num_rows == 0) {
            Output::outputError(404, "No se encontró un usuario con ese id");
        }
        $ret = new $classDTO(mysqli_fetch_assoc($resultado));
        $resultado->free_result();
        $mysqli->close();
        Output::outputJson($ret);
    }
    public function post(string $query, mysqli $link): never
    {
        $resultado = $link->query($query);
        if ($resultado === false) {
            Output::outputError(500, "Falló la consulta: " . $link->error);
        }
        $ret = [
            'usrId' => $link->insert_id
        ];
        $link->close();
        Output::outputJson($ret, 201);
    }
    public function patch($id)
    {
        // Implementación del método patch
    }
    public function delete($id)
    {
        // Implementación del método delete
    }
}

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
            $error= $mysqli->error;
            $mysqli->close();            
            Output::outputError(500, 'Falló la consulta: ' . $error);
        }
        $ret = [];
        while ($fila = $resultado->fetch_assoc()) {
            $ret[] = new $classDTO($fila);
        }
        $resultado->free_result();
        $mysqli->close();
        Output::outputJson($ret);
    }
    public function getById(string $query, string $classDTO)
    {
        $mysqli = $this->dbConnection->conectarBD();
        $resultado = $mysqli->query($query);
        if ($resultado === false) {
            $error= $mysqli->error;
            $mysqli->close();
            Output::outputError(500, "Falló la consulta al querer obtener un $classDTO por id: " . $error);
            die;
        }
        if ($resultado->num_rows == 0) {
            $mysqli->close();
            Output::outputError(404, "No se encontró un $classDTO con ese id");
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
            $error= $link->error;
            $link->close();
            Output::outputError(500, 'Falló la consulta: ' . $error);
        }
        $ret = [
            'id' => $link->insert_id
        ];
        $link->close();
        Output::outputJson($ret, 201);
    }
    public function patch(string $query, mysqli $link)
    {
        $resultado = $link->query($query);
        if ($resultado === false) {
            $error= $link->error;
            $link->close();
            Output::outputError(500, 'Falló la consulta: ' . $error);
        }

        $ret = [];

        $link->close();
        Output::outputJson($ret, 201);
    }
    public function delete(string $queryBusqueda, string $queryBajaLogica)
    {
        $mysqli = $this->dbConnection->conectarBD();
        $resultado = $mysqli->query($queryBusqueda);
        if ($resultado === false) {
            $error = $mysqli->error;
            $mysqli->close();
            Output::outputError(500, "Falló la consulta al querer comprobar la existencia de la entidad por id: " . $error);
            die;
        }
        if ($resultado->num_rows == 0) {
            $mysqli->close();
            Output::outputError(404, 'No se encontró la entidad con ese id para ser eliminada');
        }
        $resultado->free_result();
        $resultado = $mysqli->query($queryBajaLogica);
        if ($resultado === false) {
            $error= $mysqli->error;
            $mysqli->close();
            Output::outputError(500, 'Falló la consulta: ' . $error);
        }
        $mysqli->close();
        Output::outputJson([]);
    }
}
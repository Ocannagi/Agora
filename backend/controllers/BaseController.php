<?php

use Model\CustomException;
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
            throw new mysqli_sql_exception(code:500, message:'Falló la consulta: ' . $error);
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
            throw new mysqli_sql_exception(code:500, message:"Falló la consulta al querer obtener un $classDTO por id: " . $error);
        }
        if ($resultado->num_rows == 0) {
            $mysqli->close();
            throw new CustomException(code:404, message:"No se encontró un $classDTO con ese id");
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
            throw new mysqli_sql_exception(code:500, message:'Falló la consulta: ' . $error);
        }
        $ret = [
            'id' => $link->insert_id
        ];
        Output::outputJson($ret, 201);
    }
    public function patch(string $query, mysqli $link)
    {
        $resultado = $link->query($query);
        if ($resultado === false) {
            $error= $link->error;
            throw new mysqli_sql_exception(code:500, message:'Falló la consulta: ' . $error);
        }

        $ret = [];
        Output::outputJson($ret, 201);
    }
    public function delete(string $queryBusqueda, string $queryBajaLogica)
    {
        $mysqliDelete = $this->dbConnection->conectarBD();
        $resultado = $mysqliDelete->query($queryBusqueda);
        if ($resultado === false) {
            $error = $mysqliDelete->error;
            $mysqliDelete->close();
            throw new mysqli_sql_exception(code:500, message:"Falló la consulta al querer comprobar la existencia de la entidad por id: " . $error);
        }
        if ($resultado->num_rows == 0) {
            $mysqliDelete->close();
            throw new CustomException(code:404, message:'No se encontró la entidad con ese id para ser eliminada');
        }
        $resultado->free_result();
        $resultado = $mysqliDelete->query($queryBajaLogica);
        if ($resultado === false) {
            $error= $mysqliDelete->error;
            $mysqliDelete->close();
            throw new mysqli_sql_exception(code:500, message:'Falló la consulta: ' . $error);
        }
        $mysqliDelete->close();
        Output::outputJson([]);
    }
}
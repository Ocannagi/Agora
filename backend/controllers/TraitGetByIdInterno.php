<?php

use Model\CustomException;

trait TraitGetByIdInterno
{
    public function getByIdInterno(string $query, string $classDTO): IDTO
    {
        if (!class_exists($classDTO)) {
            throw new CustomException(code: 500, message: 'La clase ' . $classDTO . ' no existe.');
        }

        if (!is_subclass_of($classDTO, IDTO::class)) {
            throw new CustomException(code: 500, message: 'La clase ' . $classDTO . ' no implementa la interfaz IDTO.');
        }

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
        return $ret;

    }
}
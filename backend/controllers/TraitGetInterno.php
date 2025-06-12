<?php

use Model\CustomException;

trait TraitGetInterno
{

    public function getInterno(string $query, string $classDTO, ?mysqli $linkExterno = null): array
    {
        if (!class_exists($classDTO)) {
            throw new CustomException(code:500, message:'La clase ' . $classDTO . ' no existe.');
        }

        if (!is_subclass_of($classDTO, IDTO::class)) {
            throw new CustomException(code:500, message:'La clase ' . $classDTO . ' no implementa la interfaz IDTO.');
        }

        $mysqli = null;
        if($linkExterno === null || !($linkExterno instanceof mysqli)) {
            $mysqli = $this->dbConnection->conectarBD();
        } else {
            $mysqli = $linkExterno;
        }        
        
        $resultado = $mysqli->query($query);
        if ($resultado === false) {
            $error = $mysqli->error;
            if($linkExterno === null || !($linkExterno instanceof mysqli)) {
                $mysqli->close();
            }
            throw new mysqli_sql_exception(code:500, message:'Falló la consulta: ' . $error);
        }
        $ret = [];
        while ($fila = $resultado->fetch_assoc()) {
            $ret[] = new $classDTO($fila);
        }
        $resultado->free_result();
        if($linkExterno === null || !($linkExterno instanceof mysqli)) {
            $mysqli->close();
        }
        return $ret;
    }
}

<?php

namespace Utilidades;
use mysqli;
use Model\CustomException;
use InvalidArgumentException;

class Querys
{
    /**
     * Verifica si una consulta SQL devuelve algún resultado.
     * @param mysqli $link Conexión a la base de datos.
     * @param string $query Consulta SQL a ejecutar.
     * @param string $msg Mensaje descriptivo para el error.
     * @param string|null $columnId Nombre de la columna a verificar (opcional).
     * Si se proporciona, se devuelve el valor de esa columna del primer resultado.
     * Si no se proporciona, se devuelve un booleano.
     * @throws CustomException Si hay un error al ejecutar la consulta.
     * 
     * @return bool|int Devuelve true si hay resultados, false si no los hay.
     * Si se proporciona $columnId, devuelve el valor de esa columna del primer resultado.
     */
    public static function existeEnBD(mysqli $link, string $query, string $msg, ?string $columnId = null): bool | int
    {
        $result = $link->query($query);
        if (!$result) {
            throw new CustomException(code: 500, message: "Error interno al querer $msg: " . $link->error);
        }

        $retorno = false;

        if ($columnId !== null) {
            if ($result->num_rows === 0) {
                $retorno = 0;
            }
            $row = $result->fetch_assoc();
            $retorno = (int)$row[$columnId];
        } 

        $retorno = $result->num_rows > 0;
        $result->free_result();
        return $retorno;
    }

    public static function obtenerCount(mysqli $link, string $base, string $where, string $msg): int
    {

        $query = "SELECT COUNT(*) AS count FROM $base WHERE $where";
        $result = $link->query($query);
        if (!$result) {
            throw new CustomException(code: 500, message: "Error interno al querer $msg: " . $link->error);
        } else if ($result->num_rows === 0) {
            throw new InvalidArgumentException(message: "No se encontraron resultados al querer $msg.");
        }
        
        $count = (int)$result->fetch_assoc()['count'];
        $result->free_result();
        return $count;
    }

    /**
     * Recomendado por la Copilot, usar con precaución.
     * Obtiene el último ID insertado en la base de datos.
     * @param mysqli $link Conexión a la base de datos.
     * @return int Último ID insertado.
     */
    public static function obtenerUltimoId(mysqli $link): int
    {
        $query = "SELECT LAST_INSERT_ID() AS last_id";
        $result = $link->query($query);
        if (!$result) {
            throw new CustomException(code: 500, message: "Error interno al obtener el último ID: " . $link->error);
        }
        $row = $result->fetch_assoc();
        $result->free_result();
        return (int)$row['last_id'];
    }
}
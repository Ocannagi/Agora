<?php

define('QUERYS', [
    'USUARIO' => "  SELECT   usrId, usrDni, usrNombre, usrApellido
                            , domId, domCPA, domCalleRuta, domNroKm, domPiso, domDepto, locId, locDescripcion, provId, provDescripcion
                           , usrRazonSocialFantasia, usrCuitCuil, usrEmail
                           , usrTipoUsuario , usrMatricula, usrFechaNacimiento, usrDescripcion, usrScoring
                    FROM usuario
                    LEFT JOIN domicilio ON usrDomicilio = domId
                    LEFT JOIN localidad ON locId = domLocId
                    LEFT JOIN provincia ON provId = locProvId
                    WHERE usrFechaBaja is NULL
                    AND usrId = %id",
    'ANTIGUEDAD' => "SELECT antId, antDescripcion, antFechaEstado, antTipoEstado
                        ,perId, perDescripcion
                        ,scatId, catId, catDescripcion, scatDescripcion
                        ,usrId, usrNombre, usrApellido, usrEmail, usrTipoUsuario, usrRazonSocialFantasia,usrDescripcion,usrScoring,usrCuitCuil,usrMatricula
                        ,domId, domCPA, domCalleRuta, domNroKm, domPiso, domDepto
                        ,locId, locDescripcion, provId, provDescripcion
                    FROM antiguedad
                    INNER JOIN periodo ON antPerId = perId
                    INNER JOIN subcategoria ON antScatId = scatId
                    INNER JOIN categoria ON scatCatId = catId
                    INNER JOIN usuario ON antUsrId = usrId
                    INNER JOIN domicilio ON usrDomicilio = domId
                    INNER JOIN localidad ON locId = domLocId
                    INNER JOIN provincia ON provId = locProvId
                  WHERE antTipoEstado <>'RN'
                  AND antId = %id",
    'DOMICILIO' => "SELECT domId, domCPA, domCalleRuta, domNroKm, domPiso, domDepto
                        ,locId, locDescripcion, provId, provDescripcion
                    FROM domicilio
                    LEFT JOIN localidad ON locId = domLocId
                    LEFT JOIN provincia ON provId = locProvId
                    WHERE domId = %id",

                ]);

use Model\CustomException;

trait TraitGetByIdInterno
{
    /**
     * Obtiene un objeto de tipo IDTO por su ID desde la base de datos.
     * Si se completa el ID en la consulta, se reemplaza el marcador %id por el valor proporcionado.
     * 
     * Consultas predefinidas: 'USUARIO' para obtener un usuario por su ID.
     * Antigüedades: 'ANTIGUEDAD' para obtener una antigüedad por su ID.
     * 
     * @param string $query Consulta SQL para obtener el objeto. Puede proporcionarse un string con el nombre de una consulta predefinida en QUERYS.
     * @param string $classDTO Nombre de la clase que implementa IDTO.
     * @param mysqli|null $linkExterno Conexión a la base de datos externa, si es necesario.
     * @param int|null $id ID del objeto a buscar, si es necesario.
     * @return IDTO Objeto obtenido de la base de datos.
     * @throws CustomException Si la clase no existe o no implementa IDTO, o si ocurre un error en la consulta.
     * 
     *
     */
    public function getByIdInterno(string $query, string $classDTO, ?mysqli $linkExterno = null, ?int $id = null): IDTO
    {
        if (!class_exists($classDTO)) {
            throw new CustomException(code: 500, message: 'La clase ' . $classDTO . ' no existe.');
        }

        if (!is_subclass_of($classDTO, IDTO::class)) {
            throw new CustomException(code: 500, message: 'La clase ' . $classDTO . ' no implementa la interfaz IDTO.');
        }

        $mysqli = null;
        if ($linkExterno === null || !($linkExterno instanceof mysqli)) {
            $mysqli = $this->dbConnection->conectarBD();
        } else {
            $mysqli = $linkExterno;
        }

        if ($id !== null) {
            $query = QUERYS[$query] ?? $query;
            $query = str_replace('%id', $id, $query);
        }

        $resultado = $mysqli->query($query);
        if ($resultado === false) {
            $error = $mysqli->error;
            if ($linkExterno === null || !($linkExterno instanceof mysqli)) {
                $mysqli->close();
            }
            throw new mysqli_sql_exception(code: 500, message: "Falló la consulta al querer obtener un $classDTO por id: " . $error);
        }
        if ($resultado->num_rows == 0) {
            $mysqli->close();
            throw new CustomException(code: 404, message: "No se encontró un $classDTO con ese id");
        }
        $ret = new $classDTO(mysqli_fetch_assoc($resultado));
        $resultado->free_result();
        if ($linkExterno === null || !($linkExterno instanceof mysqli)) {
            $mysqli->close();
        }
        return $ret;
    }


    public function getByIdInternoAllowsNull(string $query, string $classDTO, ?mysqli $linkExterno = null, ?int $id = null): ?IDTO
    {
        if (!class_exists($classDTO)) {
            throw new CustomException(code: 500, message: 'La clase ' . $classDTO . ' no existe.');
        }

        if (!is_subclass_of($classDTO, IDTO::class)) {
            throw new CustomException(code: 500, message: 'La clase ' . $classDTO . ' no implementa la interfaz IDTO.');
        }

        $mysqli = null;
        if ($linkExterno === null || !($linkExterno instanceof mysqli)) {
            $mysqli = $this->dbConnection->conectarBD();
        } else {
            $mysqli = $linkExterno;
        }

        if ($id !== null) {
            $query = QUERYS[$query] ?? $query;
            $query = str_replace('%id', $id, $query);
        }

        $resultado = $mysqli->query($query);
        if ($resultado === false) {
            $error = $mysqli->error;
            if ($linkExterno === null || !($linkExterno instanceof mysqli)) {
                $mysqli->close();
            }
            throw new mysqli_sql_exception(code: 500, message: "Falló la consulta al querer obtener un $classDTO por id: " . $error);
        }
        if ($resultado->num_rows == 0) {
            $mysqli->close();
            return null; // Permite que el resultado sea nulo si no se encuentra el objeto
        }
        $ret = new $classDTO(mysqli_fetch_assoc($resultado));
        $resultado->free_result();
        if ($linkExterno === null || !($linkExterno instanceof mysqli)) {
            $mysqli->close();
        }
        return $ret;
    }

}

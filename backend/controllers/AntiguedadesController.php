<?php

use Utilidades\Output;
use Utilidades\Input;
use Utilidades\Querys;
use Model\CustomException;

class AntiguedadesController extends BaseController
{
    use TraitGetInterno; // Trait para métodos internos de obtención
    use TraitGetByIdInterno; // Trait para métodos internos de obtención por ID
    use TraitCambiarEstadoAntiguedad; // Trait para cambiar el estado de una antiguedad

    private ValidacionServiceBase $antiguedadesValidacionService;
    private ISecurity $securityService;

    private static $instancia = null; // La única instancia de la clase

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $antiguedadesValidacionService)
    {
        parent::__construct($dbConnection);
        $this->antiguedadesValidacionService = $antiguedadesValidacionService;
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $antiguedadesValidacionService): AntiguedadesController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService, $antiguedadesValidacionService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    /** SECCION DE MÉTODOS CON getAntiguedadesByParams */

    public function getAntiguedadesByParams($params)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            if (is_array($params)) {

                if (array_key_exists('scatId', $params) || array_key_exists('catId', $params) || array_key_exists('perId', $params) || array_key_exists('usrId', $params) || array_key_exists('antDescripcion', $params) || array_key_exists('antTipoEstado', $params)) {
                    $scatId = null;
                    $catId = null;
                    $perId = null;
                    $usrId = null;
                    $antDescripcion = null;
                    $antTipoEstado = null;
                    if (array_key_exists('scatId', $params)) {
                        $scatId = (int)$params['scatId'];
                    }
                    if (array_key_exists('catId', $params)) {
                        $catId = (int)$params['catId'];
                    }
                    if (array_key_exists('perId', $params)) {
                        $perId = (int)$params['perId'];
                    }
                    if (array_key_exists('usrId', $params)) {
                        $usrId = (int)$params['usrId'];
                    }
                    if (array_key_exists('antDescripcion', $params)) {
                        $antDescripcion = (string)$params['antDescripcion'];
                    }
                    if (array_key_exists('antTipoEstado', $params)) {
                        $antTipoEstado = (string)$params['antTipoEstado'];
                        if (!in_array($antTipoEstado, array_map(fn($e) => $e->value, TipoEstadoEnum::cases()))) {
                            throw new InvalidArgumentException(code: 400, message: "El parámetro 'antTipoEstado' no es válido.");
                        }
                    }

                    return $this->getAntiguedadesByFiltros($mysqli ,$scatId, $catId, $perId, $usrId, $antDescripcion, $antTipoEstado);
                } else {
                    throw new InvalidArgumentException(code: 400, message: "No se recibieron parámetros válidos.");
                }
            } else {
                throw new InvalidArgumentException(code: 400, message: "No se recibieron parámetros válidos.");
            }
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        } finally {
            if(isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }

    private function getAntiguedadesByFiltros(mysqli $mysqli, ?int $scatId, ?int $catId, ?int $perId, ?int $usrId, ?string $antDescripcion, ?string $antTipoEstado): ?array
    {
        $this->securityService->requireLogin(tipoUsurio: null);

        $query = "SELECT antId, antDescripcion, antFechaEstado, antTipoEstado
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
                  WHERE antTipoEstado <>'RN'";
        if ($scatId != null) {
            $query .= " AND scatId = $scatId";
        }
        if ($catId != null) {
            $query .= " AND catId = $catId";
        }
        if ($perId != null) {
            $query .= " AND perId = $perId";
        }
        if ($usrId != null) {
            $query .= " AND usrId = $usrId";
        }
        if (Input::esNotNullVacioBlanco($antDescripcion)) {
            $antDescripcion = $mysqli->real_escape_string($antDescripcion);
            $query .= " AND antDescripcion LIKE '%$antDescripcion%'";
        }
        if ($antTipoEstado != null) {
            $query .= " AND antTipoEstado = '$antTipoEstado'";
        }

        $arrayAntiguedadesDTO = $this->getInterno(query: $query, classDTO: "AntiguedadDTO", linkExterno: $mysqli);
        foreach ($arrayAntiguedadesDTO as $antiguedadDTO) {
            $query = "SELECT imaId, imaUrl, imaAntId, imaOrden, imaNombreArchivo FROM imagenantiguedad WHERE imaAntId = {$antiguedadDTO->antId} ORDER BY imaOrden";
            $antiguedadDTO->imagenes = $this->getInterno(query: $query, classDTO: "ImagenAntiguedadDTO", linkExterno: $mysqli);
        }
        Output::outputJson($arrayAntiguedadesDTO);
    }

    /** FIN DE SECCION */

    public function getAntiguedades()
    {
        try {
            $this->securityService->requireLogin(tipoUsurio: null);

            $query = "SELECT antId, antDescripcion, antFechaEstado, antTipoEstado
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
                  WHERE antTipoEstado <>'RN'";

            // Se eliminó la unión con imagenantiguedad para evitar duplicados en caso de que una antigüedad tenga varias imágenes.

            $arrayAntiguedadesDTO = $this->getInterno(query: $query, classDTO: "AntiguedadDTO");

            foreach ($arrayAntiguedadesDTO as $antiguedadDTO) {
                $query = "SELECT imaId, imaUrl, imaAntId, imaOrden, imaNombreArchivo FROM imagenantiguedad WHERE imaAntId = {$antiguedadDTO->antId} ORDER BY imaOrden";
                $antiguedadDTO->imagenes = $this->getInterno(query: $query, classDTO: "ImagenAntiguedadDTO");
            }

            Output::outputJson($arrayAntiguedadesDTO);
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }

    public function getAntiguedadesById($id)
    {
        try {
            settype($id, 'integer');
            $this->securityService->requireLogin(tipoUsurio: null);

            $query = "SELECT antId, antDescripcion, antFechaEstado, antTipoEstado
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
                  AND antId = $id";

            $antiguedadDTO = $this->getByIdInterno(query: $query, classDTO: "AntiguedadDTO");
            $antiguedadDTO->imagenes = $this->getInterno(query: "SELECT imaId, imaUrl, imaAntId, imaOrden, imaNombreArchivo FROM imagenantiguedad WHERE imaAntId = $id ORDER BY imaOrden", classDTO: "ImagenAntiguedadDTO");

            Output::outputJson($antiguedadDTO);
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }

    public function postAntiguedades()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());
            $data = Input::getArrayBody(msgEntidad: "la antigüedad");

            $this->antiguedadesValidacionService->validarType(className: "AntiguedadCreacionDTO", datos: $data);
            $antiguedadCreacionDTO = new AntiguedadCreacionDTO($data);

            if (isset($antiguedadCreacionDTO->usuario->usrId)) {
                if ($claimDTO->usrTipoUsuario == TipoUsuarioEnum::UsuarioGeneral->value || $claimDTO->usrTipoUsuario == TipoUsuarioEnum::UsuarioAnticuario->value) {
                    $antiguedadCreacionDTO->usuario->usrId = $claimDTO->usrId;
                    $antiguedadCreacionDTO->usuario->usrTipoUsuario = $claimDTO->usrTipoUsuario;
                }
                // A modo de prueba, el usuario técnico puede agregar antigüedades a cualquier usuario o a sí mismo.
                if ($claimDTO->usrTipoUsuario == TipoUsuarioEnum::SoporteTecnico->value) {
                    if (!isset($antiguedadCreacionDTO->usuario->usrId) || $antiguedadCreacionDTO->usuario->usrId == 0) {
                        $antiguedadCreacionDTO->usuario->usrId = $claimDTO->usrId;
                    }
                    $antiguedadCreacionDTO->usuario->usrTipoUsuario = $claimDTO->usrTipoUsuario; // Soporte Técnico puede agregar antigüedades a cualquier usuario.
                }
            }


            $this->antiguedadesValidacionService->validarInput($mysqli, $antiguedadCreacionDTO);
            Input::escaparDatos($antiguedadCreacionDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($antiguedadCreacionDTO);

            $query = "INSERT INTO antiguedad (antDescripcion, antPerId, antScatId, antUsrId)
                  VALUES ($antiguedadCreacionDTO->antDescripcion, {$antiguedadCreacionDTO->periodo->perId}, {$antiguedadCreacionDTO->subcategoria->scatId}, {$antiguedadCreacionDTO->usuario->usrId})";

            return parent::post(query: $query, link: $mysqli);
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        } finally {
            if(isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }

    public function patchAntiguedades($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());
            settype($id, 'integer');
            $data = Input::getArrayBody(msgEntidad: "la antigüedad");

            $data['antId'] = $id;

            $this->antiguedadesValidacionService->validarType(className: "AntiguedadDTO", datos: $data);
            $antiguedadDTO = new AntiguedadDTO($data);

            if (isset($antiguedadDTO->usuario->usrId)) {
                if (TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico()) {
                    if (!isset($antiguedadDTO->usuario->usrId) || $antiguedadDTO->usuario->usrId == 0)
                        $antiguedadDTO->usuario->usrId = $claimDTO->usrId;
                } else {
                    if ($claimDTO->usrId != $antiguedadDTO->usuario->usrId) {
                        throw new CustomException(code: 403, message: "No tiene permiso para modificar esta antigüedad.");
                    }

                    if($antiguedadDTO->usuario->usrId != $this->obtenerUsrIdAntiguedad($mysqli, $id)) {
                        throw new CustomException(code: 403, message: "No tiene permiso para modificar esta antigüedad.");
                    }
                }
            }

            $this->antiguedadesValidacionService->validarInput($mysqli, $antiguedadDTO);
            Input::escaparDatos($antiguedadDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($antiguedadDTO);

            $antiguedadDTO->tipoEstado = $this->calcularEstadoAntiguedad($mysqli, $antiguedadDTO);
            
            $conservaMismoTipoEstado = Querys::existeEnBD(
                link: $mysqli,
                query: "SELECT 1 FROM antiguedad WHERE antId={$antiguedadDTO->antId} AND antTipoEstado='{$antiguedadDTO->tipoEstado->value}'",
                msg: "verificar si la antigüedad conserva el mismo tipo de estado"
            );

            $query = "UPDATE antiguedad
                  SET antDescripcion = $antiguedadDTO->antDescripcion,
                      antPerId = {$antiguedadDTO->periodo->perId},
                      antScatId = {$antiguedadDTO->subcategoria->scatId},
                      antUsrId = {$antiguedadDTO->usuario->usrId},
                      antTipoEstado = '{$antiguedadDTO->tipoEstado->value}'";

            // Si el tipo de estado ha cambiado, se actualiza la fecha de estado
            if (!$conservaMismoTipoEstado) {
                $query .= ", antFechaEstado = NOW()";
            }

            $query .= " WHERE antId = $id";

            return parent::patch(query: $query, link: $mysqli);
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        } finally {
            if(isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }

    public function deleteAntiguedades($id)
    {
        try {
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());
            settype($id, 'integer');

            $queryBusqueda = "SELECT antId FROM antiguedad WHERE antId = $id AND antTipoEstado <> 'RN'";

            if ($claimDTO->usrTipoUsuario !== 'ST') {
                $queryBusqueda .= " AND antUsrId = {$claimDTO->usrId}";
            }

            $queryBajaLogica = "UPDATE antiguedad SET antTipoEstado = 'RN', antFechaEstado = NOW() WHERE antId = $id";

            return parent::delete(queryBusqueda: $queryBusqueda, queryBajaLogica: $queryBajaLogica);
        } catch (\Throwable $th) {
            if ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage());
            }
        }
    }
}

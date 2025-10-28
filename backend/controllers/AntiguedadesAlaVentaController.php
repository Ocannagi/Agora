<?php

use Utilidades\Output;
use Utilidades\Input;
use Utilidades\Querys;
use Model\CustomException;

class AntiguedadesAlaVentaController extends BaseController
{
    private ValidacionServiceBase $antiguedadesAlaVentaValidacionService;
    private ISecurity $securityService;

    use TraitGetInterno; // Trait para métodos internos de obtención genéricos
    use TraitGetByIdInterno; // Trait para métodos internos de obtención por ID
    use TraitCambiarEstadoAntiguedad; // Trait para cambiar el TipoEstado de una Antiguedad
    use TraitGetPaginado; // Trait para obtener paginados genéricos

    private static $instancia = null; // La única instancia de la clase

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $antiguedadesAlaVentaValidacionService)
    {
        parent::__construct($dbConnection);
        $this->securityService = $securityService;
        $this->antiguedadesAlaVentaValidacionService = $antiguedadesAlaVentaValidacionService;
    }

    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $antiguedadesAlaVentaValidacionService): AntiguedadesAlaVentaController
    {
        if (self::$instancia === null) {
            self::$instancia = new AntiguedadesAlaVentaController($dbConnection, $securityService, $antiguedadesAlaVentaValidacionService);
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    /** SECCION DE MÉTODOS CON getAntiguedadesByParams */

    public function getAntiguedadesAlaVentaByParams($params)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {

            if (is_array($params)) {
                if (
                    array_key_exists('aavPrecioVentaMax', $params) || array_key_exists('aavPrecioVentaMin', $params) || array_key_exists('usrId', $params)
                    || array_key_exists('perId', $params) || array_key_exists('scatId', $params) || array_key_exists('catId', $params) || array_key_exists('antDescripcion', $params)
                ) {
                    $aavPrecioVentaMax = null;
                    $aavPrecioVentaMin = null;
                    $usrId = null;
                    $perId = null;
                    $scatId = null;
                    $catId = null;
                    $antDescripcion = null;

                    if (array_key_exists('aavPrecioVentaMax', $params) && Input::esNotNullVacioBlanco($params['aavPrecioVentaMax'])) {
                        $aavPrecioVentaMax = (float)$params['aavPrecioVentaMax'];
                    }
                    if (array_key_exists('aavPrecioVentaMin', $params) && Input::esNotNullVacioBlanco($params['aavPrecioVentaMin'])) {
                        $aavPrecioVentaMin = (float)$params['aavPrecioVentaMin'];
                    }
                    if (array_key_exists('usrId', $params) && Input::esNotNullVacioBlanco($params['usrId'])) {
                        $usrId = (int)$params['usrId'];
                    }
                    if (array_key_exists('perId', $params) && Input::esNotNullVacioBlanco($params['perId'])) {
                        $perId = (int)$params['perId'];
                    }
                    if (array_key_exists('scatId', $params) && Input::esNotNullVacioBlanco($params['scatId'])) {
                        $scatId = (int)$params['scatId'];
                    }
                    if (array_key_exists('catId', $params) && Input::esNotNullVacioBlanco($params['catId'])) {
                        $catId = (int)$params['catId'];
                    }
                    if (array_key_exists('antDescripcion', $params) && Input::esNotNullVacioBlanco($params['antDescripcion'])) {
                        $antDescripcion = (string)$params['antDescripcion'];
                    }

                    return $this->getAntiguedadesAlaVentaByFiltros($mysqli, $aavPrecioVentaMax, $aavPrecioVentaMin, $usrId, $perId, $scatId, $catId, $antDescripcion);
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
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }

    private function getAntiguedadesAlaVentaByFiltros(mysqli $mysqli, ?float $aavPrecioVentaMax, ?float $aavPrecioVentaMin, ?int $usrId, ?int $perId, ?int $scatId, ?int $catId, ?string $antDescripcion)
    {

        $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());

        $filtros = [];
        if (isset($aavPrecioVentaMax)) {
            $filtros[] = "aavPrecioVenta <= $aavPrecioVentaMax";
        }
        if (isset($aavPrecioVentaMin)) {
            $filtros[] = "aavPrecioVenta >= $aavPrecioVentaMin";
        }
        if (isset($usrId)) {
            $filtros[] = "antUsrId = $usrId";
        }
        if (isset($perId)) {
            $filtros[] = "antPerId = $perId";
        }
        if (isset($scatId)) {
            $filtros[] = "antScatId = $scatId";
        }
        if (isset($catId)) {
            $filtros[] = "catId = $catId";
        }
        if (isset($antDescripcion)) {
            $antDescripcion = $mysqli->real_escape_string($antDescripcion);
            $filtros[] = "antDescripcion LIKE '%$antDescripcion%'";
        }

        $whereAdicional = "";
        if (count($filtros) > 0) {
            $whereAdicional = " AND " . implode(" AND ", $filtros);
        }


        $query = "SELECT aavId, aavAntId, aavUsrIdVendedor, aavDomOrigen, aavPrecioVenta, aavTadId, aavFechaPublicacion, aavFechaRetiro, aavHayVenta, tisId
                      FROM antiguedadalaventa
                      INNER JOIN antiguedad ON aavAntId = antId
                      INNER JOIN subcategoria ON antScatId = scatId
                      INNER JOIN categoria ON scatCatId = catId
                      INNER JOIN usuario ON antUsrId = usrId
                      LEFT  JOIN tasacioninsitu ON aavTadId = tisTadId
                                    AND tisFechaBaja IS NULL
                                    AND tisFechaTasInSituRealizada IS NOT NULL 
                      WHERE aavFechaRetiro IS NULL AND aavHayVenta = FALSE $whereAdicional"; //IMPORTANTE: Solo se consideran las tasaciones in situ realizadas

        $arrayAntiguedadesAlaVentaDTO = $this->getInterno(query: $query, classDTO: AntiguedadAlaVentaDTO::class, linkExterno: $mysqli);

        $arrayAntiguedadesAlaVentaDTO = $this->completarArrayAntiguedadesAlaVentaDTO($mysqli, $arrayAntiguedadesAlaVentaDTO);

        Output::outputJson($arrayAntiguedadesAlaVentaDTO);
    }

    /** SECCION DE MÉTODOS CRUD */

    public function getAntiguedadesAlaVentaPaginado($paginado)
    {
        $mysqli = $this->dbConnection->conectarBD();
        
        try {
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());

            $query = "SELECT aavId, aavAntId, aavUsrIdVendedor, aavDomOrigen, aavPrecioVenta, aavTadId, aavFechaPublicacion, aavFechaRetiro, aavHayVenta, tisId
                      FROM antiguedadalaventa
                      LEFT  JOIN tasacioninsitu ON aavTadId = tisTadId
                                    AND tisFechaBaja IS NULL
                                    AND tisFechaTasInSituRealizada IS NOT NULL 
                      WHERE aavFechaRetiro IS NULL AND aavHayVenta = FALSE"; //IMPORTANTE: Solo se consideran las tasaciones in situ realizadas
            
            $queryFiltro = "";
            if ($this->isFiltrarPorUsrId($paginado)) {
                $queryFiltro .= " AND aavUsrIdVendedor = {$claimDTO->usrId}";
            }
            $query .= $queryFiltro;

            $paginadoResponseDTO = $this->getPaginadoResponseDTO($paginado, $mysqli, "antiguedadalaventa", "aavFechaRetiro IS NULL AND aavHayVenta = FALSE" . $queryFiltro, "obtener el total de antigüedades a la venta para paginado", $query, AntiguedadALaVentaDTO::class);

            $paginadoResponseDTO->arrayEntidad = $this->completarArrayAntiguedadesAlaVentaDTO($mysqli, $paginadoResponseDTO->arrayEntidad);

            Output::outputJson($paginadoResponseDTO);

        } catch (\Throwable $th) {
            if ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } elseif ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        } finally {
            if (isset($mysqli) && $mysqli instanceof mysqli) { // Verificar si la conexión fue establecida
                $mysqli->close(); // Cerrar la conexión a la base de datos
            }
        }
    }


    public function getAntiguedadesAlaVentaPaginadoSearch($paginadoSearch)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $this->validatePaginadoSearch($paginadoSearch);

            $searchWord = $mysqli->real_escape_string($paginadoSearch['searchWord']);

            $query = "SELECT aavId, aavAntId, aavUsrIdVendedor, aavDomOrigen, aavPrecioVenta, aavTadId, aavFechaPublicacion, aavFechaRetiro, aavHayVenta, tisId
                      FROM";
            $queryBase = "
                      antiguedadalaventa
                      LEFT  JOIN tasacioninsitu ON aavTadId = tisTadId
                                    AND tisFechaBaja IS NULL
                                    AND tisFechaTasInSituRealizada IS NOT NULL
                      LEFT  JOIN antiguedad ON aavAntId = antId
                      LEFT JOIN subcategoria ON antScatId = scatId
                      LEFT JOIN categoria ON scatCatId = catId
                      LEFT JOIN periodo ON antPerId = perId
                      LEFT JOIN usuario ON antUsrId = usrId"; // <-- sin WHERE aquí
            
            $whereClause= "((antDescripcion LIKE '%{$searchWord}%') 
                    OR (scatDescripcion LIKE '%{$searchWord}%') 
                    OR (catDescripcion LIKE '%{$searchWord}%') 
                    OR (perDescripcion LIKE '%{$searchWord}%') 
                    OR (usrApellido LIKE '%{$searchWord}%') 
                    OR (usrRazonSocialFantasia LIKE '%{$searchWord}%')) 
                    AND aavFechaRetiro IS NULL AND aavHayVenta = FALSE";

            // armar la query completa con un único WHERE
            $query = $query . $queryBase . " WHERE " . $whereClause;

            $paginadoResponseDTO = $this->getPaginadoResponseDTO(
                $paginadoSearch,
                $mysqli,
                $queryBase,
                $whereClause,
                "obtener el total de antigüedades a la venta para paginado con búsqueda",
                $query,
                AntiguedadALaVentaDTO::class
            );
            $paginadoResponseDTO->arrayEntidad = $this->completarArrayAntiguedadesAlaVentaDTO($mysqli, $paginadoResponseDTO->arrayEntidad);
            Output::outputJson($paginadoResponseDTO);

        } catch (\Throwable $th) {
            if ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } elseif ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        } finally {
            if (isset($mysqli) && $mysqli instanceof mysqli) { // Verificar si la conexión fue establecida
                $mysqli->close(); // Cerrar la conexión a la base de datos
            }
        }
    }



    public function getAntiguedadesAlaVenta()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());
            $query = "SELECT aavId, aavAntId, aavUsrIdVendedor, aavDomOrigen, aavPrecioVenta, aavTadId, aavFechaPublicacion, aavFechaRetiro, aavHayVenta, tisId
                      FROM antiguedadalaventa
                      LEFT  JOIN tasacioninsitu ON aavTadId = tisTadId
                                    AND tisFechaBaja IS NULL
                                    AND tisFechaTasInSituRealizada IS NOT NULL 
                      WHERE aavFechaRetiro IS NULL AND aavHayVenta = FALSE"; //IMPORTANTE: Solo se consideran las tasaciones in situ realizadas

            $arrayAntiguedadesAlaVentaDTO = $this->getInterno(query: $query, classDTO: AntiguedadAlaVentaDTO::class, linkExterno: $mysqli);

            // Reemplazo del bloque duplicado
            $arrayAntiguedadesAlaVentaDTO = $this->completarArrayAntiguedadesAlaVentaDTO($mysqli, $arrayAntiguedadesAlaVentaDTO);

            Output::outputJson($arrayAntiguedadesAlaVentaDTO);
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
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }

    public function getAntiguedadesAlaVentaById($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            settype($id, 'integer');
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());
            $query = "SELECT aavId, aavAntId, aavUsrIdVendedor, aavDomOrigen, aavPrecioVenta, aavTadId, aavFechaPublicacion, aavFechaRetiro, aavHayVenta, tisId
                      FROM antiguedadalaventa
                      LEFT  JOIN tasacioninsitu ON aavTadId = tisTadId
                                    AND tisFechaBaja IS NULL
                                    AND tisFechaTasInSituRealizada IS NOT NULL
                      WHERE aavId = $id AND aavFechaRetiro IS NULL AND aavHayVenta = FALSE"; //IMPORTANTE: Solo se consideran las tasaciones in situ realizadas

            $antiguedadAlaVentaDTO = $this->getByIdInterno(query: $query, classDTO: AntiguedadAlaVentaDTO::class, linkExterno: $mysqli);

            // Reemplazo por función privada para un solo DTO
            $antiguedadAlaVentaDTO = $this->completarAntiguedadAlaVentaDTO($mysqli, $antiguedadAlaVentaDTO);

            Output::outputJson($antiguedadAlaVentaDTO);
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
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }

    public function postAntiguedadesAlaVenta()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $mysqli->begin_transaction(); // Iniciar transacción
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());
            $data = Input::getArrayBody(msgEntidad: "la antigüedad a la venta");

            // TODO : Validar que lleguen los datos necesarios

            $this->antiguedadesAlaVentaValidacionService->validarType(className: AntiguedadAlaVentaCreacionDTO::class, datos: $data);

            $antiguedadAlaVentaCreacionDTO = new AntiguedadAlaVentaCreacionDTO($data);

            if (!isset($antiguedadAlaVentaCreacionDTO->antiguedad) || !isset($antiguedadAlaVentaCreacionDTO->antiguedad->antId)) {
                throw new InvalidArgumentException(code: 400, message: "La antigüedad a la venta debe tener una antigüedad asociada con su ID.");
            }

            $antiguedadAlaVentaCreacionDTO->antiguedad = $this->getByIdInterno(
                query: 'ANTIGUEDAD',
                classDTO: AntiguedadDTO::class,
                linkExterno: $mysqli,
                id: $antiguedadAlaVentaCreacionDTO->antiguedad->antId
            );

            if (!isset($antiguedadAlaVentaCreacionDTO->vendedor) || !isset($antiguedadAlaVentaCreacionDTO->vendedor->usrId)) {
                throw new InvalidArgumentException(code: 400, message: "La antigüedad a la venta debe tener un vendedor asociado con su ID.");
            }

            $antiguedadAlaVentaCreacionDTO->vendedor = $this->getByIdInterno(
                query: 'USUARIO',
                classDTO: UsuarioDTO::class,
                linkExterno: $mysqli,
                id: $antiguedadAlaVentaCreacionDTO->vendedor->usrId
            );

            if (!isset($antiguedadAlaVentaCreacionDTO->domicilioOrigen) || !isset($antiguedadAlaVentaCreacionDTO->domicilioOrigen->domId)) {
                throw new InvalidArgumentException(code: 400, message: "La antigüedad a la venta debe tener un domicilio de origen asociado con su ID.");
            }

            $antiguedadAlaVentaCreacionDTO->domicilioOrigen = $this->getByIdInterno(
                query: 'DOMICILIO',
                classDTO: DomicilioDTO::class,
                linkExterno: $mysqli,
                id: $antiguedadAlaVentaCreacionDTO->domicilioOrigen->domId
            );

            if (isset($antiguedadAlaVentaCreacionDTO->aavPrecioVenta)) {
                $antiguedadAlaVentaCreacionDTO->aavPrecioVenta = Input::redondearNumero($antiguedadAlaVentaCreacionDTO->aavPrecioVenta, 2);
            }

            if (isset($antiguedadAlaVentaCreacionDTO->tasacion->tadId)) {
                // La tasación digital debe existir y estar realizada, por eso usamos getByIdInternoAllowsNull, la query ya filtra las no realizadas
                $antiguedadAlaVentaCreacionDTO->tasacion = $this->getByIdInternoAllowsNull(
                    query: 'TASACIONDIGITAL',
                    classDTO: TasacionDigitalDTO::class,
                    linkExterno: $mysqli,
                    id: $antiguedadAlaVentaCreacionDTO->tasacion->tadId
                );

                if ($antiguedadAlaVentaCreacionDTO->tasacion != null) {
                    $queryInsitu = "SELECT tisId, tisTadId, tisDomTasId, tisFechaTasInSituSolicitada, tisFechaTasInSituProvisoria, 
                             tisFechaTasInSituRealizada, tisFechaTasInSituRechazada, tisObservacionesInSitu, tisPrecioInSitu
                    FROM tasacioninsitu
                    INNER JOIN tasaciondigital ON tisTadId = tadId
                    WHERE tisFechaBaja IS NULL
                    AND tisFechaTasInSituRealizada IS NOT NULL
                    AND tisTadId = {$antiguedadAlaVentaCreacionDTO->tasacion->tadId}"; //IMPORTANTE: Solo se consideran las tasaciones in situ realizadas

                    // Si la tasación digital tiene una tasación in situ asociada, la obtenemos (se asume que puede ser nula)
                    $antiguedadAlaVentaCreacionDTO->tasacion->tasacionInSitu = $this->getByIdInternoAllowsNull(
                        query: $queryInsitu,
                        classDTO: TasacionInSituDTO::class,
                        linkExterno: $mysqli
                    );
                }
            }

            $this->antiguedadesAlaVentaValidacionService->validarInput(
                $mysqli,
                $antiguedadAlaVentaCreacionDTO,
                $claimDTO
            );

            //Input::escaparDatos($antiguedadAlaVentaCreacionDTO, $mysqli); // No es necesario, no hay campos de tipo string
            //Input::agregarComillas_ConvertNULLtoString($antiguedadAlaVentaCreacionDTO); //No es necesario, no hay campos de tipo string

            $this->cambiarEstadoAntiguedad($mysqli, $antiguedadAlaVentaCreacionDTO->antiguedad, TipoEstadoEnum::AlaVenta());

            $tasacionId = isset($antiguedadAlaVentaCreacionDTO->tasacion->tadId) ? $antiguedadAlaVentaCreacionDTO->tasacion->tadId : "NULL";

            $query = "INSERT INTO antiguedadalaventa (aavAntId, aavUsrIdVendedor, aavDomOrigen, aavPrecioVenta, aavTadId)
                      VALUES ({$antiguedadAlaVentaCreacionDTO->antiguedad->antId}, 
                              {$antiguedadAlaVentaCreacionDTO->vendedor->usrId}, 
                              {$antiguedadAlaVentaCreacionDTO->domicilioOrigen->domId}, 
                              {$antiguedadAlaVentaCreacionDTO->aavPrecioVenta}, 
                              {$tasacionId})";

            $resultado = $mysqli->query($query);
            if ($resultado === false) {
                $error = $mysqli->error;
                throw new mysqli_sql_exception(code: 500, message: 'Falló la consulta: ' . $error);
            }
            $ret = [
                'id' => $mysqli->insert_id
            ];

            $mysqli->commit(); // Confirmar transacción si todo sale bien
            Output::outputJson($ret, 201);
        } catch (\Throwable $th) {

            if (isset($mysqli) && $mysqli instanceof mysqli) {
                $mysqli->rollback(); // Revertir transacción si hay error
            }

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
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }

    //Solo se permite modificar el domicilio de origen, el precio de venta y la tasación digital asociada
    public function patchAntiguedadesAlaVenta($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $mysqli->begin_transaction(); // Iniciar transacción
            settype($id, 'integer');
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());

            $query = "SELECT aavId, aavAntId, aavUsrIdVendedor, aavDomOrigen, aavPrecioVenta, aavTadId, aavFechaPublicacion, aavFechaRetiro, aavHayVenta, tisId
                      FROM antiguedadalaventa
                      LEFT  JOIN tasacioninsitu ON aavTadId = tisTadId
                                    AND tisFechaBaja IS NULL
                                    AND tisFechaTasInSituRealizada IS NOT NULL
                      WHERE aavId = $id AND aavFechaRetiro IS NULL AND aavHayVenta = FALSE"; //IMPORTANTE: Solo se consideran las tasaciones in situ realizadas

            $antiguedadAlaVentaDTO = $this->getByIdInterno(query: $query, classDTO: AntiguedadAlaVentaDTO::class, linkExterno: $mysqli);

            $data = Input::getArrayBody(msgEntidad: "la antigüedad a la venta");

            $data['aavId'] = $id;

            $this->antiguedadesAlaVentaValidacionService->validarType(className: AntiguedadAlaVentaDTO::class, datos: $data);
            $antiguedadAlaVentaDTOPatch = new AntiguedadAlaVentaDTO($data);

            // Actualizar solo los campos que están presentes en el cuerpo de la solicitud (PATCH)
            $antiguedadAlaVentaDTO->domicilioOrigen = isset($antiguedadAlaVentaDTOPatch->domicilioOrigen) ? $antiguedadAlaVentaDTOPatch->domicilioOrigen : $antiguedadAlaVentaDTO->domicilioOrigen;
            $antiguedadAlaVentaDTO->aavPrecioVenta = isset($antiguedadAlaVentaDTOPatch->aavPrecioVenta) ? $antiguedadAlaVentaDTOPatch->aavPrecioVenta : $antiguedadAlaVentaDTO->aavPrecioVenta;
            $antiguedadAlaVentaDTO->tasacion = isset($antiguedadAlaVentaDTOPatch->tasacion) ? $antiguedadAlaVentaDTOPatch->tasacion : $antiguedadAlaVentaDTO->tasacion;


            if (!isset($antiguedadAlaVentaDTO->antiguedad) || !isset($antiguedadAlaVentaDTO->antiguedad->antId)) {
                throw new InvalidArgumentException(code: 400, message: "La antigüedad a la venta debe tener una antigüedad asociada con su ID.");
            }

            $antiguedadAlaVentaDTO->antiguedad = $this->getByIdInterno(
                query: 'ANTIGUEDAD',
                classDTO: AntiguedadDTO::class,
                linkExterno: $mysqli,
                id: $antiguedadAlaVentaDTO->antiguedad->antId
            );

            if (!isset($antiguedadAlaVentaDTO->vendedor) || !isset($antiguedadAlaVentaDTO->vendedor->usrId)) {
                throw new InvalidArgumentException(code: 400, message: "La antigüedad a la venta debe tener un vendedor asociado con su ID.");
            }

            $antiguedadAlaVentaDTO->vendedor = $this->getByIdInterno(
                query: 'USUARIO',
                classDTO: UsuarioDTO::class,
                linkExterno: $mysqli,
                id: $antiguedadAlaVentaDTO->vendedor->usrId
            );

            if (!isset($antiguedadAlaVentaDTO->domicilioOrigen) || !isset($antiguedadAlaVentaDTO->domicilioOrigen->domId)) {
                throw new InvalidArgumentException(code: 400, message: "La antigüedad a la venta debe tener un domicilio de origen asociado con su ID.");
            }

            $antiguedadAlaVentaDTO->domicilioOrigen = $this->getByIdInterno(
                query: 'DOMICILIO',
                classDTO: DomicilioDTO::class,
                linkExterno: $mysqli,
                id: $antiguedadAlaVentaDTO->domicilioOrigen->domId
            );

            if (isset($antiguedadAlaVentaDTO->aavPrecioVenta)) {
                $antiguedadAlaVentaDTO->aavPrecioVenta = Input::redondearNumero($antiguedadAlaVentaDTO->aavPrecioVenta, 2);
            }

            if (isset($antiguedadAlaVentaDTO->tasacion) && isset($antiguedadAlaVentaDTO->tasacion->tadId)) {
                // La tasación digital debe existir y estar realizada, por eso usamos getByIdInternoAllowsNull, la query ya filtra las no realizadas
                $antiguedadAlaVentaDTO->tasacion = $this->getByIdInternoAllowsNull(
                    query: 'TASACIONDIGITAL',
                    classDTO: TasacionDigitalDTO::class,
                    linkExterno: $mysqli,
                    id: $antiguedadAlaVentaDTO->tasacion->tadId
                );

                if ($antiguedadAlaVentaDTO->tasacion != null) {
                    $queryInsitu = "SELECT tisId, tisTadId, tisDomTasId, tisFechaTasInSituSolicitada, tisFechaTasInSituProvisoria, 
                             tisFechaTasInSituRealizada, tisFechaTasInSituRechazada, tisObservacionesInSitu, tisPrecioInSitu
                    FROM tasacioninsitu
                    WHERE tisTadId = {$antiguedadAlaVentaDTO->tasacion->tadId}
                    AND tisFechaTasInSituRealizada IS NOT NULL
                    AND tisFechaBaja IS NULL"; //IMPORTANTE: Solo se consideran las tasaciones in situ realizadas
                    // Si la tasación digital tiene una tasación in situ asociada, la obtenemos (se asume que puede ser nula)
                    $antiguedadAlaVentaDTO->tasacion->tasacionInSitu =  $this->getByIdInternoAllowsNull(
                        query: $queryInsitu,
                        classDTO: TasacionInSituDTO::class,
                        linkExterno: $mysqli
                    );
                }
            }
            $this->antiguedadesAlaVentaValidacionService->validarInput(
                $mysqli,
                $antiguedadAlaVentaDTO,
                $claimDTO
            );
            //Input::escaparDatos($antiguedadAlaVentaDTO, $mysqli); // No es necesario, no hay campos de tipo string
            //Input::agregarComillas_ConvertNULLtoString($antiguedadAlaVentaDTO); //No es necesario, no hay campos de tipo string

            $query = "UPDATE antiguedadalaventa
                      SET aavDomOrigen = {$antiguedadAlaVentaDTO->domicilioOrigen->domId}, 
                          aavPrecioVenta = {$antiguedadAlaVentaDTO->aavPrecioVenta}, 
                          aavTadId = " . (isset($antiguedadAlaVentaDTO->tasacion->tadId) ? $antiguedadAlaVentaDTO->tasacion->tadId : "NULL") . "
                      WHERE aavId = {$antiguedadAlaVentaDTO->aavId}";

            $resultado = $mysqli->query($query);
            if ($resultado === false) {
                $error = $mysqli->error;
                throw new mysqli_sql_exception(code: 500, message: 'Falló la consulta: ' . $error);
            }
            $ret = [
                'id' => $antiguedadAlaVentaDTO->aavId
            ];
            $mysqli->commit(); // Confirmar transacción si todo sale bien
            Output::outputJson($ret, 200);
        } catch (\Throwable $th) {
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                $mysqli->rollback(); // Revertir transacción en caso de error
            }


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
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }

    // La eliminación de una antigüedad a la venta implica marcar la fecha de retiro y cambiar el estado de la antigüedad a "Retirado - No disponible"
    function deleteAntiguedadesAlaVenta($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $mysqli->begin_transaction(); // Iniciar transacción
            settype($id, 'integer');
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());

            
            $condicionWhere = "";
            if (!TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico()){
                // Si no es soporte técnico, solo puede retirar su propia antigüedad a la venta
                $condicionWhere = " AND aavUsrIdVendedor = {$claimDTO->usrId} ";
            }

            $aavAntId = Querys::existeEnBD( link: $mysqli,
                                query: "SELECT aavAntId FROM antiguedadalaventa WHERE aavId = $id AND aavFechaRetiro IS NULL AND aavHayVenta = FALSE $condicionWhere",
                                msg: 'establecer si la antigüedad a la venta existe',
                                columnId: 'aavAntId');

           if($aavAntId === 0)
            {
                throw new CustomException(code: 404, message: "No existe una antigüedad a la venta activa con el ID proporcionado" . (TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico() ? "" : " para el usuario logueado") . ".");
            }

            $queryUpdate = "UPDATE antiguedadalaventa
                            SET aavFechaRetiro = NOW()
                            WHERE aavId = $id AND aavFechaRetiro IS NULL AND aavHayVenta = FALSE $condicionWhere";
            $resultado = $mysqli->query($queryUpdate);
            if ($resultado === false) {
                $error = $mysqli->error;
                throw new mysqli_sql_exception(code: 500, message: 'Falló la consulta: ' . $error);
            }

            $this->cambiarEstadoAntiguedad($mysqli, new AntiguedadDTO(['antId' => $aavAntId]), TipoEstadoEnum::RetiradoNoDisponible());

            $ret = [
                'id' => $id
            ];

            $mysqli->commit(); // Confirmar transacción si todo sale bien
            Output::outputJson($ret, 200);

        } catch (\Throwable $th) {
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                $mysqli->rollback(); // Revertir transacción en caso de error
            }
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
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }

    /**
     * Completa vendedor, domicilioOrigen, antiguedad (+imagenes) y tasacion (+tasacionInSitu)
     * para cada AntiguedadAlaVentaDTO del arreglo.
     */
    private function completarArrayAntiguedadesAlaVentaDTO(mysqli $mysqli, array $arrayAntiguedadesAlaVentaDTO): array
    {
        foreach ($arrayAntiguedadesAlaVentaDTO as $antiguedadAlaVentaDTO) {
            $antiguedadAlaVentaDTO->vendedor = $this->getByIdInterno(
                query: 'USUARIO',
                classDTO: UsuarioDTO::class,
                linkExterno: $mysqli,
                id: $antiguedadAlaVentaDTO->vendedor->usrId
            );

            $antiguedadAlaVentaDTO->domicilioOrigen = $this->getByIdInterno(
                query: 'DOMICILIO',
                classDTO: DomicilioDTO::class,
                linkExterno: $mysqli,
                id: $antiguedadAlaVentaDTO->domicilioOrigen->domId
            );

            $antiguedadAlaVentaDTO->antiguedad = $this->getByIdInterno(
                query: 'ANTIGUEDAD',
                classDTO: AntiguedadDTO::class,
                linkExterno: $mysqli,
                id: $antiguedadAlaVentaDTO->antiguedad->antId
            );

            $antiguedadAlaVentaDTO->antiguedad->imagenes = $this->getInterno(
                query: "SELECT imaId, imaUrl, imaAntId, imaOrden, imaNombreArchivo FROM imagenantiguedad WHERE imaAntId = {$antiguedadAlaVentaDTO->antiguedad->antId} ORDER BY imaOrden",
                classDTO: ImagenAntiguedadDTO::class,
                linkExterno: $mysqli
            );

            if (isset($antiguedadAlaVentaDTO->tasacion->tadId)) {
                // La tasación digital debe existir y estar realizada; la query ya filtra las no realizadas
                $antiguedadAlaVentaDTO->tasacion = $this->getByIdInternoAllowsNull(
                    query: 'TASACIONDIGITAL',
                    classDTO: TasacionDigitalDTO::class,
                    linkExterno: $mysqli,
                    id: $antiguedadAlaVentaDTO->tasacion->tadId
                );

                if ($antiguedadAlaVentaDTO->tasacion != null) {
                    $queryInsitu = "SELECT tisId, tisTadId, tisDomTasId, tisFechaTasInSituSolicitada, tisFechaTasInSituProvisoria, 
                             tisFechaTasInSituRealizada, tisFechaTasInSituRechazada, tisObservacionesInSitu, tisPrecioInSitu
                    FROM tasacioninsitu
                    INNER JOIN tasaciondigital ON tisTadId = tadId
                    WHERE tisFechaBaja IS NULL
                    AND tisFechaTasInSituRealizada IS NOT NULL 
                    AND tisTadId = {$antiguedadAlaVentaDTO->tasacion->tadId}";

                    // Puede ser nula
                    $antiguedadAlaVentaDTO->tasacion->tasacionInSitu = $this->getByIdInternoAllowsNull(
                        query: $queryInsitu,
                        classDTO: TasacionInSituDTO::class,
                        linkExterno: $mysqli
                    );
                }
            }
        }

        return $arrayAntiguedadesAlaVentaDTO;
    }

    /**
     * Completa vendedor, domicilioOrigen, antiguedad (+imagenes) y tasacion (+tasacionInSitu)
     * para un solo AntiguedadAlaVentaDTO.
     */
    private function completarAntiguedadAlaVentaDTO(mysqli $mysqli, AntiguedadAlaVentaDTO $antiguedadAlaVentaDTO): AntiguedadAlaVentaDTO
    {
        // Reutiliza la misma lógica del método que opera sobre arrays
        $array = $this->completarArrayAntiguedadesAlaVentaDTO($mysqli, [$antiguedadAlaVentaDTO]);
        return $array[0];
    }

}

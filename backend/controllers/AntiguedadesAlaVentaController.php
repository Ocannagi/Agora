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

    private static $instancia = null; // La única instancia de la clase

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $antiguedadesAlaVentaValidacionService)
    {
        parent::__construct($dbConnection);
        $this->securityService = $securityService;
        $this->antiguedadesAlaVentaValidacionService = $antiguedadesAlaVentaValidacionService;
    }

    public static function getInstance(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $antiguedadesAlaVentaValidacionService): AntiguedadesAlaVentaController
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


        $query = "SELECT aavId, aavAntId, aavDomOrigen, aavPrecioVenta, aavTadId, aavFechaPublicacion, aavFechaRetiro, aavHayVenta, tisId
                      FROM antiguedadesalaventa
                      INNER JOIN antiguedades ON aavAntId = antId
                      INNER JOIN subcategorias ON antScatId = scatId
                      INNER JOIN categorias ON scatCatId = catId
                      INNER JOIN usuarios ON antUsrId = usrId
                      LEFT  JOIN tasacioninsitu ON aavTadId = tisTadId
                                    AND tisFechaBaja IS NULL
                                    AND tisFechaTasInSituRealizada IS NOT NULL --IMPORTANTE: Solo se consideran las tasaciones in situ realizadas
                      WHERE aavFechaRetiro IS NULL AND aavHayVenta = FALSE $whereAdicional";

        $arrayAntiguedadesAlaVentaDTO = $this->getInterno(query: $query, classDTO: AntiguedadAlaVentaDTO::class, linkExterno: $mysqli);

        foreach ($arrayAntiguedadesAlaVentaDTO as $antiguedadAlaVentaDTO) {
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
                    INNER JOIN tasaciondigital ON tisTadId = tadId
                    WHERE tisFechaBaja IS NULL
                    AND tisFechaTasInSituRealizada IS NOT NULL --IMPORTANTE: Solo se consideran las tasaciones in situ realizadas
                    AND tisTadId = {$antiguedadAlaVentaDTO->tasacion->tadId}";

                    // Si la tasación digital tiene una tasación in situ asociada, la obtenemos (se asume que puede ser nula)
                    $antiguedadAlaVentaDTO->tasacion->tasacionInSitu = $this->getByIdInternoAllowsNull(
                        query: $queryInsitu,
                        classDTO: TasacionInSituDTO::class,
                        linkExterno: $mysqli
                    );
                }
            }
        }

        Output::outputJson($arrayAntiguedadesAlaVentaDTO);
    }

    /** SECCION DE MÉTODOS CRUD */

    public function getAntiguedadesAlaVenta()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());
            $query = "SELECT aavId, aavAntId, aavDomOrigen, aavPrecioVenta, aavTadId, aavFechaPublicacion, aavFechaRetiro, aavHayVenta, tisId
                      FROM antiguedadesalaventa
                      LEFT  JOIN tasacioninsitu ON aavTadId = tisTadId
                                    AND tisFechaBaja IS NULL
                                    AND tisFechaTasInSituRealizada IS NOT NULL --IMPORTANTE: Solo se consideran las tasaciones in situ realizadas
                      WHERE aavFechaRetiro IS NULL AND aavHayVenta = FALSE";

            $arrayAntiguedadesAlaVentaDTO = $this->getInterno(query: $query, classDTO: AntiguedadAlaVentaDTO::class, linkExterno: $mysqli);

            foreach ($arrayAntiguedadesAlaVentaDTO as $antiguedadAlaVentaDTO) {
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
                    INNER JOIN tasaciondigital ON tisTadId = tadId
                    WHERE tisFechaBaja IS NULL
                    AND tisFechaTasInSituRealizada IS NOT NULL --IMPORTANTE: Solo se consideran las tasaciones in situ realizadas
                    AND tisTadId = {$antiguedadAlaVentaDTO->tasacion->tadId}";

                        // Si la tasación digital tiene una tasación in situ asociada, la obtenemos (se asume que puede ser nula)
                        $antiguedadAlaVentaDTO->tasacion->tasacionInSitu = $this->getByIdInternoAllowsNull(
                            query: $queryInsitu,
                            classDTO: TasacionInSituDTO::class,
                            linkExterno: $mysqli
                        );
                    }
                }
            }

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
            $query = "SELECT aavId, aavAntId, aavDomOrigen, aavPrecioVenta, aavTadId, aavFechaPublicacion, aavFechaRetiro, aavHayVenta, tisId
                      FROM antiguedadesalaventa
                      LEFT  JOIN tasacioninsitu ON aavTadId = tisTadId
                                    AND tisFechaBaja IS NULL
                                    AND tisFechaTasInSituRealizada IS NOT NULL --IMPORTANTE: Solo se consideran las tasaciones in situ realizadas
                      WHERE aavId = $id AND aavFechaRetiro IS NULL AND aavHayVenta = FALSE";

            $antiguedadAlaVentaDTO = $this->getByIdInterno(query: $query, classDTO: AntiguedadAlaVentaDTO::class, linkExterno: $mysqli);


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
                    INNER JOIN tasaciondigital ON tisTadId = tadId
                    WHERE tisFechaBaja IS NULL
                    AND tisFechaTasInSituRealizada IS NOT NULL --IMPORTANTE: Solo se consideran las tasaciones in situ realizadas
                    AND tisTadId = {$antiguedadAlaVentaDTO->tasacion->tadId}";

                    // Si la tasación digital tiene una tasación in situ asociada, la obtenemos (se asume que puede ser nula)
                    $antiguedadAlaVentaDTO->tasacion->tasacionInSitu = $this->getByIdInternoAllowsNull(
                        query: $queryInsitu,
                        classDTO: TasacionInSituDTO::class,
                        linkExterno: $mysqli
                    );
                }
            }

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

            $this->antiguedadesAlaVentaValidacionService->validarType(className: AntiguedadAlaVentaCreacionDTO::class, datos: $data);
            $antiguedadAlaVentaCreacionDTO = new AntiguedadAlaVentaCreacionDTO($data);

            $antiguedadAlaVentaCreacionDTO->antiguedad = $this->getByIdInterno(
                query: 'ANTIGUEDAD',
                classDTO: AntiguedadDTO::class,
                linkExterno: $mysqli,
                id: $antiguedadAlaVentaCreacionDTO->antiguedad->antId
            );

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
                    AND tisFechaTasInSituRealizada IS NOT NULL --IMPORTANTE: Solo se consideran las tasaciones in situ realizadas
                    AND tisTadId = {$antiguedadAlaVentaCreacionDTO->tasacion->tadId}";

                    // Si la tasación digital tiene una tasación in situ asociada, la obtenemos (se asume que puede ser nula)
                    $antiguedadAlaVentaCreacionDTO->tasacion->tasacionInSitu = $this->getByIdInternoAllowsNull(
                        query: $queryInsitu,
                        classDTO: TasacionInSituDTO::class,
                        linkExterno: $mysqli
                    );
                }
            }

            $this->antiguedadesAlaVentaValidacionService->validarInput(
                linkExterno: $mysqli,
                entidadDTO: $antiguedadAlaVentaCreacionDTO,
                extraParams: $claimDTO
            );

            //Input::escaparDatos($antiguedadAlaVentaCreacionDTO, $mysqli); // No es necesario, no hay campos de tipo string
            //Input::agregarComillas_ConvertNULLtoString($antiguedadAlaVentaCreacionDTO); //No es necesario, no hay campos de tipo string

            $this->cambiarEstadoAntiguedad($mysqli, $antiguedadAlaVentaCreacionDTO->antiguedad, TipoEstadoEnum::AlaVenta());

            $tasacionId = isset($antiguedadAlaVentaCreacionDTO->tasacion->tadId) ? $antiguedadAlaVentaCreacionDTO->tasacion->tadId : "NULL";

            $query = "INSERT INTO antiguedadesalaventa (aavAntId, aavDomOrigen, aavPrecioVenta, aavTadId)
                      VALUES ({$antiguedadAlaVentaCreacionDTO->antiguedad->antId}, 
                              {$antiguedadAlaVentaCreacionDTO->domicilioOrigen->domId}, 
                              {$antiguedadAlaVentaCreacionDTO->aavPrecioVenta}, 
                              {$tasacionId}";

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

    public function patchAntiguedadesAlaVenta()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $mysqli->begin_transaction(); // Iniciar transacción
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());
            $data = Input::getArrayBody(msgEntidad: "la antigüedad a la venta");

            $this->antiguedadesAlaVentaValidacionService->validarType(className: AntiguedadAlaVentaDTO::class, datos: $data);
            $antiguedadAlaVentaDTO = new AntiguedadAlaVentaDTO($data);

            $antiguedadAlaVentaDTO->antiguedad = $this->getByIdInterno(
                query: 'ANTIGUEDAD',
                classDTO: AntiguedadDTO::class,
                linkExterno: $mysqli,
                id: $antiguedadAlaVentaDTO->antiguedad->antId
            );

            $antiguedadAlaVentaDTO->domicilioOrigen = $this->getByIdInterno(
                query: 'DOMICILIO',
                classDTO: DomicilioDTO::class,
                linkExterno: $mysqli,
                id: $antiguedadAlaVentaDTO->domicilioOrigen->domId
            );

            if (isset($antiguedadAlaVentaDTO->aavPrecioVenta)) {
                $antiguedadAlaVentaDTO->aavPrecioVenta = Input::redondearNumero($antiguedadAlaVentaDTO->aavPrecioVenta, 2);
            }

            if (isset($antiguedadAlaVentaDTO->tasacion->tadId)) {
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
                    AND tisFechaTasInSituRealizada IS NOT NULL --IMPORTANTE: Solo se consideran las tasaciones in situ realizadas
                    AND tisFechaBaja IS NULL";
                    // Si la tasación digital tiene una tasación in situ asociada, la obtenemos (se asume que puede ser nula)
                    $antiguedadAlaVentaDTO->tasacion->tasacionInSitu =  $this->getByIdInternoAllowsNull(
                        query: $queryInsitu,
                        classDTO: TasacionInSituDTO::class,
                        linkExterno: $mysqli
                    );
                }
            }
            $this->antiguedadesAlaVentaValidacionService->validarInput(
                linkExterno: $mysqli,
                entidadDTO: $antiguedadAlaVentaDTO,
                extraParams: $claimDTO
            );
            //Input::escaparDatos($antiguedadAlaVentaDTO, $mysqli); // No es necesario, no hay campos de tipo string
            //Input::agregarComillas_ConvertNULLtoString($antiguedadAlaVentaDTO); //No es necesario, no hay campos de tipo string

            if (isset($antiguedadAlaVentaDTO->aavFechaRetiro))
                $this->cambiarEstadoAntiguedad($mysqli, $antiguedadAlaVentaDTO->antiguedad, TipoEstadoEnum::RetiradoNoDisponible());

            $query = "UPDATE antiguedadesalaventa
                      SET aavAntId = {$antiguedadAlaVentaDTO->antiguedad->antId}, 
                          aavDomOrigen = {$antiguedadAlaVentaDTO->domicilioOrigen->domId}, 
                          aavPrecioVenta = {$antiguedadAlaVentaDTO->aavPrecioVenta}, 
                          aavTadId = " . (isset($antiguedadAlaVentaDTO->tasacion->tadId) ? $antiguedadAlaVentaDTO->tasacion->tadId : "NULL") . ",
                          aavFechaRetiro = " . (isset($antiguedadAlaVentaDTO->aavFechaRetiro) ? "NOW()" : "NULL") . "
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
            $mysqli->rollback(); // Revertir transacción en caso de error
            throw $th;
        } finally {
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }

    //No vamos a generar un método delete, ya que no se deben eliminar registros de antiguedades a la venta, solo retirarlas (patch)
}

<?php

use Utilidades\Output;
use Utilidades\Input;
use Model\CustomException;

class TasacionesInSituController extends BaseController
{

    use TraitGetByIdInterno;
    use TraitValidarTasacion;

    private ValidacionServiceBase $tasacionesInSituValidacionService;
    private ISecurity $securityService;

    private static $instancia = null;

    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $tasacionesInSituValidacionService)
    {
        parent::__construct($dbConnection);
        $this->securityService = $securityService;
        $this->tasacionesInSituValidacionService = $tasacionesInSituValidacionService;
    }

    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $tasacionesInSituValidacionService): TasacionesInSituController
    {
        if (self::$instancia === null) {
            self::$instancia = new TasacionesInSituController($dbConnection, $securityService, $tasacionesInSituValidacionService);
        }
        return self::$instancia;
    }

    private function __clone() {}


    public function getTasacionesInSitu()
    {
        try {
            $claimDTO = $this->securityService->requireLogin(null);
            $query = "SELECT tisId, tisTadId, tisDomTasId, tisFechaTasInSituSolicitada, tisFechaTasInSituProvisoria, 
                             tisFechaTasInSituRealizada, tisFechaTasInSituRechazada, tisObservacionesInSitu, tisPrecioInSitu,

                             tadUsrPropId, tadUsrTasId
                      FROM tasacioninsitu AS tis
                        INNER JOIN tasaciondigital AS tad ON tis.tisTadId = tad.tadId
                            AND tad.tadFechaBaja IS NULL
                      WHERE tisFechaBaja IS NULL";

            if ($claimDTO->usrTipoUsuario !== TipoUsuarioEnum::SoporteTecnico->value) {
                $query .= " AND (tad.tadUsrPropId = {$claimDTO->usrId} OR tad.tadUsrTasId = {$claimDTO->usrId})";
            }
            $query .= " ORDER BY tisId DESC";

            return parent::get($query, TasacionInSituDTO::class);
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

    public function getTasacionesInSituById(int $id)
    {
        try {
            settype($id, 'int');
            $claimDTO = $this->securityService->requireLogin(null);
            $query = "SELECT tisId, tisTadId, tisDomTasId, tisFechaTasInSituSolicitada, tisFechaTasInSituProvisoria,
                             tisFechaTasInSituRealizada, tisFechaTasInSituRechazada, tisObservacionesInSitu, tisPrecioInSitu,

                             tadUsrPropId, tadUsrTasId
                      FROM tasacioninsitu AS tis
                        INNER JOIN tasaciondigital AS tad ON tis.tisTadId = tad.tadId
                            AND tad.tadFechaBaja IS NULL
                      WHERE tisId = $id
                        AND tisFechaBaja IS NULL";

            if ($claimDTO->usrTipoUsuario !== TipoUsuarioEnum::SoporteTecnico->value) {
                $query .= " AND (tad.tadUsrPropId = {$claimDTO->usrId} OR tad.tadUsrTasId = {$claimDTO->usrId})";
            }

            return parent::getById($query, TasacionInSituDTO::class);
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

    public function postTasacionesInSitu()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::solicitanteTasacionToArray());
            $data = Input::getArrayBody(msgEntidad: "la tasación In Situ");

            $this->tasacionesInSituValidacionService->validarType(className: TasacionInSituCreacionDTO::class, datos: $data);
            $tasacionInSituCreacionDTO = new TasacionInSituCreacionDTO($data);

            $this->tasacionesInSituValidacionService->validarInput($mysqli, $tasacionInSituCreacionDTO, $claimDTO);
            Input::escaparDatos($tasacionInSituCreacionDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($tasacionInSituCreacionDTO);

            $query = "INSERT INTO tasacioninsitu (tisTadId, tisDomTasId, tisFechaTasInSituProvisoria)
                      VALUES ({$tasacionInSituCreacionDTO->tadId}, {$tasacionInSituCreacionDTO->domicilio->domId}, {$tasacionInSituCreacionDTO->tisFechaTasInSituProvisoria})";

            return parent::post($query, $mysqli);
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


    public function patchTasacionesInSitu(int $id)
    {
        /*DE MOMENTO NO SE PUEDE MODIFICAR LA FECHA PROVISORIA. SOLO SE PUEDE RECHAZAR, QUE LUEGO EL USARIO DESESTIME (Dar de BAJA) Y VUELVA A GENERAR UNA NUEVA PRESTACION*/

        $mysqli = $this->dbConnection->conectarBD();
        try {
            settype($id, 'int');
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::tasadorToArray());
            $data = Input::getArrayBody(msgEntidad: "la tasación In Situ");


            $tasacionInSituFechas = $this->getByIdInterno(
                query: "SELECT  tisFechaTasInSituSolicitada, tisFechaTasInSituProvisoria
                    FROM tasacioninsitu
                    WHERE tisId = $id
                    AND tisFechaBaja IS NULL",
                classDTO: TasacionInSituDTO::class,
                linkExterno: $mysqli
            );

            if ($tasacionInSituFechas === null) {
                Output::outputError(404, "No se encontró la tasación in situ con ID: $id");
            }

            $data['tisId'] = $id; // Aseguramos que el ID esté en los datos para la validación y creación del DTO.
            $data['tisFechaTasInSituSolicitada'] = $tasacionInSituFechas->tisFechaTasInSituSolicitada; // Mantenemos la fecha solicitada si existe.
            $data['tisFechaTasInSituProvisoria'] = $tasacionInSituFechas->tisFechaTasInSituProvisoria; // Mantenemos la fecha provisoria si existe.

            $this->tasacionesInSituValidacionService->validarType(className: TasacionInSituDTO::class, datos: $data);
            $tasacionInSituDTO = new TasacionInSituDTO($data);

            if (isset($tasacionInSituDTO->tisPrecioInSitu)) {
                $tasacionInSituDTO->tisPrecioInSitu = Input::redondearNumero($tasacionInSituDTO->tisPrecioInSitu, 2);
            }

            $this->tasacionesInSituValidacionService->validarInput($mysqli, $tasacionInSituDTO, $claimDTO);
            Input::escaparDatos($tasacionInSituDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($tasacionInSituDTO);

            $query = "UPDATE tasacioninsitu
                      SET tisFechaTasInSituRealizada = {$tasacionInSituDTO->tisFechaTasInSituRealizada},
                          tisFechaTasInSituRechazada = {$tasacionInSituDTO->tisFechaTasInSituRechazada},
                          tisObservacionesInSitu = {$tasacionInSituDTO->tisObservacionesInSitu},
                          tisPrecioInSitu = {$tasacionInSituDTO->tisPrecioInSitu}
                      WHERE tisId = {$id}";

            return parent::patch($query, $mysqli);
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

    public function deleteTasacionesInSitu(int $id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            settype($id, 'int');
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::solicitanteTasacionToArray());

            $query = $query = "SELECT tisId, tisTadId, tisDomTasId, tisFechaTasInSituSolicitada, tisFechaTasInSituProvisoria,
                             tisFechaTasInSituRealizada, tisFechaTasInSituRechazada, tisObservacionesInSitu, tisPrecioInSitu,

                             tadUsrPropId, tadUsrTasId
                      FROM tasacioninsitu AS tis
                        INNER JOIN tasaciondigital AS tad ON tis.tisTadId = tad.tadId
                            AND tad.tadFechaBaja IS NULL
                      WHERE tisId = $id
                        AND tisFechaBaja IS NULL";

            $tasacionInSituDTO = $this->getByIdInterno(query: $query, classDTO: TasacionInSituDTO::class, linkExterno: $mysqli);

            $this->validarExistencia_Solicitante($tasacionInSituDTO->tadId, $claimDTO, $mysqli);

            $queryBajaLogica = "UPDATE tasacioninsitu
                                SET tisFechaBaja = NOW()
                                WHERE tisId = $id";

            $resultado = $mysqli->query($queryBajaLogica);
            
            if ($resultado === false) {
                $error = $mysqli->error;
                throw new mysqli_sql_exception(code: 500, message: 'Falló la consulta: ' . $error);
            }

            Output::outputJson([]);

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
}

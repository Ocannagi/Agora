<?php

use Utilidades\Output;
use Utilidades\Input;
use Model\CustomException;

class TasacionesDigitalesController extends BaseController
{
    use TraitGetByIdInterno;
    use TraitGetInterno;
    use TraitCambiarEstadoAntiguedad;

    private ValidacionServiceBase $tasacionesDigitalesValidacionService;
    private ISecurity $securityService;

    private static $instancia = null;

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $tasacionesDigitalesValidacionService)
    {
        parent::__construct($dbConnection);
        $this->tasacionesDigitalesValidacionService = $tasacionesDigitalesValidacionService;
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $tasacionesDigitalesValidacionService): TasacionesDigitalesController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService, $tasacionesDigitalesValidacionService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}


    /**
     *  Obtiene todas las tasaciones digitales del usuario autenticado.
     *  Si el usuario es de tipo Soporte Técnico, obtiene todas las tasaciones digitales.
     */
    public function getTasacionesDigitales()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: null);
            $query = "SELECT tadId, tadUsrTasId, tadUsrPropId, tadAntId, tadFechaSolicitud, 
                             tadFechaTasDigitalRealizada, tadFechaTasDigitalRechazada, 
                             tadObservacionesDigital, tadPrecioDigital,

                                tisId, tisTadId, tisDomTasId, tisFechaTasInSituSolicitada,
                                tisFechaTasInSituProvisoria, tisFechaTasInSituRealizada,
                                tisFechaTasInSituRechazada, tisObservacionesInSitu, tisPrecioInSitu
                      FROM tasaciondigital AS tad
                        LEFT JOIN tasacioninsitu AS tis ON tad.tadId = tis.tisTadId
                            AND tis.tisFechaBaja IS NULL
                      WHERE tadFechaBaja IS NULL";

            if (!TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico()) {
                $query .= " AND (tadUsrPropId = {$claimDTO->usrId} OR tadUsrTasId = {$claimDTO->usrId})";
            }
            $query .= " ORDER BY tadId DESC";

            $arrayTasacionDigitalDTO = $this->getInterno(query: $query, classDTO: TasacionDigitalDTO::class);

            foreach ($arrayTasacionDigitalDTO as $tasacionDigitalDTO) {
                $tasacionDigitalDTO->tasador = $this->getByIdInterno(query: 'USUARIO', classDTO: UsuarioDTO::class, linkExterno: $mysqli, id: $tasacionDigitalDTO->tasador->usrId);
                $tasacionDigitalDTO->propietario = $this->getByIdInterno(query: 'USUARIO', classDTO: UsuarioDTO::class, linkExterno: $mysqli, id: $tasacionDigitalDTO->propietario->usrId);
                $tasacionDigitalDTO->antiguedad = $this->getByIdInterno(query: 'ANTIGUEDAD', classDTO: AntiguedadDTO::class, linkExterno: $mysqli, id: $tasacionDigitalDTO->antiguedad->antId);
            }

            Output::outputJson($arrayTasacionDigitalDTO);
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
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }

    public function getTasacionesDigitalesById($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            settype($id, 'int');
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: null);
            $query = "SELECT tadId, tadUsrTasId, tadUsrPropId, tadAntId, tadFechaSolicitud,
                             tadFechaTasDigitalRealizada, tadFechaTasDigitalRechazada,
                             tadObservacionesDigital, tadPrecioDigital,
                                tisId, tisTadId, tisDomTasId, tisFechaTasInSituSolicitada,
                                tisFechaTasInSituProvisoria, tisFechaTasInSituRealizada,
                                tisFechaTasInSituRechazada, tisObservacionesInSitu, tisPrecioInSitu
                      FROM tasaciondigital AS tad
                        LEFT JOIN tasacioninsitu AS tis ON tad.tadId = tis.tisTadId
                            AND tis.tisFechaBaja IS NULL
                      WHERE tadId = {$id} AND tadFechaBaja IS NULL";

            if (!TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico()) {
                $query .= " AND (tadUsrPropId = {$claimDTO->usrId} OR tadUsrTasId = {$claimDTO->usrId})";
            }

            $tasacionDigitalDTO = $this->getByIdInterno(query: $query, classDTO: TasacionDigitalDTO::class, linkExterno: $mysqli);

            if (!$tasacionDigitalDTO instanceof TasacionDigitalDTO) {
                throw new InvalidArgumentException(code: 404, message: "No se encontró la tasación digital con ID {$id}.");
            }

            $tasacionDigitalDTO->tasador = $this->getByIdInterno(query: 'USUARIO', classDTO: UsuarioDTO::class, linkExterno: $mysqli, id: $tasacionDigitalDTO->tasador->usrId);
            $tasacionDigitalDTO->propietario = $this->getByIdInterno(query: 'USUARIO', classDTO: UsuarioDTO::class, linkExterno: $mysqli, id: $tasacionDigitalDTO->propietario->usrId);
            $tasacionDigitalDTO->antiguedad = $this->getByIdInterno(query: 'ANTIGUEDAD', classDTO: AntiguedadDTO::class, linkExterno: $mysqli, id: $tasacionDigitalDTO->antiguedad->antId);

            Output::outputJson($tasacionDigitalDTO);
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
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }



    public function postTasacionesDigitales()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::solicitanteTasacionToArray());
            $data = Input::getArrayBody(msgEntidad: "la tasación digital");

            if (!array_key_exists('usrTasId', $data) || !array_key_exists('usrPropId', $data) || !array_key_exists('antId', $data)) {
                throw new InvalidArgumentException(code: 400, message: "Los campos 'usrTasId', 'usrPropId' y 'antId' son obligatorios para crear una tasación digital.");
            }

            $tasador = $this->getByIdInterno(query: 'USUARIO', classDTO: UsuarioDTO::class, linkExterno: $mysqli, id: $data['usrTasId']);
            $propietario = $this->getByIdInterno(query: 'USUARIO', classDTO: UsuarioDTO::class, linkExterno: $mysqli, id: $data['usrPropId']);
            $antiguedad = $this->getByIdInterno(query: 'ANTIGUEDAD', classDTO: AntiguedadDTO::class, linkExterno: $mysqli, id: $data['antId']);

            if (!$tasador instanceof UsuarioDTO || !$propietario instanceof UsuarioDTO || !$antiguedad instanceof AntiguedadDTO) {
                throw new CustomException(code: 500, message: "Error interno: No se pudo obtener uno de los objetos requeridos (Usuario o Antigüedad).");
            }

            $data['tasador'] = $tasador;
            $data['propietario'] = $propietario;
            $data['antiguedad'] = $antiguedad;

            $tasacionDigitalCreacionDTO = new TasacionDigitalCreacionDTO($data);

            if (isset($tasacionDigitalCreacionDTO->propietario->usrId)) {
                if ($claimDTO->usrTipoUsuario === TipoUsuarioEnum::UsuarioGeneral->value && $claimDTO->usrId !== $tasacionDigitalCreacionDTO->propietario->usrId) {
                    throw new InvalidArgumentException(code: 403, message: "No tienes permiso para crear una tasación digital para otro usuario.");
                }
            }

            $this->tasacionesDigitalesValidacionService->validarInput($mysqli, $tasacionDigitalCreacionDTO);
            Input::escaparDatos($tasacionDigitalCreacionDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($tasacionDigitalCreacionDTO);

            $query = "INSERT INTO tasaciondigital (tadUsrTasId, tadUsrPropId, tadAntId)
                      VALUES ({$tasacionDigitalCreacionDTO->tasador->usrId}, {$tasacionDigitalCreacionDTO->propietario->usrId}, {$tasacionDigitalCreacionDTO->antiguedad->antId})";


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


    public function patchTasacionesDigitales($id)
    {
        /*SOLO SE PUEDE MODIFICAR LAS FECHAS DE REALIZACION O RECHAZO Y EL PRECIO Y LA OBSERVACION POR ÚNICA VEZ*/
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $mysqli->begin_transaction();
            settype($id, 'int');
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::tasadorToArray());
            $data = Input::getArrayBody(msgEntidad: "la tasación digital");

            $tasacionDigitalDTO = $this->getByIdInterno(
                query: "SELECT tadUsrTasId, tadUsrPropId, tadAntId, tadFechaSolicitud
                      FROM tasaciondigital
                      WHERE tadId = $id AND tadFechaBaja IS NULL",
                classDTO: TasacionDigitalDTO::class,
                linkExterno: $mysqli
            );

            if (!$tasacionDigitalDTO instanceof TasacionDigitalDTO) {
                throw new InvalidArgumentException(code: 404, message: "No se encontró la tasación digital con ID $id.");
            }

            // Salvo que sea soporte técnico, el tasador debe ser el mismo que el usuario autenticado
            if(!TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico() && $claimDTO->usrId !== $tasacionDigitalDTO->tasador->usrId) {
                throw new InvalidArgumentException(code: 403, message: "No tienes permiso para modificar esta tasación digital.");
            }


            $tasador = $this->getByIdInterno(query: 'USUARIO', classDTO: UsuarioDTO::class, linkExterno: $mysqli, id: $tasacionDigitalDTO->tasador->usrId);
            $propietario = $this->getByIdInterno(query: 'USUARIO', classDTO: UsuarioDTO::class, linkExterno: $mysqli, id: $tasacionDigitalDTO->propietario->usrId);
            $antiguedad = $this->getByIdInterno(query: 'ANTIGUEDAD', classDTO: AntiguedadDTO::class, linkExterno: $mysqli, id: $tasacionDigitalDTO->antiguedad->antId);

            if (!$tasador instanceof UsuarioDTO || !$propietario instanceof UsuarioDTO || !$antiguedad instanceof AntiguedadDTO) {
                throw new CustomException(code: 500, message: "Error interno: No se pudo obtener uno de los objetos requeridos (Usuario o Antigüedad).");
            }


            $data['tadId'] = $id;
            $data['tadFechaSolicitud'] = $tasacionDigitalDTO->tadFechaSolicitud;
            $data['tasador'] = $tasador;
            $data['propietario'] = $propietario;
            $data['antiguedad'] = $antiguedad;

            $this->tasacionesDigitalesValidacionService->validarType(className: TasacionDigitalDTO::class, datos: $data);
            $tasacionDigitalDTO = new TasacionDigitalDTO($data);

            //Estaría repetido
/*             if ($claimDTO->usrTipoUsuario !== TipoUsuarioEnum::SoporteTecnico->value && $claimDTO->usrId !== $tasacionDigitalDTO->tasador->usrId) {
                throw new InvalidArgumentException(code: 403, message: "No tienes permiso para modificar esta tasación digital.");
            }
 */
            if (isset($tasacionDigitalDTO->tadPrecioDigital)) {
                $tasacionDigitalDTO->tadPrecioDigital = Input::redondearNumero($tasacionDigitalDTO->tadPrecioDigital, 2);
            }

            $this->tasacionesDigitalesValidacionService->validarInput($mysqli, $tasacionDigitalDTO);

            if(Input::esNotNullVacioBlanco($tasacionDigitalDTO->tadFechaTasDigitalRealizada) && !empty($tasacionDigitalDTO->tadPrecioDigital)) {
                $this->ifCambiarEstadoAntiguedadFromRDtoTD($mysqli, $tasacionDigitalDTO->antiguedad);
            }
            
            Input::escaparDatos($tasacionDigitalDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($tasacionDigitalDTO);

            $query = "UPDATE tasaciondigital
                      SET tadFechaTasDigitalRealizada = {$tasacionDigitalDTO->tadFechaTasDigitalRealizada},
                          tadFechaTasDigitalRechazada = {$tasacionDigitalDTO->tadFechaTasDigitalRechazada},
                          tadObservacionesDigital = {$tasacionDigitalDTO->tadObservacionesDigital},
                          tadPrecioDigital = {$tasacionDigitalDTO->tadPrecioDigital}
                      WHERE tadId = {$tasacionDigitalDTO->tadId}";
            
        $resultado = $mysqli->query($query);
        if ($resultado === false) {
            $error= $mysqli->error;
            throw new mysqli_sql_exception(code:500, message:'Falló la consulta: ' . $error);
        }

        $mysqli->commit(); // Confirmar transacción si todo sale bien
        $ret = [];
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

    public function deleteTasacionesDigitales($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $mysqli->begin_transaction(); // Iniciar transacción
            settype($id, 'int');
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::solicitanteTasacionToArray());

            $tasDigitalDTO = $this->getByIdInterno(query: 'TASACIONDIGITAL', classDTO: TasacionDigitalDTO::class, linkExterno: $mysqli, id: $id);

            if (!isset($tasDigitalDTO) || !$tasDigitalDTO instanceof TasacionDigitalDTO) {
                throw new InvalidArgumentException(code: 404, message: "No se encontró la tasación digital con ID {$id}.");
            }

            if (!TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico() && $claimDTO->usrId !== $tasDigitalDTO->propietario->usrId) {
                throw new InvalidArgumentException(code: 403, message: "No tienes permiso para eliminar esta tasación digital.");
            }


            $queryBajaLogica1 = "UPDATE tasacioninsitu
                                SET tisFechaBaja = NOW()
                                WHERE tisTadId = {$id}
                                AND tisFechaBaja IS NULL";


            $queryBajaLogica2 = "UPDATE tasaciondigital
                                SET tadFechaBaja = NOW()
                                WHERE tadId = {$id}
                                AND tadFechaBaja IS NULL";


            $this->deleteInterno(queryBajaLogica1: $queryBajaLogica1, queryBajaLogica2: $queryBajaLogica2, mysqli: $mysqli);
            $this->ifCambiarEstadoAntiguedadFromTItoTD($mysqli, $tasDigitalDTO);
            $this->ifCambiarEstadoAntiguedadFromTDtoRD($mysqli, $tasDigitalDTO);

            $mysqli->commit(); // Confirmar transacción si todo sale bien
            Output::outputJson([]);
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
                $mysqli->close();
            }
        }
    }


    private function deleteInterno(string $queryBajaLogica1, string $queryBajaLogica2, mysqli $mysqli)
    {
        $resultado = $mysqli->query($queryBajaLogica1);
        if ($resultado === false) {
            $error = $mysqli->error;
            throw new mysqli_sql_exception(code: 500, message: 'Falló la consulta: ' . $error);
        }

        $resultado = $mysqli->query($queryBajaLogica2);
        if ($resultado === false) {
            $error = $mysqli->error;
            throw new mysqli_sql_exception(code: 500, message: 'Falló la consulta: ' . $error);
        }
        if ($mysqli->affected_rows <= 0) {
            throw new CustomException(code: 500, message: "No se pudo efectuar la baja lógica de la tasación digital.");
        }
    }
}

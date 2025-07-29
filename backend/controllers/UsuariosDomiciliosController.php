<?php

use Utilidades\Output;
use Utilidades\Input;
use Model\CustomException;

class UsuariosDomiciliosController extends BaseController
{
    private ISecurity $securityService;
    private ValidacionServiceBase $usuariosDomiciliosValidacionService;
    private static $instancia = null; // La única instancia de la clase

    use TraitGetByIdInterno;
    use TraitGetInterno;

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $usuariosDomiciliosValidacionService)
    {
        parent::__construct($dbConnection); // Llama al constructor de la clase base
        $this->securityService = $securityService;
        $this->usuariosDomiciliosValidacionService = $usuariosDomiciliosValidacionService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $usuariosDomiciliosValidacionService): UsuariosDomiciliosController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService, $usuariosDomiciliosValidacionService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    /** SECCION DE MÉTODOS CON getUsuariosDomiciliosByParams */

    public function getUsuariosDomiciliosByParams($params)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            if (is_array($params)) {
                if (count($params) == 1 && array_key_exists('usrId', $params)) {
                    $id = $params['usrId'];
                    settype($id, 'integer');
                    $this->getUsuariosDomiciliosByUserId($id, $mysqli);
                } else {
                    Output::outputError(400, "No se recibieron parámetros válidos.");
                }
            } else {
                Output::outputError(400, "No se recibieron parámetros válidos.");
            }
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
                $mysqli->close();
            }
        }
    }

    private function getUsuariosDomiciliosByUserId(int $id, mysqli $mysqli)
    {
        $claimDTO = $this->securityService->requireLogin(tipoUsurio: null);
        settype($id, 'integer');
        

        if ($claimDTO->usrId !== $id && !in_array($claimDTO->usrTipoUsuario, TipoUsuarioEnum::soporteTecnicoToArray())) {
            throw new CustomException(code: 403, message: "No tiene permiso para acceder a los domicilios de otro usuario.");
        }

        $usuarioDomicilioArrayDTO = new stdClass();

        $usuarioDomicilioArrayDTO->usuario = $this->getByIdInterno(query: 'USUARIO', classDTO: UsuarioDTO::class, id: $id, linkExterno: $mysqli);
        $query =  "SELECT domId, domCPA, domCalleRuta, domNroKm, domPiso, domDepto,
                    locId, locDescripcion, provId, provDescripcion
                    FROM domicilio
                    INNER JOIN localidad ON domLocId = locId
                    INNER JOIN provincia ON locProvId = provId
                    INNER JOIN usuariodomicilio
                        ON udomDom = domId
                        AND udomUsr = $id
                    WHERE domFechaBaja is NULL
                    AND udomFechaBaja IS NULL
                    ORDER BY domId";

        $usuarioDomicilioArrayDTO->domicilios = $this->getInterno(
            query: $query,
            classDTO: DomicilioDTO::class,
            linkExterno: $mysqli
        );

        Output::outputJson($usuarioDomicilioArrayDTO);
    }

    /** FIN DE SECCION */

    public function getUsuariosDomicilios()
    {
        try {
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());

            $query =  "SELECT 
                        udomId,
                        usrId, usrDni, usrNombre, usrApellido
                            , domId, domCPA, domCalleRuta, domNroKm, domPiso, domDepto, locId, locDescripcion, provId, provDescripcion
                           , usrRazonSocialFantasia, usrCuitCuil, usrEmail
                           , usrTipoUsuario , usrMatricula, usrFechaNacimiento, usrDescripcion, usrScoring 
                        FROM usuariodomicilio
                        INNER JOIN usuario ON udomUsr = usrId
                        INNER JOIN domicilio ON udomDom = domId
                        LEFT JOIN localidad ON locId = domLocId
                        LEFT JOIN provincia ON provId = locProvId
                        WHERE udomFechaBaja IS NULL
                        ORDER BY udomId";

            return parent::get(query: $query, classDTO: UsuarioDomicilioDTO::class);
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
        }
    }

    public function postUsuariosDomicilios()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: null);
            $data = Input::getArrayBody(msgEntidad: "el usuarioDomicilio");

            $this->usuariosDomiciliosValidacionService->validarType(className: UsuarioDomicilioCreacionDTO::class, datos: $data);

            if ((!isset($data['usrId']) || $data['usrId'] == 0) && (!isset($data['udomUsr']) || $data['udomUsr'] == 0)) {
                $data['usrId'] = $claimDTO->usrId;
                $data['udomUsr'] = $claimDTO->usrId;
            } else {

                $id = $data['udomUsr'] ?? $data['usrId'];
                settype($id, 'integer');
                if ($claimDTO->usrId !== $id && !TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico()) {
                    throw new CustomException(code: 403, message: "No tiene permiso para agregar un domicilio a otro usuario.");
                }
            }

            $usuarioDomicilioCreacionDTO = new UsuarioDomicilioCreacionDTO($data);

            $this->usuariosDomiciliosValidacionService->validarInput($mysqli, $usuarioDomicilioCreacionDTO);

            $query = "INSERT INTO usuariodomicilio (udomUsr, udomDom) VALUES ({$usuarioDomicilioCreacionDTO->usuario->usrId}, {$usuarioDomicilioCreacionDTO->domicilio->domId})";

            return parent::post(query: $query, link: $mysqli);
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
                $mysqli->close();
            }
        }
    }

    //No se permite modificar un domicilio de un usuario, sólo se puede dar de baja.

    public function deleteUsuariosDomicilios($id)
    {
        try {
            $claimDTO = $this->securityService->requireLogin(null);
            settype($id, 'integer');

            $queryBusqueda = "SELECT udomId FROM usuariodomicilio WHERE udomId = $id AND udomFechaBaja IS NULL";

            if (!TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico()) {
                $queryBusqueda .= " AND udomUsr = {$claimDTO->usrId}";
            }

            $queryBajaLogica = "UPDATE usuariodomicilio SET udomFechaBaja = NOW() WHERE udomId = $id";

            return parent::delete(
                queryBusqueda: $queryBusqueda,
                queryBajaLogica: $queryBajaLogica
            );
            
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
        }
    }
}
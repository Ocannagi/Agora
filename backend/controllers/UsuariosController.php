<?php

use Utilidades\Output;
use Utilidades\Input;
use Model\CustomException;
use Utilidades\Querys;

class UsuariosController extends BaseController
{
    private ValidacionServiceBase $usuariosValidacionService;
    private ISecurity $securityService;

    private static $instancia = null; // La única instancia de la clase

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $usuariosValidacionService)
    {
        parent::__construct($dbConnection);
        $this->usuariosValidacionService = $usuariosValidacionService;
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $usuariosValidacionService): UsuariosController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService, $usuariosValidacionService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}



    public function getUsuarios()
    {
        try {
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());
            return parent::get(query: "SELECT usrId, usrNombre, usrApellido, usrEmail, usrTipoUsuario FROM usuario WHERE usrFechaBaja is NULL", classDTO: UsuarioMinDTO::class);
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

    public function getUsuariosById($id)
    {
        try {
            settype($id, 'integer');
            $this->securityService->requireLogin(tipoUsurio: null);

            $query = "  SELECT   usrId, usrDni, usrNombre, usrApellido
                            , domId, domCPA, domCalleRuta, domNroKm, domPiso, domDepto, locId, locDescripcion, provId, provDescripcion
                           , usrRazonSocialFantasia, usrCuitCuil, usrEmail
                           , usrTipoUsuario , usrMatricula, usrFechaNacimiento, usrDescripcion, usrScoring
                    FROM usuario
                    LEFT JOIN domicilio ON usrDomicilio = domId
                    LEFT JOIN localidad ON locId = domLocId
                    LEFT JOIN provincia ON provId = locProvId
                    WHERE usrId = $id
                    AND usrFechaBaja is NULL";

            return parent::getById(query: $query, classDTO: UsuarioDTO::class);
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

    public function postUsuarios()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $data = Input::getArrayBody(msgEntidad: "el usuario");

            $this->usuariosValidacionService->validarType(className: UsuarioCreacionDTO::class, datos: $data);

            $usuarioCreacionDTO = new UsuarioCreacionDTO($data);

            $this->usuariosValidacionService->validarInput($mysqli, $usuarioCreacionDTO);
            Input::escaparDatos($usuarioCreacionDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($usuarioCreacionDTO);

            $hashPassword = "'" . $this->securityService->hashPassword($usuarioCreacionDTO->usrPassword) . "'";

            $query =    "INSERT INTO usuario (usrDni, usrApellido, usrNombre, usrRazonSocialFantasia , usrCuitCuil,
                        usrTipoUsuario, usrMatricula, usrDomicilio, usrFechaNacimiento, usrDescripcion, usrEmail,
                        usrPassword) VALUES ($usuarioCreacionDTO->usrDni, $usuarioCreacionDTO->usrApellido, $usuarioCreacionDTO->usrNombre, $usuarioCreacionDTO->usrRazonSocialFantasia, $usuarioCreacionDTO->usrCuitCuil,
                        $usuarioCreacionDTO->usrTipoUsuario, $usuarioCreacionDTO->usrMatricula, {$usuarioCreacionDTO->domicilio->domId}, $usuarioCreacionDTO->usrFechaNacimiento, $usuarioCreacionDTO->usrDescripcion, $usuarioCreacionDTO->usrEmail,
                        $hashPassword)";

            return parent::post(query: $query, link: $mysqli);
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
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


    public function patchUsuarios($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $this->securityService->requireLogin(tipoUsurio: null);
            settype($id, 'integer');

            $data = Input::getArrayBody(msgEntidad: "el usuario");

            $data['usrId'] = $id;

            $this->usuariosValidacionService->validarType(className: "UsuarioDTO", datos: $data);
            $usuarioDTO = new UsuarioDTO($data);

            $this->usuariosValidacionService->validarInput($mysqli, $usuarioDTO);

            Input::escaparDatos($usuarioDTO, $mysqli);

            $hashPassword = "'" . $this->securityService->hashPassword($usuarioDTO->usrPassword) . "'"; // Se escapa la contraseña antes de hashearla y se le agregan comillas simples para que sea un string en la consulta SQL.

            Input::agregarComillas_ConvertNULLtoString($usuarioDTO); // cuidado con el password, no usar el de usuarioDTO, usar el de la variable anterior que ya fue escapada, hasheada y se le agregaron comillas simples.

            //NO SE DEBE MODIFICAR EL TIPO DE USUARIO // usrTipoUsuario = $usuarioDTO->usrTipoUsuario, 
            $query = "UPDATE usuario SET usrDni = $usuarioDTO->usrDni, usrApellido = $usuarioDTO->usrApellido, usrNombre = $usuarioDTO->usrNombre, usrRazonSocialFantasia = $usuarioDTO->usrRazonSocialFantasia , usrCuitCuil = $usuarioDTO->usrCuitCuil,
            usrMatricula = $usuarioDTO->usrMatricula, usrDomicilio = {$usuarioDTO->domicilio->domId}, usrFechaNacimiento = $usuarioDTO->usrFechaNacimiento, usrDescripcion = $usuarioDTO->usrDescripcion,
            usrScoring = $usuarioDTO->usrScoring, usrEmail = $usuarioDTO->usrEmail,
            usrPassword = $hashPassword WHERE usrId = $usuarioDTO->usrId AND usrFechaBaja IS NULL";

            return parent::patch($query, $mysqli);
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
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

    /*
    public function patchUsuarios($id) // Usar únicamente para cambiar la contraseña
    {

        try {
            settype($id, 'integer');
            $mysqli = $this->dbConnection->conectarBD();
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error()) {
                Output::outputError(400, "El formato de datos es incorrecto");
            }
            if (empty($data)) {
                Output::outputError(400, "No se recibieron datos para crear el usuario");
            }
            $prueba  = $mysqli->real_escape_string($data['usrPassword']);
            var_dump($prueba);
            $hashPassword = "'" . $this->securityService->hashPassword($mysqli->real_escape_string($data['usrPassword'])) . "'";
            var_dump($hashPassword);

            $query = "UPDATE usuario SET usrPassword = $hashPassword WHERE usrId = $id";


            return parent::patch($query, $mysqli);
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage());
            }
        }
    }
    */

    public function deleteUsuarios($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());
            settype($id, 'integer');

            if(Querys::existeEnBD(link: $mysqli, query: "SELECT 1 FROM antiguedadalaventa WHERE aavUsrIdVendedor = $id AND aavFechaRetiro IS NULL AND aavHayVenta = FALSE", msg: "comprobar si el usuario tiene antigüedades a la venta sin retirar en la base de datos."))
                throw new CustomException(code: 400, message: "No se puede eliminar el usuario porque tiene antigüedades a la venta sin retirar en la base de datos.");

            return parent::delete(queryBusqueda: "SELECT usrId FROM usuario WHERE usrId=$id AND usrFechaBaja IS NULL", queryBajaLogica: "UPDATE usuario SET usrFechaBaja = CURRENT_TIMESTAMP() WHERE usrId=$id");
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
}

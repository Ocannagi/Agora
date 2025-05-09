<?php

use Utilidades\Output;
use Utilidades\Input;

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
        $this->securityService->requireLogin(tipoUsurio: ['ST']);
        return parent::get(query: "SELECT usrId, usrNombre, usrApellido, usrEmail, usrTipoUsuario FROM usuario WHERE usrFechaBaja is NULL", classDTO: "UsuarioMinDTO");
    }

    public function getUsuariosById($id)
    {
        settype($id, 'integer');
        $this->securityService->requireLogin(tipoUsurio: null);
        return parent::getById(query: "SELECT usrId, usrDni, usrNombre, usrApellido, usrEmail, usrTipoUsuario FROM usuario WHERE usrId = $id AND usrFechaBaja is NULL", classDTO: "UsuarioDTO");
    }

    public function postUsuarios()
    {
        try {
            $mysqli = $this->dbConnection->conectarBD();
            $data = Input::getArrayBody(msgEntidad: "el usuario");

            $this->usuariosValidacionService->validarType(className: "UsuarioCreacionDTO", datos: $data);

            $usuarioCreacionDTO = new UsuarioCreacionDTO($data);
            Input::trimStringDatos($usuarioCreacionDTO);

            $this->usuariosValidacionService->validarInput($mysqli, $usuarioCreacionDTO);
            Input::escaparDatos($usuarioCreacionDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($usuarioCreacionDTO);

            $hashPassword = "'" . $this->securityService->hashPassword($usuarioCreacionDTO->usrPassword) . "'";

            $usrDomicilio = $usuarioCreacionDTO->domicilio->domId;

            $query =    "INSERT INTO usuario (usrDni, usrApellido, usrNombre, usrRazonSocialFantasia , usrCuitCuil,
                        usrTipoUsuario, usrMatricula, usrDomicilio, usrFechaNacimiento, usrDescripcion, usrEmail,
                        usrPassword) VALUES ($usuarioCreacionDTO->usrDni, $usuarioCreacionDTO->usrApellido, $usuarioCreacionDTO->usrNombre, $usuarioCreacionDTO->usrRazonSocialFantasia, $usuarioCreacionDTO->usrCuitCuil,
                        $usuarioCreacionDTO->usrTipoUsuario, $usuarioCreacionDTO->usrMatricula, $usrDomicilio, $usuarioCreacionDTO->usrFechaNacimiento, $usuarioCreacionDTO->usrDescripcion, $usuarioCreacionDTO->usrEmail,
                        $hashPassword)";

            return parent::post(query: $query, link: $mysqli);
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }


    public function patchUsuarios($id)
    {

        try {
            $this->securityService->requireLogin(tipoUsurio: null);
            settype($id, 'integer');
            $mysqli = $this->dbConnection->conectarBD();
            $data = Input::getArrayBody(msgEntidad: "el usuario");

            $data['usrId'] = $id;

            $this->usuariosValidacionService->validarType(className: "UsuarioDTO", datos: $data);
            $usuarioDTO = new UsuarioDTO($data);
            Input::trimStringDatos($usuarioDTO);

            $this->usuariosValidacionService->validarInput($mysqli, $usuarioDTO);

            Input::escaparDatos($usuarioDTO, $mysqli);

            $hashPassword = "'" . $this->securityService->hashPassword($usuarioDTO->usrPassword) . "'"; // Se escapa la contraseña antes de hashearla y se le agregan comillas simples para que sea un string en la consulta SQL.

            Input::agregarComillas_ConvertNULLtoString($usuarioDTO); // cuidado con el password, no usar el de usuarioDTO, usar el de la variable anterior que ya fue escapada, hasheada y se le agregaron comillas simples.

            $usrDomicilio = $usuarioDTO->domicilio->domId;
            
            $query = "UPDATE usuario SET usrDni = $usuarioDTO->usrDni, usrApellido = $usuarioDTO->usrApellido, usrNombre = $usuarioDTO->usrNombre, usrRazonSocialFantasia = $usuarioDTO->usrRazonSocialFantasia , usrCuitCuil = $usuarioDTO->usrCuitCuil,
            usrTipoUsuario = $usuarioDTO->usrTipoUsuario, usrMatricula = $usuarioDTO->usrMatricula, usrDomicilio = $usrDomicilio, usrFechaNacimiento = $usuarioDTO->usrFechaNacimiento, usrDescripcion = $usuarioDTO->usrDescripcion,
            usrScoring = $usuarioDTO->usrScoring, usrEmail = $usuarioDTO->usrEmail,
            usrPassword = $hashPassword WHERE usrId = $usuarioDTO->usrId AND usrFechaBaja IS NULL";

            return parent::patch($query, $mysqli);
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
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
        try {
            $this->securityService->requireLogin(tipoUsurio: ['ST']);
            settype($id, 'integer');

            return parent::delete(queryBusqueda: "SELECT usrId FROM usuario WHERE usrId=$id AND usrFechaBaja IS NULL", queryBajaLogica: "UPDATE usuario SET usrFechaBaja = CURRENT_TIMESTAMP() WHERE usrId=$id");
        } catch (\Throwable $th) {
            if ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }
}

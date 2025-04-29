<?php

use Utilidades\Output;
use Utilidades\Input;

class UsuariosController extends BaseController
{
    private IValidar $valdacionService;
    private ISecurity $securityService;

    private static $instancia = null; // La única instancia de la clase

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, IValidar $validacionService)
    {
        parent::__construct($dbConnection);
        $this->valdacionService = $validacionService;
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, IValidar $validacionService): UsuariosController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService, $validacionService); // Crea la instancia si no existe
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

    public function getUsuariosConParametros($id)
    {
        $this->securityService->requireLogin(tipoUsurio: ['ST']);
        return parent::getConParametros(query: "SELECT usrId, usrNombre, usrApellido, usrEmail, usrTipoUsuario FROM usuario WHERE usrId = $id AND usrFechaBaja is NULL", classDTO: "UsuarioDTO");
    }

    public function postUsuarios()
    {
        try {
            $mysqli = $this->dbConnection->conectarBD();
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error()) {
                Output::outputError(400, "El formato de datos es incorrecto");
            }
            if (empty($data)) {
                Output::outputError(400, "No se recibieron datos para crear el usuario");
            }

            $this->valdacionService->validarType(className: "UsuarioCreacionDTO", datos: $data);

            $usuarioCreacionDTO = new UsuarioCreacionDTO($data);

            $this->valdacionService->validarInputUsuario($mysqli, $usuarioCreacionDTO);
            Input::escaparDatos($usuarioCreacionDTO, $mysqli);
            Input::convertNULLtoString($usuarioCreacionDTO);

            $query =    "INSERT INTO usuario (usrDni, usrApellido, usrNombre, usrRazonSocialFantasia , usrCuitCuil,
                        usrTipoUsuario, usrMatricula, usrDomicilio, usrFechaNacimiento, usrDescripcion, usrEmail,
                        usrPassword) VALUES ('$usuarioCreacionDTO->usrDni', '$usuarioCreacionDTO->usrApellido', '$usuarioCreacionDTO->usrNombre', '$usuarioCreacionDTO->usrRazonSocialFantasia', '$usuarioCreacionDTO->usrCuitCuil',
                        '$usuarioCreacionDTO->usrTipoUsuario', '$usuarioCreacionDTO->usrMatricula', $usuarioCreacionDTO->usrDomicilio, '$usuarioCreacionDTO->usrFechaNacimiento', '$usuarioCreacionDTO->usrDescripcion', '$usuarioCreacionDTO->usrEmail',
                        '$usuarioCreacionDTO->usrPassword')";

            return parent::post(query: $query, link: $mysqli);

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
}

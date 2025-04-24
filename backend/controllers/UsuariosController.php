<?php
use Utilidades\Output;

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
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, IValidar $validacionService) : UsuariosController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection,$securityService,$validacionService); // Crea la instancia si no existe
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
        $mysqli = $this->dbConnection->conectarBD();
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error()) {
            Output::outputError(400, "El formato de datos es incorrecto");
        }

        $usuarioCreacionDTO = new UsuarioCreacionDTO($data);

        $this->valdacionService->validarInputUsuario($mysqli, $usuarioCreacionDTO);

    }

    
}


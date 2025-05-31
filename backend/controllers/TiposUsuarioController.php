<?php

class TiposUsuarioController extends BaseController
{
    private ISecurity $securityService;
    
    private static $instancia = null; // La única instancia de la clase


    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService) 
    {
        parent::__construct($dbConnection);
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService) : TiposUsuarioController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

     // Método para evitar la clonación del objeto
     private function __clone() {}


     /**
     * Devuelve los tipos de usuario disponibles en la base de datos.
     * Si el usuario es de tipo ST o SI, devuelve todos los tipos de usuario.
     * Si el usuario es de otro tipo, devuelve solo los tipos de usuario que no son ST o SI.
     */
    public function getTiposUsuario()
    {
        $tipoUsuario = $this->securityService->requireLogin(null)->usrTipoUsuario;
        $query = in_array($tipoUsuario, ['ST', 'SI']) ? "SELECT * FROM tipousuario" : "SELECT * FROM tipousuario WHERE ttuTipoUsuario NOT IN ('ST', 'SI')";
        return parent::get(query: $query, classDTO: "TipoUsuarioDTO");
    }
}

   
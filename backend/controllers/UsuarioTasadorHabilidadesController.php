<?php

class UsuarioTasadorHabilidadesController extends BaseController
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
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService): UsuarioTasadorHabilidadesController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    public function getUsuarioTasadorHabilidadesById($id)
    {
        $this->securityService->requireLogin(tipoUsurio: null);
        settype($id, 'integer');

        $query = "SELECT uthId, uthHabilidad, uthValor
                  FROM usuariotasadorhabilidad
                  WHERE utsUsrId = $id";

        return parent::getById(query: $query, classDTO: "UsuarioTasadorHabilidadesDTO");
    }

}
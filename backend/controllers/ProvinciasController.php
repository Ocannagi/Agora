<?php

class ProvinciasController extends BaseController
{
    private ISecurity $securityService;

    private static $instancia = null;

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService)
    {
        parent::__construct($dbConnection);
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService): ProvinciasController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}
   
    public function getProvincias()
    {
        $this->securityService->requireLogin(null);
        $query = "SELECT * FROM provincia";
        return parent::get(query: $query, classDTO: "ProvinciaDTO");
    }

    public function getProvinciasById(int $id)
    {
        $this->securityService->requireLogin(null);
        $query = "SELECT * FROM provincia WHERE provId = $id";
        return parent::get(query: $query, classDTO: "ProvinciaDTO");
    }

}
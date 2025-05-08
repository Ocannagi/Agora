<?php

use Utilidades\Output;
use Utilidades\Input;

class DomiciliosController extends BaseController
{
    private ValidacionServiceBase $domiciliosValidacionService;
    private ISecurity $securityService;

    private static $instancia = null; // La única instancia de la clase

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $domiciliosValidacionService)
    {
        parent::__construct($dbConnection);
        $this->domiciliosValidacionService = $domiciliosValidacionService;
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $domiciliosValidacionService): DomiciliosController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService, $domiciliosValidacionService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    public function getDomicilios() {
        $this->securityService->requireLogin(null);

        $query = "SELECT domId, domCalleRuta, domNroKm, domPiso, domDepto, domCPA, 
                  FROM domicilio"



    }
}

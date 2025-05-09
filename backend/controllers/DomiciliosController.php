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

        $query =  "SELECT domId, domCPA, domCalleRuta, domNroKm, domPiso, domDepto,
                    locId, locDescripcion, provId, provDescripcion
                    FROM domicilio
                    INNER JOIN localidad ON domLocId = locId
                    INNER JOIN provincia ON locProvId = provId
                    WHERE domFechaBaja is NULL
                    ORDER BY domId";

        return parent::get(query: $query, classDTO: "DomicilioDTO");
    }

    public function getDomiciliosById($id) {
        settype($id, 'integer');
        $this->securityService->requireLogin(null);

        $query =  "SELECT domId, domCPA, domCalleRuta, domNroKm, domPiso, domDepto,
                    locId, locDescripcion, provId, provDescripcion
                    FROM domicilio
                    INNER JOIN localidad ON domLocId = locId
                    INNER JOIN provincia ON locProvId = provId
                    WHERE domId = $id AND domFechaBaja is NULL";

        return parent::getById(query: $query, classDTO: "DomicilioDTO");
    }

    public function postDomicilios() {
        try {
            $this->securityService->requireLogin(null);
            $mysqli = $this->dbConnection->conectarBD();
            $data = Input::getArrayBody(msgEntidad:'el domicilio');

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
}

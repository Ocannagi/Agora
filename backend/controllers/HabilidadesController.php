<?php

class HabilidadesController extends BaseController
{
    private ISecurity $securityService;
    private static $instancia = null; // La única instancia de la clase

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService)
    {
        parent::__construct($dbConnection); // Llama al constructor de la clase base
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService): HabilidadesController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    public function getUsuarioTasadorHabilidadesById($id) : ?array
    {
        $this->securityService->requireLogin(tipoUsurio: null);
        settype($id, 'integer');

        $query = "SELECT 
                         usrId

                        ,perId, perDescripcion

                        ,scatId, catId, catDescripcion, scatDescripcion

                  FROM usuariotasadorhabilidad
                  INNER JOIN usuario ON utsUsrId = usrId
                  INNER JOIN periodo ON utsPerId = perId
                  INNER JOIN subcategoria ON utsScatId = scatId
                  INNER JOIN categoria ON scatCatId = catId
                  WHERE utsUsrId = $id";

        return parent::get(query: $query, classDTO: "HabilidadDTO"); // Llama al método get de la clase base en vez de getById porque devuelve un array
    }

    

}
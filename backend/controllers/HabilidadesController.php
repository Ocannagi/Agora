<?php

use Utilidades\Output;
use Utilidades\Input;


class HabilidadesController extends BaseController
{
    private ISecurity $securityService;
    private ValidacionServiceBase $habilidadesValidacionService;
    private static $instancia = null; // La única instancia de la clase

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $habilidadesValidacionService)
    {
        parent::__construct($dbConnection); // Llama al constructor de la clase base
        $this->securityService = $securityService;
        $this->habilidadesValidacionService = $habilidadesValidacionService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $habilidadesValidacionService): HabilidadesController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService, $habilidadesValidacionService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    /** SECCION DE MÉTODOS CON getHabilidadesByParams */


    public function getHabilidadesByParams($params)
    {
        if (is_array($params)) {

            if (count($params) == 1 && array_key_exists('usrId', $params)) {
                $id = $params['usrId'];
                settype($id, 'integer');
                return $this->getHabilidadesByUserId($id);
            } else {
                Output::outputError(400, "No se recibieron parámetros válidos.");
            }
        } else {
            Output::outputError(400, "No se recibieron parámetros válidos.");
        }
    }

    private function getHabilidadesByUserId($id): ?array
    {
        $this->securityService->requireLogin(tipoUsurio: null);
        settype($id, 'integer');

        $query = "SELECT 
                        utsId
                        
                        ,usrId

                        ,perId, perDescripcion

                        ,scatId, catId, catDescripcion, scatDescripcion

                  FROM usuariotasadorhabilidad
                  INNER JOIN usuario ON utsUsrId = usrId
                  INNER JOIN periodo ON utsPerId = perId
                  INNER JOIN subcategoria ON utsScatId = scatId
                  INNER JOIN categoria ON scatCatId = catId
                  WHERE utsUsrId = $id";

        return parent::get(query: $query, classDTO: "HabilidadMinDTO"); // Llama al método get de la clase base en vez de getById porque devuelve un array
    }


    /** FIN DE SECCION */

    public function getHabilidadesById($id)
    {
        $this->securityService->requireLogin(tipoUsurio: null);
        settype($id, 'integer');

        $query = "SELECT 
                        utsId,
                        
                        usrId, usrDni, usrNombre, usrApellido
                            , domId, domCPA, domCalleRuta, domNroKm, domPiso, domDepto, locId, locDescripcion, provId, provDescripcion
                           , usrRazonSocialFantasia, usrCuitCuil, usrEmail
                           , usrTipoUsuario , usrMatricula, usrFechaNacimiento, usrDescripcion, usrScoring
                        
                        
                        ,perId, perDescripcion

                        ,scatId, catId, catDescripcion, scatDescripcion
                        
                  FROM usuariotasadorhabilidad
                  INNER JOIN usuario ON utsUsrId = usrId
                    LEFT JOIN domicilio ON usrDomicilio = domId
                    LEFT JOIN localidad ON locId = domLocId
                    LEFT JOIN provincia ON provId = locProvId
                  INNER JOIN periodo ON utsPerId = perId
                  INNER JOIN subcategoria ON utsScatId = scatId
                  INNER JOIN categoria ON scatCatId = catId
                  WHERE utsId = $id";

        return parent::getById(query: $query, classDTO: "HabilidadDTO");
    }

    public function postHabilidades()
    {
        try {
            $claimDTO = $this->securityService->requireLogin(['ST', 'UT', 'UA']);
            $mysqli = $this->dbConnection->conectarBD();
            $data = Input::getArrayBody(msgEntidad: "la habilidad");

            $habilidadCreacionDTO = new HabilidadCreacionDTO($data);

            // El usuario tasador y el usuario anticuario sólo pueden agregar habilidades a sí mismos.
            if ($claimDTO->usrTipoUsuario == 'UT' || $claimDTO->usrTipoUsuario == 'UA') {
                $habilidadCreacionDTO->usrId = $claimDTO->usrId;
            }

            // A modo de prueba, el usuario técnico puede agregar habilidades a cualquier usuario o a sí mismo.
            if ($claimDTO->usrTipoUsuario == 'ST') {
                if ($habilidadCreacionDTO->usrId == 0)
                    $habilidadCreacionDTO->usrId = $claimDTO->usrId;
            }

            $this->habilidadesValidacionService->validarInput($mysqli, $habilidadCreacionDTO);
            $perId = $habilidadCreacionDTO->periodo->perId;
            $scatId = $habilidadCreacionDTO->subcategoria->scatId;

            $query = "INSERT INTO usuariotasadorhabilidad (utsUsrId, utsPerId, utsScatId) VALUES ($habilidadCreacionDTO->usrId, $perId, $scatId)";

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

    public function deleteHabilidades($id)
    {
        try {
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: ['ST', 'UT', 'UA']);
            settype($id, 'integer');

            if ($claimDTO->usrTipoUsuario == 'UT' || $claimDTO->usrTipoUsuario == 'UA') {
                if ($id != $claimDTO->usrId) {
                    Output::outputError(403, "No tiene permiso para eliminar la habilidad de otro usuario.");
                }
            }

            $queryBusqueda = "SELECT utsId FROM usuariotasadorhabilidad WHERE utsId = $id AND utsFechaBaja IS NULL";
            $queryBajaLogica = "UPDATE usuariotasadorhabilidad SET utsFechaBaja = CURRENT_TIMESTAMP() WHERE utsId = $id";

            return parent::delete(queryBusqueda: $queryBusqueda, queryBajaLogica: $queryBajaLogica);
            
        } catch (\Throwable $th) {
            if ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }
}

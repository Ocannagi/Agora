<?php

use Utilidades\Output;
use Utilidades\Input;

class AntiguedadesController extends BaseController
{
    private ValidacionServiceBase $antiguedadesValidacionService;
    private ISecurity $securityService;

    private static $instancia = null; // La única instancia de la clase

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $antiguedadesValidacionService)
    {
        parent::__construct($dbConnection);
        $this->antiguedadesValidacionService = $antiguedadesValidacionService;
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $antiguedadesValidacionService): AntiguedadesController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService, $antiguedadesValidacionService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    /** SECCION DE MÉTODOS CON getAntiguedadesByParams */

    public function getAntiguedadesByParams($params)
    {
        if (is_array($params)) {

            if (array_key_exists('scatId', $params) || array_key_exists('catId', $params) || array_key_exists('perId', $params) || array_key_exists('usrId', $params) || array_key_exists('antDescripcion', $params)) {
                $scatId = null;
                $catId = null;
                $perId = null;
                $usrId = null;
                $antDescripcion = null;
                if (array_key_exists('scatId', $params)) {
                    $scatId = (int)$params['scatId'];
                }
                if (array_key_exists('catId', $params)) {
                    $catId = (int)$params['catId'];
                }
                if (array_key_exists('perId', $params)) {
                    $perId = (int)$params['perId'];
                }
                if (array_key_exists('usrId', $params)) {
                    $usrId = (int)$params['usrId'];
                }
                if (array_key_exists('antDescripcion', $params)) {
                    $antDescripcion = (string)$params['antDescripcion'];
                }
                return $this->getAntiguedadesByFiltros($scatId, $catId, $perId, $usrId, $antDescripcion);
            } else {
                Output::outputError(400, "No se recibieron parámetros válidos.");
            }
        } else {
            Output::outputError(400, "No se recibieron parámetros válidos.");
        }
    }

    private function getAntiguedadesByFiltros(?int $scatId, ?int $catId, ?int $perId, ?int $usrId, ?string $antDescripcion): ?array
    {
        $this->securityService->requireLogin(tipoUsurio: null);

        $query = "SELECT antId, antDescripcion, antFechaEstado, antTipoEstado
                        ,perId, perDescripcion
                        ,scatId, catId, catDescripcion, scatDescripcion
                        ,usrId, usrNombre, usrApellido, usrEmail, usrTipoUsuario, usrRazonSocialFantasia,usrDescripcion,usrScoring,usrCuitCuil,usrMatricula
                        ,domId, domCPA, domCalleRuta, domNroKm, domPiso, domDepto
                        ,locId, locDescripcion, provId, provDescripcion
                        ,imaId, imaAntId, imaDescripcion, imaUrl
                  FROM antiguedad
                    INNER JOIN periodo ON antPerId = perId
                    INNER JOIN subcategoria ON antScatId = scatId
                    INNER JOIN categoria ON scatCatId = catId
                    INNER JOIN usuario ON antUsrId = usrId
                    INNER JOIN domicilio ON usrDomicilio = domId
                    INNER JOIN localidad ON locId = domLocId
                    INNER JOIN provincia ON provId = locProvId
                    LEFT JOIN imagenantiguedad ON antId = imaAntId
                  WHERE antTipoEstado <>'RN'";
        if ($scatId != null) {
            $query .= " AND scatId = $scatId";
        }
        if ($catId != null) {
            $query .= " AND catId = $catId";
        }
        if ($perId != null) {
            $query .= " AND perId = $perId";
        }
        if ($usrId != null) {
            $query .= " AND usrId = $usrId";
        }
        if ($antDescripcion != null) {
            $query .= " AND antDescripcion LIKE '%$antDescripcion%'";
        }

        return parent::get(query: $query, classDTO: "AntiguedadDTO");
    }

    /** FIN DE SECCION */

    public function getAntiguedades()
    {
        $this->securityService->requireLogin(tipoUsurio: null);

        $query = "SELECT antId, antDescripcion, antFechaEstado, antTipoEstado
                        ,perId, perDescripcion
                        ,scatId, catId, catDescripcion, scatDescripcion
                        ,usrId, usrNombre, usrApellido, usrEmail, usrTipoUsuario, usrRazonSocialFantasia,usrDescripcion,usrScoring,usrCuitCuil,usrMatricula
                        ,domId, domCPA, domCalleRuta, domNroKm, domPiso, domDepto
                        ,locId, locDescripcion, provId, provDescripcion
                        ,imaId, imaAntId, imaDescripcion, imaUrl
                  FROM antiguedad
                    INNER JOIN periodo ON antPerId = perId
                    INNER JOIN subcategoria ON antScatId = scatId
                    INNER JOIN categoria ON scatCatId = catId
                    INNER JOIN usuario ON antUsrId = usrId
                    INNER JOIN domicilio ON usrDomicilio = domId
                    INNER JOIN localidad ON locId = domLocId
                    INNER JOIN provincia ON provId = locProvId
                    LEFT JOIN imagenantiguedad ON antId = imaAntId
                  WHERE antTipoEstado <>'RN'";

        return parent::get(query: $query, classDTO: "AntiguedadDTO");
    }

    public function getAntiguedadesById($id)
    {
        settype($id, 'integer');
        $this->securityService->requireLogin(tipoUsurio: null);

        $query = "SELECT antId, antDescripcion, antFechaEstado, antTipoEstado
                        ,perId, perDescripcion
                        ,scatId, catId, catDescripcion, scatDescripcion
                        ,usrId, usrNombre, usrApellido, usrEmail, usrTipoUsuario, usrRazonSocialFantasia,usrDescripcion,usrScoring,usrCuitCuil,usrMatricula
                        ,domId, domCPA, domCalleRuta, domNroKm, domPiso, domDepto
                        ,locId, locDescripcion, provId, provDescripcion
                        ,imaId, imaAntId, imaDescripcion, imaUrl
                  FROM antiguedad
                    INNER JOIN periodo ON antPerId = perId
                    INNER JOIN subcategoria ON antScatId = scatId
                    INNER JOIN categoria ON scatCatId = catId
                    INNER JOIN usuario ON antUsrId = usrId
                    INNER JOIN domicilio ON usrDomicilio = domId
                    INNER JOIN localidad ON locId = domLocId
                    INNER JOIN provincia ON provId = locProvId
                    LEFT JOIN imagenantiguedad ON antId = imaAntId
                  WHERE antTipoEstado <>'RN'
                  AND antId = $id";

        return parent::getById(query: $query, classDTO: "AntiguedadDTO");
    }
}

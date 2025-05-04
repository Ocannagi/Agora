<?php

use Utilidades\Output;
use Utilidades\Input;

class SubcategoriasController extends BaseController
{
    private ValidacionServiceBase $subcategoriasValidacionService;
    private ISecurity $securityService;

    private static $instancia = null; // La única instancia de la clase

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $subcategoriasValidacionService)
    {
        parent::__construct($dbConnection);
        $this->subcategoriasValidacionService = $subcategoriasValidacionService;
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $subcategoriasValidacionService): SubcategoriasController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService, $subcategoriasValidacionService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    public function getSubcategorias()
    {
        $this->securityService->requireLogin(null);

        $query =   "SELECT scatId, scatCatId, catDescripcion, scatDescripcion
                    FROM subcategoria
                    INNER JOIN categoria ON scatCatId = catId
                    WHERE scatFechaBaja is NULL
                    ORDER BY scatId";


        return parent::get(query: $query, classDTO: "SubcategoriaDetalleDTO");
    }

    public function getSubcategoriasById($id)
    {
        settype($id, 'integer');
        $this->securityService->requireLogin(null);

        $query =   "SELECT scatId, scatCatId, catDescripcion, scatDescripcion
                    FROM subcategoria
                    INNER JOIN categoria ON scatCatId = catId
                    WHERE scatId = $id AND scatFechaBaja is NULL";

        return parent::getById(query: $query, classDTO: "SubcategoriaDetalleDTO");
    }

    public function postSubcategorias()
    {
        try {
            $this->securityService->requireLogin(tipoUsurio: ['ST']);

            $mysqli = $this->dbConnection->conectarBD();
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error()) {
                Output::outputError(400, "El formato de datos es incorrecto");
            }
            if (empty($data)) {
                Output::outputError(400, "No se recibieron datos para crear la subcategoría");
            }

            $this->subcategoriasValidacionService->validarType(className: "SubcategoriaCreacionDTO", datos: $data);
            $subcategoriaCreacionDTO = new SubcategoriaCreacionDTO($data);
            Input::trimStringDatos($subcategoriaCreacionDTO);

            $this->subcategoriasValidacionService->validarInput($mysqli,$subcategoriaCreacionDTO);



            $subcategoriaCreacionDTO->scatDescripcion = Input::cadaPalabraMayuscula($subcategoriaCreacionDTO->scatDescripcion);

            Input::escaparDatos($subcategoriaCreacionDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($subcategoriaCreacionDTO);

            $query =   "INSERT INTO subcategoria (scatCatId, scatDescripcion)
                        VALUES ($subcategoriaCreacionDTO->scatCatId, $subcategoriaCreacionDTO->scatDescripcion)";
            
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

    public function patchSubcategorias($id)
    {
        try {
            $this->securityService->requireLogin(tipoUsurio: ['ST']);
            settype($id, 'integer');

            $mysqli = $this->dbConnection->conectarBD();
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error()) {
                Output::outputError(400, "El formato de datos es incorrecto");
            }
            if (empty($data)) {
                Output::outputError(400, "No se recibieron datos para modificar la subcategoría");
            }

            $data['scatId'] = $id;

            $this->subcategoriasValidacionService->validarType(className: "SubcategoriaDTO", datos: $data);
            $subcategoriaDTO = new SubcategoriaDTO($data);
            Input::trimStringDatos($subcategoriaDTO);

            $this->subcategoriasValidacionService->validarInput($mysqli,$subcategoriaDTO);

            $subcategoriaDTO->scatDescripcion = Input::cadaPalabraMayuscula($subcategoriaDTO->scatDescripcion);

            Input::escaparDatos($subcategoriaDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($subcategoriaDTO);

            $query =   "UPDATE subcategoria
                        SET scatCatId = $subcategoriaDTO->scatCatId,
                            scatDescripcion = $subcategoriaDTO->scatDescripcion
                        WHERE scatId = $id AND scatFechaBaja IS NULL";

            return parent::patch(query: $query, link: $mysqli);

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

    public function deleteSubcategorias($id)
    {
        try {
            $this->securityService->requireLogin(tipoUsurio: ['ST']);
            settype($id, 'integer');

            $mysqli = $this->dbConnection->conectarBD();

            $queryBusqueda =   "SELECT 1 FROM subcategoria
                                WHERE scatId = $id AND scatFechaBaja IS NULL";
            
            $queryBajaLogica =   "UPDATE subcategoria
                                  SET scatFechaBaja = CURRENT_TIMESTAMP()
                                  WHERE scatId = $id";

            return parent::delete(queryBusqueda: $queryBusqueda, queryBajaLogica: $queryBajaLogica);

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

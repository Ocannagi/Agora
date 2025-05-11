<?php

use Utilidades\Output;
use Utilidades\Input;

class CategoriasController extends BaseController
{
    private ValidacionServiceBase $categoriasValidacionService;
    private ISecurity $securityService;

    private static $instancia = null; // La única instancia de la clase

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $categoriasValidacionService)
    {
        parent::__construct($dbConnection);
        $this->categoriasValidacionService = $categoriasValidacionService;
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $categoriasValidacionService): CategoriasController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService, $categoriasValidacionService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    public function getCategorias()
    {
        $this->securityService->requireLogin(null);
        return parent::get(query: "SELECT catId, catDescripcion FROM categoria WHERE catFechaBaja is NULL", classDTO: "CategoriaDTO");
    }

    public function getCategoriasById($id)
    {
        settype($id, 'integer');
        $this->securityService->requireLogin(null);
        return parent::getById(query: "SELECT catId, catDescripcion FROM categoria WHERE catId = $id AND catFechaBaja is NULL", classDTO: "CategoriaDTO");
    }

    public function postCategorias()
    {
        try {
            $this->securityService->requireLogin(tipoUsurio: ['ST']);

            $mysqli = $this->dbConnection->conectarBD();
            $data = Input::getArrayBody(msgEntidad: "la categoría");

            $this->categoriasValidacionService->validarType(className: "CategoriaCreacionDTO", datos: $data);

            $categoriaCreacionDTO = new CategoriaCreacionDTO($data);

            $this->categoriasValidacionService->validarInput($mysqli, $categoriaCreacionDTO);

            $categoriaCreacionDTO->catDescripcion = Input::cadaPalabraMayuscula($categoriaCreacionDTO->catDescripcion);

            Input::escaparDatos($categoriaCreacionDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($categoriaCreacionDTO);

            return parent::post(query: "INSERT INTO categoria (catDescripcion) VALUES ($categoriaCreacionDTO->catDescripcion)", link: $mysqli);
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

    public function patchCategorias($id)
    {
        try {
            $this->securityService->requireLogin(tipoUsurio: ['ST']);
            settype($id, 'integer');
            $mysqli = $this->dbConnection->conectarBD();
            $data = Input::getArrayBody(msgEntidad: "la categoría");

            $data['catId'] = $id;

            $this->categoriasValidacionService->validarType(className: "CategoriaDTO", datos: $data);

            $categoriaDTO = new CategoriaDTO($data);

            $this->categoriasValidacionService->validarInput($mysqli, $categoriaDTO);

            $categoriaDTO->catDescripcion = Input::cadaPalabraMayuscula($categoriaDTO->catDescripcion);

            Input::escaparDatos($categoriaDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($categoriaDTO);

            $query = "UPDATE categoria SET catDescripcion = $categoriaDTO->catDescripcion WHERE catId = $categoriaDTO->catId AND catFechaBaja IS NULL";

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

    public function deleteCategorias($id)
    {
        try {
            $this->securityService->requireLogin(tipoUsurio: ['ST']);
            settype($id, 'integer');

            return parent::delete(queryBusqueda: "SELECT 1 FROM categoria WHERE catId=$id AND catFechaBaja IS NULL", queryBajaLogica: "UPDATE categoria SET catFechaBaja = CURRENT_TIMESTAMP() WHERE catId=$id");
        
        } catch (\Throwable $th) {
            if ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }
}

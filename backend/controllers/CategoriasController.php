<?php

use Utilidades\Output;
use Utilidades\Input;
use Model\CustomException;

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
        try {
            $this->securityService->requireLogin(null);
            return parent::get(query: "SELECT catId, catDescripcion FROM categoria WHERE catFechaBaja is NULL", classDTO: "CategoriaDTO");
        } catch (\Throwable $th) {
            if ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } elseif ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }

    public function getCategoriasById($id)
    {
        try {
            settype($id, 'integer');
            $this->securityService->requireLogin(null);
            return parent::getById(query: "SELECT catId, catDescripcion FROM categoria WHERE catId = $id AND catFechaBaja is NULL", classDTO: "CategoriaDTO");
        } catch (\Throwable $th) {
            if ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } elseif ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }

    public function postCategorias()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());

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
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        } finally {
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si fue creada
                $mysqli->close();
            }
        }
    }

    public function patchCategorias($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());
            settype($id, 'integer');

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
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        } finally {
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si fue creada
                $mysqli->close();
            }
        }
    }

    public function deleteCategorias($id)
    {
        try {
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());
            settype($id, 'integer');

            return parent::delete(queryBusqueda: "SELECT 1 FROM categoria WHERE catId=$id AND catFechaBaja IS NULL", queryBajaLogica: "UPDATE categoria SET catFechaBaja = CURRENT_TIMESTAMP() WHERE catId=$id");
        } catch (\Throwable $th) {
            if ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } elseif ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }
}

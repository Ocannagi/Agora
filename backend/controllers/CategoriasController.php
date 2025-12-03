<?php

use Utilidades\Output;
use Utilidades\Input;
use Model\CustomException;

class CategoriasController extends BaseController
{
    private ValidacionServiceBase $categoriasValidacionService;
    private ISecurity $securityService;

    use traitGetPaginado;

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



    public function getCategoriasPaginado($paginado)
    {
        $mysqli = $this->dbConnection->conectarBD();
        $query = "SELECT catId, catDescripcion FROM categoria WHERE catFechaBaja is NULL";
        
        try {
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());
            
            $this->getPaginado($paginado, $mysqli, "categoria", "catFechaBaja is NULL", "obtener el total de categorias para paginado", $query, CategoriaMinDTO::class);
           
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
        } finally {
            if (isset($mysqli) && $mysqli instanceof mysqli) { // Verificar si la conexión fue establecida
                $mysqli->close(); // Cerrar la conexión a la base de datos
            }
        }
    }









    /** SECCION DE MÉTODOS CON getCategoriasByParams */

    public function getCategoriasByParams($params)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            if (is_array($params)) {
                if (array_key_exists('catDescripcion', $params)) {
                    $catDescripcion = null;

                    if (Input::esNotNullVacioBlanco($params['catDescripcion'])) {
                        settype($params['catDescripcion'], 'string');
                        $catDescripcion = $params['catDescripcion'];
                    }

                    return $this->getCategoriasByFiltros($mysqli, $catDescripcion);

                } else {
                    throw new InvalidArgumentException("No se enviaron los parámetros necesarios");
                }

            } else {
                throw new InvalidArgumentException("Los parámetros deben ser un array asociativo.");
            }
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        } finally {
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }

    private function getCategoriasByFiltros(mysqli $mysqli, ?string $catDescripcion)
    {
        try {
            //$this->securityService->requireLogin(null);

            $whereClauses = [];

            if (!is_null($catDescripcion)) {
                $catDescripcion = $mysqli->real_escape_string($catDescripcion);
                $whereClauses[] = "catDescripcion LIKE '%$catDescripcion%'";
            }
            
            $whereSQL = "";
            if (count($whereClauses) > 0) {
                $whereSQL = " AND " . implode(" AND ", $whereClauses);
            }

            $query =   "SELECT catId, catDescripcion
                        FROM categoria 
                        WHERE catFechaBaja is NULL" . $whereSQL;
            $query .= " ORDER BY catDescripcion ASC";
            
            return parent::get(query: $query, classDTO: CategoriaDTO::class);
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

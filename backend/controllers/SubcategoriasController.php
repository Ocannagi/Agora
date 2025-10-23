<?php

use Utilidades\Output;
use Utilidades\Input;
use Model\CustomException;

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

    /** SECCION DE MÉTODOS CON getSubcategoriasByParams */

    public function getSubcategoriasByParams($params)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            if (is_array($params)) {
                if(array_key_exists('catId', $params) && array_key_exists('scatDescripcion', $params)) {
                    $catId = null;
                    $scatDescripcion = null;

                    if (Input::esNotNullVacioBlanco($params['catId'])) {
                        settype($params['catId'], 'integer');
                        $catId = $params['catId'];
                    }
                    if (Input::esNotNullVacioBlanco($params['scatDescripcion'])) {
                        settype($params['scatDescripcion'], 'string');
                        $scatDescripcion = $params['scatDescripcion'];
                    }

                    return $this->getSubcategoriasByFiltros($mysqli, $catId, $scatDescripcion);

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

    private function getSubcategoriasByFiltros(mysqli $mysqli, ?int $catId, ?string $scatDescripcion)
    {
        try {
            //$this->securityService->requireLogin(null);

            $whereClauses = [];
            if (!is_null($catId)) {
                $whereClauses[] = "catId = $catId";
            }
            if (!is_null($scatDescripcion)) {
                $scatDescripcion = $mysqli->real_escape_string($scatDescripcion);
                $whereClauses[] = "scatDescripcion LIKE '%$scatDescripcion%'";
            }

            $whereSQL = "";
            if (count($whereClauses) > 0) {
                $whereSQL = " AND " . implode(" AND ", $whereClauses);
            }

            $query =   "SELECT scatId, scatDescripcion, catId, catDescripcion
                    FROM subcategoria
                    INNER JOIN categoria ON scatCatId = catId
                    WHERE scatFechaBaja is NULL" . $whereSQL . "
                    ORDER BY scatDescripcion ASC";


            return parent::get(query: $query, classDTO: SubcategoriaDTO::class);
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

    public function getSubcategorias()
    {
        try {
            $this->securityService->requireLogin(null);

            $query =   "SELECT scatId, catId, catDescripcion, scatDescripcion
                    FROM subcategoria
                    INNER JOIN categoria ON scatCatId = catId
                    WHERE scatFechaBaja is NULL
                    ORDER BY scatId";


            return parent::get(query: $query, classDTO: "SubcategoriaDTO");
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

    public function getSubcategoriasById($id)
    {
        try {
            settype($id, 'integer');
            $this->securityService->requireLogin(null);

            $query =   "SELECT scatId, catId, catDescripcion, scatDescripcion
                    FROM subcategoria
                    INNER JOIN categoria ON scatCatId = catId
                    WHERE scatId = $id AND scatFechaBaja is NULL";

            return parent::getById(query: $query, classDTO: "SubcategoriaDTO");
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

    public function postSubcategorias()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());

            $data = Input::getArrayBody(msgEntidad: "la subcategoría");

            $this->subcategoriasValidacionService->validarType(className: "SubcategoriaCreacionDTO", datos: $data);
            if (array_key_exists('categoria', $data)) {
                $this->subcategoriasValidacionService->validarType(className: "CategoriaDTO", datos: $data['categoria']);
            }

            $subcategoriaCreacionDTO = new SubcategoriaCreacionDTO($data);

            $this->subcategoriasValidacionService->validarInput($mysqli, $subcategoriaCreacionDTO);

            $subcategoriaCreacionDTO->scatDescripcion = Input::cadaPalabraMayuscula($subcategoriaCreacionDTO->scatDescripcion);

            Input::escaparDatos($subcategoriaCreacionDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($subcategoriaCreacionDTO);

            $catId = $subcategoriaCreacionDTO->categoria->catId;

            $query =   "INSERT INTO subcategoria (scatCatId, scatDescripcion)
                        VALUES ($catId, $subcategoriaCreacionDTO->scatDescripcion)";

            return parent::post(query: $query, link: $mysqli);
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
                $mysqli->close();
            }
        }
    }

    public function patchSubcategorias($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());
            settype($id, 'integer');

            $data = Input::getArrayBody(msgEntidad: "la subcategoría");

            $data['scatId'] = $id;

            $this->subcategoriasValidacionService->validarType(className: "SubcategoriaDTO", datos: $data);
            if (array_key_exists('categoria', $data)) {
                $this->subcategoriasValidacionService->validarType(className: "CategoriaDTO", datos: $data['categoria']);
            }

            $subcategoriaDTO = new SubcategoriaDTO($data);

            $this->subcategoriasValidacionService->validarInput($mysqli, $subcategoriaDTO);

            $subcategoriaDTO->scatDescripcion = Input::cadaPalabraMayuscula($subcategoriaDTO->scatDescripcion);

            Input::escaparDatos($subcategoriaDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($subcategoriaDTO);

            $catId = $subcategoriaDTO->categoria->catId;

            $query =   "UPDATE subcategoria
                        SET scatCatId = $catId,
                            scatDescripcion = $subcategoriaDTO->scatDescripcion
                        WHERE scatId = $id AND scatFechaBaja IS NULL";

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
                $mysqli->close();
            }
        }
    }

    public function deleteSubcategorias($id)
    {
        try {
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());
            settype($id, 'integer');

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
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }
}

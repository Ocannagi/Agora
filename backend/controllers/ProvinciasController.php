<?php

use Model\CustomException;
use Utilidades\Output;
use Utilidades\Input;

class ProvinciasController extends BaseController
{
    private ISecurity $securityService;

    private static $instancia = null;

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService)
    {
        parent::__construct($dbConnection);
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService): ProvinciasController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    /** SECCION DE MÉTODOS CON getProvinciasByParams */

    public function getProvinciasByParams($params)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            if (is_array($params)) {
                if (array_key_exists('provDescripcion', $params)) {
                    $provDescripcion = null;

                    if (Input::esNotNullVacioBlanco($params['provDescripcion'])) {
                        settype($params['provDescripcion'], 'string');
                        $provDescripcion = $params['provDescripcion'];
                    }

                    return $this->getProvinciasByFiltros($mysqli, $provDescripcion);

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

    private function getProvinciasByFiltros(mysqli $mysqli, ?string $provDescripcion)
    {
        try {
            //$this->securityService->requireLogin(null);

            $whereClauses = [];

            if (!is_null($provDescripcion)) {
                $provDescripcion = $mysqli->real_escape_string($provDescripcion);
                $whereClauses[] = "provDescripcion LIKE '%$provDescripcion%'";
            }
            if (count($whereClauses) > 0) {
                $whereSql = " WHERE " . implode(" AND ", $whereClauses);
            } else {
                $whereSql = "";
            }

            $query =   "SELECT provId, provDescripcion
                    FROM provincia";
            $query .= $whereSql;
            $query .= " ORDER BY provDescripcion ASC";
            
            return parent::get(query: $query, classDTO: ProvinciaDTO::class);
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

    public function getProvincias()
    {
        try {
            $this->securityService->requireLogin(null);
            $query = "SELECT * FROM provincia";
            return parent::get(query: $query, classDTO: "ProvinciaDTO");
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

    public function getProvinciasById(int $id)
    {
        try {
            $this->securityService->requireLogin(null);
            $query = "SELECT * FROM provincia WHERE provId = $id";
            return parent::get(query: $query, classDTO: "ProvinciaDTO");
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

<?php

require_once(__DIR__ . '/../JWT/vendor/autoload.php');
require_once(__DIR__ . '/../../config/config.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Utilidades\Output;
use Model\CustomException;
use Firebase\JWT\ExpiredException;

class SecurityService implements ISecurity
{
    private IDbConnection $dbConnection;
    private static $instancia = null;

    private function __construct(IDbConnection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }


    public static function getInstancia(IDbConnection $dbConnection)
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection);
        }
        return self::$instancia;
    }

    private function __clone() {}


    /**
     * Constata que el token de seguridad es válido y no ha expirado.
     * Si el token es válido, devuelve un ClaimDTO con los datos del usuario.
     * @param array|null $tipoUsurio
     * Los tipos de usuario que pueden acceder a este recurso.
     * Si es null, no se valida el tipo de usuario.
     * @return ClaimDTO
     * Los datos del usuario.
     * @throws Exception
     * Si el token no es válido o ha expirado, lanza una excepción.
     * Si el tipo de usuario no es válido, lanza una excepción.
     */
    public function requireLogin(?array $tipoUsurio): ClaimDTO
    {
        $authHeader = $this->getAuthorizationHeader();
        //var_dump($authHeader);
        try {
            list($jwt) = @sscanf($authHeader, 'Bearer %s');
            if (!$jwt)
                throw new CustomException(code: 401, message: "El token de seguridad está vacío");
            $datos = JWT::decode($jwt, new Key(JWT_KEY, JWT_ALG)); // si esta expirado el token, lanza excepción;

            if ($tipoUsurio !== null) {
                if (!in_array($datos->usrTipoUsuario, $tipoUsurio)) {
                    throw new CustomException(code: 403, message: "No tiene permisos para acceder a este recurso.");
                }
            }
            $mysqli = $this->dbConnection->conectarBD();
            $jwtSql = $mysqli->real_escape_string($jwt);
            $resultado = $mysqli->query("SELECT 1 FROM tokens WHERE tokToken = '$jwtSql'");
            if (!$resultado) {
                throw new mysqli_sql_exception(code: 500, message: $mysqli->error);
            } elseif ($resultado->num_rows != 1) {
                throw new CustomException(code: 403, message: "No tiene permisos para acceder a este recurso.");
            }
            $mysqli->close();
            return new ClaimDTO($datos);
        } catch (\Throwable $e) {
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                $mysqli->close();
            }

            if($e instanceof ExpiredException) {
                throw new CustomException(code: 401, message: "El token está vencido. Vuelva a loguearse por favor.");
            } else if (!($e instanceof CustomException) && !($e instanceof mysqli_sql_exception)) {
                throw new CustomException(code: 401, message: "El token de seguridad no es válido: " . $e->getMessage());
            } else {
                throw $e; // Re-lanzar la excepción si es CustomException o mysqli_sql_exception
            }
        }
    }

    private function getAuthorizationHeader(): ?string
    {
        // Intenta getallheaders() si existe (Apache)
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            return $headers['Authorization'] ?? null;
        }
        
        // Fallback para CGI/FastCGI
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }
        
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
        
        // Apache pasa el header como REDIRECT_
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            return $headers['Authorization'] ?? null;
        }
        
        return null;
    }

    public function tokenGenerator(array $data): string
    {
        return JWT::encode($data, JWT_KEY, JWT_ALG);
    }

    public function deleteTokensExpirados(?mysqli $unLink = NULL): void
    {
        $mysqli = $unLink ?? $this->dbConnection->conectarBD();
        try {
            $segundos = JWT_EXP;
            $sql = "DELETE FROM tokens where TIMESTAMPDIFF(SECOND, tokFechaInsert,NOW()) >= $segundos";
            $resultado = $mysqli->query($sql);
            if ($resultado === false) {
                //print_r($mysqli->error);
                throw new mysqli_sql_exception(code: 500, message: "Falló la conexión al querer eliminar tokens expirados: " . $mysqli->error);
            }
            if ($unLink === NULL) {
                $mysqli->close();
                Output::outputJson([]);
            }
        } catch (\Throwable $th) {
            if ($unLink === NULL) {
                $mysqli->close();
            }
            if($th instanceof mysqli_sql_exception) {
                throw $th; // Re-lanzar la excepción si es mysqli_sql_exception
            } else {
                throw new CustomException(code: 500, message: "Error al eliminar tokens expirados: " . $th->getMessage());
            }
        }
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}

<?php

require_once(__DIR__ . '/../JWT/vendor/autoload.php');
require_once(__DIR__ . '/../../config/config.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Utilidades\Output;

class SecurityService implements ISecurity
{
    private IDbConnection $dbConnection;
    private static $instancia = null;
    
    private function __construct(IDbConnection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }


    public static function getInstancia(IDbConnection $dbConnection) {
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
    public function requireLogin(?array $tipoUsurio) : ClaimDTO
    {
        $authHeader = getallheaders();
        try {
            list($jwt) = @sscanf($authHeader['Authorization'], 'Bearer %s');
            if (!$jwt)
                Output::outputError(401, "El token de seguridad está vacío");
            $datos = JWT::decode($jwt, new Key(JWT_KEY, JWT_ALG)); // si esta expirado el token, lanza excepción;

            if ($tipoUsurio !== null) {
                if (!in_array($datos->usrTipoUsuario, $tipoUsurio)) {
                    Output::outputError(403, "No tiene permisos para acceder a este recurso.");
                }
            }
            $mysqli = $this->dbConnection->conectarBD();
            $jwtSql = $mysqli->real_escape_string($jwt);
            $resultado = $mysqli->query("SELECT 1 FROM tokens WHERE tokToken = '$jwtSql'");
            if (!$resultado) {
                Output::outputError(500, $mysqli->error);
            } elseif ($resultado->num_rows != 1) {
                Output::outputError(403);
            }
            $mysqli->close();
            return new ClaimDTO($datos);
        } catch (Exception $e) {
            Output::outputError(401, "El token puede estar vencido. Vuelva a loguearse por favor.");
        }
    }

    public function tokenGenerator(array $data): string
    {
        return JWT::encode($data, JWT_KEY, JWT_ALG);
    }

    public function deleteTokensExpirados(?mysqli $unLink = NULL) : void
    {
        $mysqli = $unLink ?? $this->dbConnection->conectarBD();

        $segundos = JWT_EXP;
        $sql = "DELETE FROM tokens where TIMESTAMPDIFF(SECOND, tokFechaInsert,NOW()) >= $segundos";
        $resultado = $mysqli->query($sql);
        if ($resultado === false) {
            //print_r($mysqli->error);
            Output::outputError(500, "Falló la conexión al querer eliminar tokens expirados: " . $mysqli->error);
        }
        if ($unLink === NULL) {
            $mysqli->close();
            Output::outputJson([]);
        }
    }
}

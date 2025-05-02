<?php

use Utilidades\Output;

class LoginController
{
    private IDbConnection $dbConnection;
    private ISecurity $securityService;

    private static $instancia = null;

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService)
    {
        $this->dbConnection = $dbConnection;
        $this->securityService = $securityService;
    }

    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService)
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService);
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    public function postLogin()
    {
        try {
            $link = $this->dbConnection->conectarBD();
            $this->securityService->deleteTokensExpirados($link);
            $loginData = json_decode(file_get_contents("php://input"), true);
            $usrEmail = $link->real_escape_string($loginData['usrEmail']);
            $query = "SELECT usrId, usrNombre, usrTipoUsuario, usrPassword FROM usuario WHERE usrEmail='$usrEmail'";
            $resultado = $link->query($query);

            if ($resultado === false) {
                $error = $link->error;
                $link->close();
                Output::outputError(500, $error);
            } else if ($resultado->num_rows != 1) {
                $resultado->free();
                $link->close();
                Output::outputError(401, 'No está registrado el mail');
            } else {
                $registro = $resultado->fetch_assoc();
                $resultado->free();

                $usrPassword = $link->real_escape_string($loginData['usrPassword']);

                if (!$this->securityService->verifyPassword(password: $usrPassword, hash: $registro['usrPassword'])) {
                    $link->close();
                    Output::outputError(401, 'La contraseña es incorrecta');
                } else {
                    unset($registro["usrPassword"]);
                    $data = get_object_vars(new ClaimDTO($registro));
                    $jwt = $this->securityService->tokenGenerator($data);
                    $jwtSql = $link->real_escape_string($jwt);
                    $link->query("DELETE FROM tokens WHERE tokToken = '$jwtSql'");
                    if ($link->query("INSERT INTO tokens (tokToken) VALUES ('$jwtSql')")) {
                        $link->close();
                        Output::outputJson(['jwt' => $jwt]);
                    } else {
                        $error = $link->error;
                        $link->close();
                        Output::outputError(500, $error);
                    }
                }
            }
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage());
            }
        }
    }

    public function deleteLogin() // Logout
    {
        try {
            $this->securityService->requireLogin(null);
            $authHeader = getallheaders();
            list($jwt) = @sscanf($authHeader['Authorization'], 'Bearer %s');
            if (!$jwt)
                Output::outputError(401, "El token de seguridad está vacío");
            $link = $this->dbConnection->conectarBD();
            $jwtSql = $link->real_escape_string($jwt);
            if (!$link->query("DELETE FROM tokens WHERE tokToken = '$jwtSql'")) {
                $link->close();
                Output::outputError(403);
            }
            $link->close();
            Output::outputJson(['jwt' => []]);
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage());
            }
        }
    }
}

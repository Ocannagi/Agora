<?php

use Utilidades\Output;
use Utilidades\Input;

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
        $link = $this->dbConnection->conectarBD();
        try {
            $this->securityService->deleteTokensExpirados($link);
            $loginData = Input::getArrayBody(msgEntidad: "el login");
            $usrEmail = $link->real_escape_string($loginData['usrEmail']);
            $query = "SELECT usrId, usrNombre, usrTipoUsuario, usrPassword FROM usuario WHERE usrEmail='$usrEmail' AND usrFechaBaja IS NULL";
            $resultado = $link->query($query);

            if ($resultado === false) {
                $error = $link->error;
                throw new mysqli_sql_exception(code: 500, message: $error);
            } else if ($resultado->num_rows != 1) {
                $resultado->free();
                throw new InvalidArgumentException(code: 401, message: 'No está registrado el mail o el usuario está dado de baja');
            } else {
                $registro = $resultado->fetch_assoc();
                $resultado->free();
                $usrPassword = $loginData['usrPassword'];

                if (!$this->securityService->verifyPassword(password: $usrPassword, hash: $registro['usrPassword'])) {
                    throw new InvalidArgumentException(code: 401, message: 'La contraseña es incorrecta');
                } else {
                    unset($registro["usrPassword"]);
                    $data = get_object_vars(new ClaimDTO($registro));
                    $jwt = $this->securityService->tokenGenerator($data);
                    $jwtSql = $link->real_escape_string($jwt);
                    $link->query("DELETE FROM tokens WHERE tokToken = '$jwtSql'");
                    if ($link->query("INSERT INTO tokens (tokToken) VALUES ('$jwtSql')")) {
                        Output::outputJson(['jwt' => $jwt]);
                    } else {
                        $error = $link->error;
                        throw new mysqli_sql_exception(code: 500, message: $error);
                    }
                }
            }
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        } finally {
            if (isset($link) && $link instanceof mysqli) {
                $link->close();
            }
        }
    }

    public function deleteLogin() // Logout
    {
        $link = $this->dbConnection->conectarBD();
        try {
            $this->securityService->requireLogin(null);
            $authHeader = getallheaders();
            list($jwt) = @sscanf($authHeader['Authorization'], 'Bearer %s');
            if (!$jwt){
                //throw new InvalidArgumentException(code: 401, message: "El token de seguridad está vacío");
                Output::outputJson(['jwt' => ""]);
            }

            $jwtSql = $link->real_escape_string($jwt);
            if (!$link->query("DELETE FROM tokens WHERE tokToken = '$jwtSql'")) {
                //throw new mysqli_sql_exception(code: 403, message: $link->error);
                Output::outputJson(['jwt' => ""]);
            }
            Output::outputJson(['jwt' => ""]);
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        } finally {
            if (isset($link) && $link instanceof mysqli) {
                $link->close();
            }
        }
    }
}

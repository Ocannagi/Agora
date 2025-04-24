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

    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService) {
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
        $this->securityService->deleteTokensExpirados($link);
        $loginData = json_decode(file_get_contents("php://input"), true);
        $usrEmail = mysqli_real_escape_string($link, $loginData['usrEmail']);
        $usrPassword = mysqli_real_escape_string($link, $loginData['usrPassword']);
        $sql = "SELECT usrId, usrNombre, usrTipoUsuario FROM usuario WHERE usrEmail='$usrEmail' AND usrPassword='$usrPassword'";
        $resultado = mysqli_query($link, $sql);
        if ($resultado && mysqli_num_rows($resultado) == 1) {
            $data = get_object_vars(new ClaimDTO(mysqli_fetch_assoc($resultado)));
            $jwt = $this->securityService->tokenGenerator($data);
            $jwtSql = mysqli_real_escape_string($link, $jwt);
            mysqli_query($link, "DELETE FROM tokens WHERE tokToken = '$jwtSql'");
            if (mysqli_query($link, "INSERT INTO tokens (tokToken) VALUES ('$jwtSql')")) {
                mysqli_close($link);
                Output::outputJson(['jwt' => $jwt]);
            } else {
                Output::outputError(500, mysqli_error($link));
            }
        }
        print_r("No está registrado el mail o la contraseña");
        Output::outputError(401);
    }

    public function deleteLogout()
    {
        $this->securityService->requireLogin(null);
        $link = $this->dbConnection->conectarBD();
        $authHeader = getallheaders();
        list($jwt) = @sscanf($authHeader['Authorization'], 'Bearer %s');
        if (!$jwt)
            Output::outputError(401, "El token de seguridad está vacío");
        $jwtSql = mysqli_real_escape_string($link, $jwt);
        if (!mysqli_query($link, "DELETE FROM tokens WHERE token = '$jwtSql'")) {
            Output::outputError(403);
        }
        mysqli_close($link);
        Output::outputJson([]);
    }
}

<?php

require_once(__DIR__ . '/dbConnection.php');
require_once(__DIR__ . '/security.php');
require_once(__DIR__ . '/validaciones.php');
require_once(__DIR__ . '/output.php');


/************* RUTEO *************/

if (!isset($_GET['accion'])) {
    outputError();
}

$metodo = strtolower($_SERVER['REQUEST_METHOD']);
$accion = explode('/', strtolower($_GET['accion']));
$funcionNombre = $metodo . ucfirst($accion[0]);
$parametros = array_slice($accion, 1);
if (count($parametros) > 0 && $metodo == 'get') {
    $funcionNombre = $funcionNombre . 'ConParametros';
}
if (function_exists($funcionNombre)) {
    call_user_func_array($funcionNombre, $parametros);
} else {
    outputError(400, "No existe " . $funcionNombre);
}

/***************************** API ********************************/


function postLogin()
{
    $link = conectarBD();
    deleteTokensExpirados($link);
    $loginData = json_decode(file_get_contents("php://input"), true);
    $usrEmail = mysqli_real_escape_string($link, $loginData['usrEmail']);
    $usrPassword = mysqli_real_escape_string($link, $loginData['usrPassword']);
    $sql = "SELECT usrId, usrNombre FROM usuario WHERE usrEmail='$usrEmail' AND usrPassword='$usrPassword'";
    $resultado = mysqli_query($link, $sql);
    if ($resultado && mysqli_num_rows($resultado) == 1) {
        $logged = mysqli_fetch_assoc($resultado);
        $data = [
            'usrId'       => $logged['usrId'],
            'usrNombre'    => $logged['usrNombre'],
            'exp'       => time() + JWT_EXP,
        ];
        $jwt = tokenGenerator($data);
        $jwtSql = mysqli_real_escape_string($link, $jwt);
        mysqli_query($link, "DELETE FROM tokens WHERE tokToken = '$jwtSql'");
        if (mysqli_query($link, "INSERT INTO tokens (tokToken) VALUES ('$jwtSql')")) {
            mysqli_close($link);
            outputJson(['jwt' => $jwt]);
        } else {
            outputError(500, mysqli_error($link));
        }
    }
    print_r("No está registrado el mail o la contraseña");
    outputError(401);
}

function postLogout()
{
    requireLogin();
    $link = conectarBD();
    $authHeader = getallheaders();
    list($jwt) = @sscanf($authHeader['Authorization'], 'Bearer %s');
    if (!$jwt)
        outputError(401, "El token de seguridad está vacío");
    $jwtSql = mysqli_real_escape_string($link, $jwt);
    if (!mysqli_query($link, "DELETE FROM tokens WHERE token = '$jwtSql'")) {
        outputError(403);
    }
    mysqli_close($link);
    outputJson([]);
}


function getUsuarios()
{
    requireLogin();
    $link = conectarBD();
    $sql = "SELECT usrId, usrNombre, usrApellido, usrEmail FROM usuario";
    $resultado = mysqli_query($link, $sql);
    if ($resultado === false) {
        print_r(mysqli_error($link));
        outputError(500);
    }
    $ret = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $ret[] = [
            'usrId' => $fila['usrId'] + 0,
            'usrApellido' => $fila['usrApellido'],
            'usrNombre'   => $fila['usrNombre'],
            'usrEmail'    => $fila['usrEmail']
        ];
    }
    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson($ret);
}

function getUsuariosConParametros($id)
{
    requireLogin();
    $id += 0;
    $link = conectarBD();
    $sql = "SELECT * FROM usuario WHERE usrId=$id";
    $resultado = mysqli_query($link, $sql);
    if ($resultado === false) {
        outputError(500, "Falló la consulta al querer obtener un usuario por id: " . mysqli_error($link));
        die;
    }
    if (mysqli_num_rows($resultado) == 0) {
        outputError(404, "No se encontró un usuario con ese id");
    }

    $ret = mysqli_fetch_assoc($resultado);
    settype($ret["usrId"], "integer");
    settype($ret["usrDomicilio"], "integer");
    mysqli_free_result($resultado);
    mysqli_close($link);
    $ret["usrPassword"] = "*****";
    outputJson($ret);
}


function getTiposUsuario()
{
    requireLogin();
    $link = conectarBD();

    $sql = "SELECT * FROM tipousuario";
    $resultado = mysqli_query($link, $sql);
    if ($resultado === false) {
        print_r(mysqli_error($link));
        outputError(500);
    }
    $ret = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $ret[] = [
            'ttuTipoUsuario' => $fila['ttuTipoUsuario'],
            'ttuDescripcion' => $fila['ttuDescripcion'],
        ];
    }
    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson($ret);
}


function postUsuario()
{
    requireLogin();
    $link = conectarBD();
    $dato = json_decode(file_get_contents('php://input'), true);
    if (json_last_error()) {
        outputError(400, "El formato de datos es incorrecto");
    }

    //Datos obligatorios
    $usrDni = 'usrDni';
    $usrApellido = 'usrApellido';
    $usrNombre = 'usrNombre';
    $usrTipoUsuario  = 'usrTipoUsuario';
    $usrDomicilio = 'usrDomicilio';
    $usrFechaNacimiento = 'usrFechaNacimiento';
    $usrEmail  = 'usrEmail';
    $usrPassword  = 'usrPassword';

    //

    validarInputUsuario($link, $dato, false);






    $usrRazonSocialFantasia = isset($dato['usrRazonSocialFantasia']) ? "'" . mysqli_real_escape_string($link, $dato['usrRazonSocialFantasia']) . "'" : "'NULL'";
    $usrMatricula = isset($dato['usrMatricula']) ? "'" . mysqli_real_escape_string($link, $dato['usrMatricula']) . "'" : "'NULL'";
    $usrDescripcion = isset($dato['usrDescripcion']) ? "'" . mysqli_real_escape_string($link, $dato['usrDescripcion']) . "'" : "'NULL'";

    $sql = "INSERT INTO usuario (usrDni, usrApellido, usrNombre, usrRazonSocialFantasia , usrCuitCuil,
            usrTipoUsuario, usrMatricula, usrDomicilio, usrFechaNacimiento, usrDescripcion, usrEmail,
            usrPassword) VALUES ($usrDni, $usrApellido, $usrNombre, $usrRazonSocialFantasia, $usrCuitCuil,
            $usrTipoUsuario, $usrMatricula, $usrDomicilio, $usrFechaNacimiento, $usrDescripcion, $usrEmail,
            $usrPassword)";

    $resultado = mysqli_query($link, $sql);
    if ($resultado === false) {
        outputError(500, "Falló la consulta: " . mysqli_error($link));
    }

    $ret = [
        'usrId' => mysqli_insert_id($link)
    ];

    mysqli_close($link);
    outputJson($ret, 201);
}

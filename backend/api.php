<?php

require_once(__DIR__ . '/persistencia.php');
require_once(__DIR__ . '/JWT/vendor/autoload.php');
require_once(__DIR__ . '/../config/config.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


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

/**************** SALIDA ******************/

function outputJson($data, $codigo = 200)
{
    header('', true, $codigo);
    header('Content-type: application/json');
    print_r(json_encode($data));
    die;
}

function outputError($codigo = 500, $mensaje = "")
{
    switch ($codigo) {
        case 400:
            header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad request", true, 400);
            break;
        case 401:
            header($_SERVER["SERVER_PROTOCOL"] . " 401 Unauthorized", true, 401);
            break;
        case 403:
            header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden", true, 403);
            break;
        case 404:
            header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found", true, 404);
            break;
        default:
            header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error", true, 500);
            break;
    }
    print_r(json_encode($mensaje));
    die;
}

/***************************** BBDD ********************************/

function conectarBD()
{
    $link = mysqli_connect(DBHOST, DBUSER, DBPASS, DBBASE);
    if ($link === false) {
        outputError(500, "Falló la conexión: " . mysqli_connect_error());
    }
    mysqli_set_charset($link, 'utf8');
    return $link;
}

function postRestablecer()
{
    $db = conectarBD();
    $sql = sf__restablecerSql();
    $result = mysqli_multi_query($db, $sql);
    if ($result === false) {
        print_r(mysqli_error($db));
        outputError(500);
    }
    mysqli_close($db);
    outputJson([], 201);
}

/***************************** SEGURIDAD ********************************/

function requireLogin()
{
    $authHeader = getallheaders();
    try {
        list($jwt) = @sscanf($authHeader['Authorization'], 'Bearer %s');
        if (!$jwt)
            outputError(401, "El token de seguridad está vacío");
        $datos = JWT::decode($jwt, new Key(JWT_KEY, JWT_ALG)); // si esta expirado el token, lanza excepción;
        $link = conectarBD();
        $jwtSql = mysqli_real_escape_string($link, $jwt);
        $resultado = mysqli_query($link, "SELECT 1 FROM tokens WHERE tokToken = '$jwtSql'");
        if (!$resultado) {
            outputError(500, mysqli_error($link));
        } elseif (mysqli_num_rows($resultado) != 1) {
            outputError(403);
        }
        mysqli_close($link);
    } catch (Exception $e) {
        outputError(401, "El token puede estar vencido. Vuelva a loguearse por favor.");
    }
}

function deleteTokensExpirados($unLink = NULL)
{
    $unLink === NULL ? $link = conectarBD() : $link = $unLink;

    $segundos = JWT_EXP;
    $sql = "DELETE FROM tokens where TIMESTAMPDIFF(SECOND, tokFechaInsert,NOW()) >= $segundos";
    $resultado = mysqli_query($link, $sql);
    if ($resultado === false) {
        print_r(mysqli_error($link));
        outputError(500, "Falló la conexión al querer eliminar tokens expirados: " . mysqli_connect_error());
    }
    if ($unLink === NULL) {
        mysqli_close($link);
        outputJson([]);
    }
}

/********************* Funciones Privadas *************************/

/**
 * Devuelve true si las key pasadas en el primer parámetro tienen asignado un valor en el segundo parámetro, o bien,
 * devuelve un array de strings con las key sin valor.
 */
function _existenDatos(array $arrayKeys, array $arrayAsociativo)
{
    $faltantes = [];

    for ($i = 0; $i < count($arrayKeys); $i++) {
        if (!isset($arrayAsociativo[$arrayKeys[$i]]))
            $faltantes = $arrayKeys[$i];
    }

    if (count($faltantes) === 0)
        return true;
    else
        return $faltantes;
}

function _esEmailValido(string $email)
{
    return preg_match("/^[a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*@[a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,5}$/",$email) === 1;
}

function _esPasswordValido(string $password)
{
    return preg_match("/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,24}$/", $password) === 1;
}

/**
 * Verifica que tenga 11 caracteres, que sean todos dígitos, que el prefijo sea 20, 23, 24, 25, 26, 27, 30, 33 o 34 y que el dígito verificador sea correcto.
 */
function _esCuitCuilValido(string $cuilCuit)
{
    $bool = false;
    if (strlen($cuilCuit) === 11 && preg_match_all("/[^\d]/",$cuilCuit)===0) {
        if (in_array((int)substr($cuilCuit, 0, 2), [20, 23, 24, 25, 26, 27, 30, 33, 34])) {
            $sum = 0;
            for ($i = 0; $i < 10; $i++) {
                switch ($i) {
                    case 0:
                        $sum += (int)$cuilCuit[$i] * 5;
                        break;
                    case 1:
                        $sum += (int)$cuilCuit[$i] * 4;
                        break;
                    case 2:
                        $sum += (int)$cuilCuit[$i] * 3;
                        break;
                    case 3:
                        $sum += (int)$cuilCuit[$i] * 2;
                        break;
                    case 4:
                        $sum += (int)$cuilCuit[$i] * 7;
                        break;
                    case 5:
                        $sum += (int)$cuilCuit[$i] * 6;
                        break;
                    case 6:
                        $sum += (int)$cuilCuit[$i] * 5;
                        break;
                    case 7:
                        $sum += (int)$cuilCuit[$i] * 4;
                        break;
                    case 8:
                        $sum += (int)$cuilCuit[$i] * 3;
                        break;
                    case 9:
                        $sum += (int)$cuilCuit[$i] * 2;
                        break;
                }
            }

            $resto = $sum % 11;

            if ($resto === 0)
                $bool = $resto === (int)$cuilCuit[10];
            else
                $bool = (11 - $resto) === (int)$cuilCuit[10];
        }
    }

    return $bool;
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
        $jwt = JWT::encode($data, JWT_KEY, JWT_ALG);
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

function postUsuario()
{
    requireLogin();
    $link = conectarBD();
    $dato = json_decode(file_get_contents('php://input'), true);
    if (json_last_error()) {
        outputError(400, "El formato de datos es incorrecto");
    }


    $usrDni = 'usrDni';
    $usrApellido = 'usrApellido';
    $usrNombre = 'usrNombre';
    $usrTipoUsuario  = 'usrTipoUsuario';
    $usrDomicilio = 'usrDomicilio';
    $usrFechaNacimiento = 'usrFechaNacimiento';
    $usrEmail  = 'usrEmail';
    $usrPassword  = 'usrPassword';

    $msg = _existenDatos([$usrDni, $usrApellido, $usrNombre, $usrTipoUsuario, $usrDomicilio, $usrFechaNacimiento, $usrEmail, $usrPassword], $dato);
    if ($msg !== true)
        outputError(400, "Los siguientes datos deben estar completos: " . implode(", ", $msg) . ".");

    $usrDni = "'" . mysqli_real_escape_string($link, $dato[$usrDni]) . "'";
    $usrApellido = "'" . mysqli_real_escape_string($link, $dato[$usrApellido]) . "'";
    $usrNombre = "'" . mysqli_real_escape_string($link, $dato[$usrNombre]) . "'";
    $usrTipoUsuario = "'" . mysqli_real_escape_string($link, $dato[$usrTipoUsuario]) . "'";
    if(is_numeric($dato[$usrDomicilio]))
        $usrDomicilio = $dato[$usrDomicilio];
    else
        outputError(400, "El usrDomicilio no es válido.");

    if(_esEmailValido($dato[$usrEmail]))
        $usrEmail = "'" . mysqli_real_escape_string($link, $dato[$usrEmail]) . "'";
    else
        outputError(400,"El email no es válido.");
    
    if(_esPasswordValido($dato[$usrPassword]))
        $usrPassword = "'" . mysqli_real_escape_string($link, $dato[$usrPassword]) . "'";
    else
        outputError(400,"El usrPassword no es válido.");

    $usrFechaNacimiento = "'" . mysqli_real_escape_string($link, substr($dato[$usrFechaNacimiento], 0, 10)) . "'";
    list($anio, $mes, $dia) = explode('-', str_replace("'", "", $usrFechaNacimiento)); //Revisar
    if (!checkdate($mes, $dia, $anio)) {
        outputError(400, "La fecha de nacimiento no es válida");
    }

    $usrCuitCuil = "'NULL'";
    if(isset($dato['usrCuitCuil']))
    {
        $usrCuitCuil = $dato['usrCuitCuil'];
        if(!_esCuitCuilValido($usrCuitCuil))
            outputError(400, "El Cuit/Cuil no es válido.");
        else
            $usrCuitCuil = "'". $usrCuitCuil . ".";
    }

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

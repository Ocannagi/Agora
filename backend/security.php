<?php

require_once(__DIR__ . '/JWT/vendor/autoload.php');
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/output.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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

function tokenGenerator (array $data)
{
    return JWT::encode($data, JWT_KEY, JWT_ALG);
}

function deleteTokensExpirados(mysqli $unLink = NULL)
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

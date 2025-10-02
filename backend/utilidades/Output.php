<?php

/**************** SALIDA ******************/
namespace Utilidades;
class Output
{
    private static function cors()
    {
        if (headers_sent()) return;
        if (!isset($_SERVER['HTTP_ORIGIN'])) return;
        $allowed = ['http://localhost:4200'];//Cambiar en producción
        if (in_array($_SERVER['HTTP_ORIGIN'], $allowed, true)) {
            header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
            header("Access-Control-Allow-Credentials: true");
        }
    }
    
    public static function outputJson($data, $codigo = 200)
    {
        self::cors();
        header('', true, $codigo);
        header('Content-type: application/json');
        print_r(json_encode($data));
        die;
    }

    public static function outputError($codigo = 500, $mensaje = "")
    {
        self::cors();
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
            case 405:
                header($_SERVER["SERVER_PROTOCOL"] . " 405 Method Not Allowed", true, 405);
                break;
            case 409:
                header($_SERVER["SERVER_PROTOCOL"] . " 409 Conflict", true, 409);
                break;
            case 410:
                header($_SERVER["SERVER_PROTOCOL"] . " 410 Gone", true, 410);
            default:
                header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error", true, 500);
                break;
        }
        header('Content-Type: application/json');
        print_r(json_encode($mensaje));
        die;
    }
}

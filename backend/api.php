<?php

require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/persistencia.php');


// CORS
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [JWT_ISS]; //Cambiar en producción
if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header("Vary: Origin");
}
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Expose-Headers: Content-Type");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}


use Utilidades\Output;

spl_autoload_register(function ($className) {
    // Define la ruta base donde están tus clases e interfaces
    $baseDir = __DIR__ . '/controllers/';
    $file = $baseDir . str_replace('\\', '/', $className) . '.php';
    if (file_exists($file)) {
        require_once $file;
    } else {
        // Si no existe, intenta cargarlo desde la carpeta de servicios
        $baseDir = __DIR__ . '/servicios/';
        $file = $baseDir . str_replace('\\', '/', $className) . '.php';
        if (file_exists($file)) {
            require_once $file;
        } else {
            // Si no existe, intenta cargarlo desde la carpeta de DTOs
            $baseDir = __DIR__ . '/DTOs/';
            $file = $baseDir . str_replace('\\', '/', $className) . '.php';
            if (file_exists($file)) {
                require_once $file;
            } else {
                $baseDir = __DIR__ . '/DTOs/';
                $file = $baseDir . str_replace('DTOs', '', str_replace('\\', '/', $className)) . '.php';
                if (file_exists($file)) {
                    require_once $file;
                } else {
                    // Si no existe, intenta cargarlo desde la carpeta de utilidades
                    $baseDir = __DIR__ . '/utilidades/';
                    $file = $baseDir . str_replace('Utilidades', '', str_replace('\\', '/', $className)) . '.php';
                    if (file_exists($file)) {
                        require_once $file;
                    } else {
                        // Si no existe, intenta cargarlo desde la carpeta de modelos
                        $baseDir = __DIR__ . '/model/';
                        $file = $baseDir . str_replace('\\', '/', $className) . '.php';
                        if (file_exists($file)) {
                            require_once $file;
                        } else {
                            $baseDir = __DIR__ . '/model/';
                            $file = $baseDir . str_replace('Model', '', str_replace('\\', '/', $className)) . '.php';
                            if (file_exists($file)) {
                                require_once $file;
                            } else {
                                $baseDir = __DIR__;
                                $file = $baseDir . str_replace('\\', '/', $className) . '.php';
                                if (file_exists($file)) {
                                    require_once $file;
                                } else {
                                    // Si no existe, lanza el outputError
                                    Output::outputError(500, "No se encontró la clase $className");
                                }
                            }
                        }
                    }
                }
            }
        }
    }
});

//************* DEPENDENCIAS *************/


/** Definir aquí las Dependencias de los Controller */
define('DEPENDENCIAS', [

    'DbConnection' => 'DbConnection',
    'SecurityService' => 'SecurityService',
    'UsuariosValidacionService' => 'UsuariosValidacionService',
    'PeriodosValidacionService' => 'PeriodosValidacionService',
    'CategoriasValidacionService' => 'CategoriasValidacionService',
    'SubcategoriasValidacionService' => 'SubcategoriasValidacionService',
    'LocalidadesValidacionService' => 'LocalidadesValidacionService',
    'DomiciliosValidacionService' => 'DomiciliosValidacionService',
    'HabilidadesValidacionService' => 'HabilidadesValidacionService',
    'AntiguedadesValidacionService' => 'AntiguedadesValidacionService',
    'ImagenesantiguedadValidacionService' => 'ImagenesAntiguedadValidacionService',
    'TasacionesdigitalesValidacionService' => 'TasacionesDigitalesValidacionService',
    'TasacionesinsituValidacionService' => 'TasacionesInSituValidacionService',
    'UsuariosdomiciliosValidacionService' => 'UsuariosDomiciliosValidacionService',
    'AntiguedadesalaventaValidacionService' => 'AntiguedadesAlaVentaValidacionService',
    'ComprasventasValidacionService' => 'ComprasVentasValidacionService',
]); // es necesario que, en caso de clases con nombre compuesto, la key del array sea el nombre de la clase solo con mayúscula inicial

/** Configuración Zona Horaria */
date_default_timezone_set('America/Argentina/Buenos_Aires');

/************* RUTEO *************/

if (!isset($_GET['accion'])) {
    Output::outputError();
}

$metodo = strtolower($_SERVER['REQUEST_METHOD']);
$accion = explode('/', strtolower($_GET['accion']));

$controllerNombre = ucfirst($accion[0]) . 'Controller';

$funcionNombre = $metodo . ucfirst($accion[0]);
$parametros = array_slice($accion, 1);
if (count($parametros) > 0 && $metodo == 'get') {
    $funcionNombre = $funcionNombre . 'ById';
} else if (isset($_GET['params']) && $metodo == 'get') {
    $parametros = [$_GET['params']]; // debe ser un solo elemento
    $funcionNombre = $funcionNombre . 'ByParams';
} else if (isset($_GET['paginado'] ) && $metodo == 'get') {
    $parametros = [$_GET['paginado']]; // debe ser un solo elemento
    $funcionNombre = $funcionNombre . 'Paginado';
}

$controller = null;

if (class_exists($controllerNombre)) {
    $controller = instanciarControllerSingleton($controllerNombre);
} else {
    Output::outputError(400, "No existe el controlador " . $controllerNombre);
}

if (method_exists($controller, $funcionNombre)) {
    call_user_func_array([$controller, $funcionNombre], $parametros);
} else {
    Output::outputError(400, "No existe " . $funcionNombre . " en el controlador " . $controllerNombre);
}

/***************************** API ********************************/


function instanciarControllerSingleton($controllerNombre): object
{
    return $controllerNombre::getInstancia(...inyectarDependencias($controllerNombre));
}

/**
 * Inyecta las dependencias necesarias en el constructor del controlador.
 * @param string $controllerNombre Nombre del controlador.
 * @return array Array de dependencias a inyectar.
 * El constructor del controlador debe tener las dependencias en el mismo orden que se declaran aquí.
 */
function inyectarDependencias($controllerNombre): array
{
    $reflection = new ReflectionClass($controllerNombre);
    $constructor = $reflection->getConstructor();
    $dependencias = $constructor->getParameters();
    $baseName = explode('Controller', $controllerNombre)[0];


    $ret = [];
    foreach ($dependencias as $dependencia) {
        if (strtolower($dependencia->getName()) === strtolower('DbConnection')) {
            $ret[] = DEPENDENCIAS['DbConnection']::getInstancia();
        } else if (strtolower($dependencia->getName()) === strtolower('SecurityService')) {
            $ret[] = DEPENDENCIAS['SecurityService']::getInstancia(DEPENDENCIAS['DbConnection']::getInstancia());
        } else if (strtolower($dependencia->getName()) === strtolower($baseName . 'ValidacionService')) {
            $ret[] = DEPENDENCIAS[$baseName . 'ValidacionService']::getInstancia();
        }
    }
    return $ret;
}

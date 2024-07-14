<?php
require_once(__DIR__ . '/output.php');

/**Si la validación falla, hay ouput con msg de error. Si la cumple, el array de datos pasado por referencia es escapado y se le agregan las comillas simples */
function validarInputUsuario(mysqli $linkExterno, array &$dato, bool $soyModificacion)
{
    //Datos obligatorios
    $usrDni = 'usrDni';
    $usrApellido = 'usrApellido';
    $usrNombre = 'usrNombre';
    $usrTipoUsuario  = 'usrTipoUsuario';
    $usrDomicilio = 'usrDomicilio';
    $usrFechaNacimiento = 'usrFechaNacimiento';
    $usrEmail  = 'usrEmail';
    $usrPassword  = 'usrPassword';


    validarDatosObligatorios([$usrDni, $usrApellido, $usrNombre, $usrTipoUsuario, $usrDomicilio, $usrFechaNacimiento, $usrEmail, $usrPassword], $dato);
    validarDni($dato, $usrDni, $linkExterno);
    validarApellido($dato, $usrApellido, $linkExterno);
    validarNombre($dato, $usrNombre, $linkExterno);
    validarTipoUsuario($dato, $usrTipoUsuario, $linkExterno);
    validarDomicilio($dato, $usrDomicilio, $linkExterno);
    validarEmail($dato, $usrEmail, $linkExterno);

    if ($soyModificacion)
        validarExisteUsuarioModificar($dato["usrId"], $linkExterno);
    else
        validarSiYaFueRegistrado($dato[$usrEmail], $dato[$usrDni], $linkExterno);

    validarPassword($dato, $usrPassword, $linkExterno);
    validarFechaNacimiento($dato, $usrFechaNacimiento, $linkExterno);

    //Datos no obligatorios (salvo x condiciones)

    validarCuitCuil($dato, "usrCuitCuil", $usrTipoUsuario, $linkExterno);
    validarRazonSocial($dato, "usrRazonSocialFantasia", "usrCuitCuil", $linkExterno);
    validarMatricula($dato, "usrMatricula", $usrTipoUsuario, $linkExterno);
    validarDescripcion($dato, "usrDescripcion", $linkExterno);
}


function validarDatosObligatorios(array $keyDatos, array $datos)
{
    $msg = _existenDatos($keyDatos, $datos);
    if ($msg !== true)
        outputError(400, "Los siguientes datos deben estar completos: " . implode(", ", $msg) . ".");
}

function validarDni(array &$dato, string $keyDni, mysqli $linkExterno)
{
    if (_esStringLongitud($dato[$keyDni], 8, 8) && _esDigito($dato[$keyDni]))
        $dato[$keyDni] = "'" . mysqli_real_escape_string($linkExterno, $dato[$keyDni]) . "'";
    else
        outputError(400, "El dni debe tener 8 dígitos y ser de tipo string");
}

function validarApellido(array &$dato, string $keyApellido, mysqli $linkExterno)
{
    if (!_esStringLongitud($dato[$keyApellido], 1, 50))
        outputError(400, "El-Los apellidos deben ser string y tener al menos un carácter y máximo 50.");
    else if (!_esApellidoNombreValido($dato[$keyApellido]))
        outputError(400, "El-Los apellidos deben iniciar con mayúscula inicial y tener caracteres válidos.");

    $dato[$keyApellido] = "'" . mysqli_real_escape_string($linkExterno, $dato[$keyApellido]) . "'";
}

function validarNombre(array &$dato, string $keyNombre, mysqli $linkExterno)
{
    if (!_esStringLongitud($dato[$keyNombre], 1, 50))
        outputError(400, "El-Los nombres deben ser string y tener al menos un carácter y máximo 50.");
    else if (!_esApellidoNombreValido($dato[$keyNombre]))
        outputError(400, "El-Los nombres deben iniciar con mayúscula inicial y tener caracteres válidos.");

    $dato[$keyNombre] = "'" . mysqli_real_escape_string($linkExterno, $dato[$keyNombre]) . "'";

}

function validarTipoUsuario(array &$dato, string $keyTipoUsuario, mysqli $linkExterno)
{
    if (_esStringLongitud($dato[$keyTipoUsuario], 2, 2)) {
        $dato[$keyTipoUsuario] = "'" . mysqli_real_escape_string($linkExterno, $dato[$keyTipoUsuario]) . "'";
        if (!_existeTipoUsuario($linkExterno, $dato[$keyTipoUsuario]))
            outputError(409, "No existe el usrTipoUsuario enviado.");
    } else
        outputError(400, "El usrTipoUsuario debe tener 2 caracteres.");
}

function validarDomicilio(array &$dato, string $keyDomicilio, mysqli $linkExterno)
{
    if (is_int($dato[$keyDomicilio])) {
        if (!_existeDomicilio($linkExterno, $dato[$keyDomicilio]))
            outputError(409, "No está registrado el domicilo enviado");
    } else
        outputError(400, "El usrDomicilio debe ser un integer, no debe enviarse como string.");
}

function validarEmail(array &$dato, string $keyEmail, mysqli $linkExterno)
{
    if (_esStringLongitud($dato[$keyEmail], 6, 100) && _esEmailValido($dato[$keyEmail]))
        $dato[$keyEmail] = "'" . mysqli_real_escape_string($linkExterno, $dato[$keyEmail]) . "'";
    else
        outputError(400, "El email no es válido.");
}

function validarExisteUsuarioModificar(int $id, mysqli $linkExterno)
{
    if (!_existeUsuarioModificar($linkExterno, $id))
        outputError(409, "El usuario a modificar no existe.");
}

function validarSiYaFueRegistrado(string $email, string $dni, mysqli $linkExterno)
{
    if (_existeUsuarioCrear($linkExterno, $email, $dni))
        outputError(409, "Ya se encuentra registrado el email o el dni del usuario a crear.");
}

function validarPassword(array &$dato, string $keyPassword, mysqli $linkExterno)
{
    if (_esStringLongitud($dato[$keyPassword], 8, 25) && _esPasswordValido($dato[$keyPassword]))
        $dato[$keyPassword] = "'" . mysqli_real_escape_string($linkExterno, $dato[$keyPassword]) . "'";
    else
        outputError(400, ["El usrPassword no es válido: " => [
            "Debe tener al menos 8 caracteres y máximo 25.", "Debe tener al menos una mayúscula.", "Debe tener al menos una minúscula.",
            "Debe tener al menos un número.", "Debe tener al menos un carácter especial #?!@$%^&*- ."
        ]]);
}

function validarFechaNacimiento(array &$dato, string $keyFechaNacimiento, mysqli $linkExterno)
{
    $dato[$keyFechaNacimiento] = "'" . mysqli_real_escape_string($linkExterno, substr($dato[$keyFechaNacimiento], 0, 10)) . "'";
    if (!_esFormatoFecha(str_replace("'", "", $dato[$keyFechaNacimiento])))
        outputError(400, "El formato de la fecha debe ser AAAA-MM-DD");
    list($anio, $mes, $dia) = explode('-', str_replace("'", "", $dato[$keyFechaNacimiento])); //Revisar
    if (!checkdate($mes, $dia, $anio)) {
        outputError(400, "La fecha no es válida");
    }
    
    $fn = date_create(str_replace("'", "", $dato[$keyFechaNacimiento]),timezone_open('America/Argentina/Buenos_Aires'));
    $hoy = date_create('',timezone_open('America/Argentina/Buenos_Aires'));
    $hoyMenos130anios = date_create('',timezone_open('America/Argentina/Buenos_Aires'))->sub(new DateInterval('P130Y'));
    $hoyMenos18anios = date_create('',timezone_open('America/Argentina/Buenos_Aires'))->sub(new DateInterval('P18Y'));

    if($fn<$hoyMenos130anios)
        outputError(400,"La fecha de nacimiento declarada tiene más 130 años al día de la fecha. Por favor, comuníquese con soporte técnico en caso de que la fecha sea correcta.");
    
    if($fn>$hoy)
        outputError(400,"La fecha de nacimiento no puede ser mayor a hoy");

    if ($fn>$hoyMenos18anios)
        outputError(400,"Debes tener 18 años o más para poder registrarte en esta web.");

}

function validarCuitCuil(array &$dato, string $keyCuitCuil, string $keyTipoUsuario, mysqli $linkExterno)
{
    if (!isset($dato[$keyCuitCuil]) && _requiereMatricula($linkExterno, $dato[$keyTipoUsuario]))
        outputError(400, "Es obligatorio el CUIT/CUIL para el tipo de usuario declarado.");


    if (!isset($dato[$keyCuitCuil]))
        $dato[$keyCuitCuil] = "'NULL'";
    else if (!_esCuitCuilValido($dato[$keyCuitCuil]))
        outputError(400, "El Cuit-Cuil no es válido.");
    else
        $dato[$keyCuitCuil] = "'" . $dato[$keyCuitCuil] . "'";
}

function validarRazonSocial(array &$dato, string $keyRazonSocial, string $keyCuitCuil, mysqli $linkExterno)
{
    if (isset($dato[$keyCuitCuil]) && in_array((int)substr(str_replace("'", "", $dato[$keyCuitCuil]), 0, 2), [30, 33, 34])) // si es un CUIT
    {
        if (!isset($dato[$keyRazonSocial])) {
            outputError(400, "Es obligatoria la Razón Social si se declara un CUIT.");
        } else if (!_esStringLongitud($dato[$keyRazonSocial], 1, 100)) {
            outputError(400, "La Razón Social debe ser un string de al menos un caracter y un máximo de 100.");
        } else
            $dato[$keyRazonSocial] = "'" . mysqli_real_escape_string($linkExterno, $dato[$keyRazonSocial]) . "'";
    } else
        $dato[$keyRazonSocial] = "'NULL'";
}

function validarMatricula(array &$dato, string $keyMatricula, string $keyTipoUsuario, mysqli $linkExterno)
{

    if (!_requiereMatricula($linkExterno, $dato[$keyTipoUsuario]))
        $dato[$keyMatricula] = "'NULL'";
    else {
        if (!isset($dato[$keyMatricula]))
            outputError(400, "La matrícula es obligatoria para el tipoUsuario declarado.");
        else if (_esStringLongitud($dato[$keyMatricula], 1, 20))
            $dato[$keyMatricula] = "'" . mysqli_real_escape_string($linkExterno, $dato[$keyMatricula]) . "'";
        else
            outputError(400, "La Matrícula debe ser un string de al menos un caracter y un máximo de 20.");
    }
}

function validarDescripcion(array &$dato, string $keyDescripcion, mysqli $linkExterno)
{
    if (!isset($dato[$keyDescripcion]))
        $dato[$keyDescripcion] = "'NULL'";
    else if (_esStringLongitud($dato[$keyDescripcion], 1, 500))
        $dato[$keyDescripcion] = "'" . mysqli_real_escape_string($linkExterno, $dato[$keyDescripcion]) . "'";
    else
        outputError(400, "La Descripción del usuario debe ser un string de al menos un caracter y un máximo de 500.");
}

/****************** Funciones Privadas sin Conectar a BD ******************/

/**
 * Devuelve true si las key pasadas en el primer parámetro tienen asignado un valor en el segundo parámetro, o bien,
 * devuelve un array de strings con las key sin valor.
 */
function _existenDatos(array $arrayKeys, array $arrayAsociativo)
{
    if (!is_array($arrayKeys) || !is_array($arrayAsociativo))
        outputError(400, "No se enviaron los datos necesarios para la operación.");

    $faltantes = [];

    for ($i = 0; $i < count($arrayKeys); $i++) {
        if (!isset($arrayAsociativo[$arrayKeys[$i]]))
            $faltantes[] = $arrayKeys[$i];
    }

    if (count($faltantes) === 0)
        return true;
    else
        return $faltantes;
}

function _esDigito(string $strNum)
{
    return preg_match("/^\d+$/", $strNum) === 1;
}

/**Evalúa si es string y si está dentro del min/max, ambos incluidos */
function _esStringLongitud($val, int $min, int $max)
{
    $bool = false;
    if (is_string($val)) {
        $len = strlen($val);
        $bool = ($len >= $min) && ($len <= $max);
    }

    return $bool;
}

function _esApellidoNombreValido(string $apellido)
{
    return preg_match("/^[A-ZÑÄËÏÖÜÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙ]{1}[a-zñäëïöüáéíóúâêîôûàèìòù'-]*(?:[a-zñäëïöüáéíóúâêîôûàèìòù']\s?[A-ZÑÄËÏÖÜÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙ]{1}[a-zñäëïöüáéíóúâêîôûàèìòù'-]*)*$/", $apellido) === 1;
}

function _esEmailValido(string $email)
{
    return preg_match("/^[a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*@[a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,5}$/", $email) === 1;
}

/**
 * Verifica que tenga entre 8 y 25 caracteres, que tenga al menos una mayúscula, una minúscula, al menos un número y al menos un carácter especial #?!@$%^&*-"
 */
function _esPasswordValido(string $password)
{
    return preg_match("/^(?=.*?[A-ZÑÄËÏÖÜÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙ])(?=.*?[a-zñäëïöüáéíóúâêîôûàèìòù])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/", $password) === 1;
}

/**
 * Verifica que tenga 11 caracteres, que sean todos dígitos, que el prefijo sea 20, 23, 24, 25, 26, 27, 30, 33 o 34 y que el dígito verificador sea correcto.
 */
function _esCuitCuilValido(string $cuilCuit)
{
    $bool = false;
    if (is_string($cuilCuit) && strlen($cuilCuit) === 11 && preg_match_all("/[^\d]/", $cuilCuit) === 0) {
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

function _esFormatoFecha(string $fecha)
{
    return preg_match("/^\d{4}-{1}\d{2}-{1}\d{2}$/", $fecha) === 1;
}

/***************************** Funciones privadas que conectan a BD con link externo ********************************/

/**
 * Devuelve true si el mail o el dni ya se encuentran registrados. Devuelve false si no se encuentran.
 */
function _existeUsuarioCrear(mysqli $link, string $email, string $dni)
{
    $sql = "SELECT 1 FROM usuario WHERE (usrEmail = $email OR usrDni = $dni) AND usrFechaBaja IS NULL";
    return _existeEnBD($link, $sql, "obtener un usuario por email o dni");
}

/**
 * Devuelve true si el usrId ya se encuentra registrado. Devuelve false, si no.
 */
function _existeUsuarioModificar(mysqli $link, int $usrId)
{
    $sql = "SELECT 1 FROM usuario WHERE usrId = $usrId";
    return _existeEnBD($link, $sql, "obtener un usuario por id");
}


/**
 * Devuelve true si el TipoUsuario se encuentra registrado en BD. Devuelve false, si no.
 */
function _existeTipoUsuario(mysqli $link, string $tipoUsuario)
{
    $sql = "SELECT 1 FROM tipousuario WHERE ttuTipoUsuario = $tipoUsuario";
    return _existeEnBD($link, $sql, "obtener un tipoUsuario por id");
}

function _existeDomicilio($link, int $domicilio)
{
    $sql = "SELECT 1 FROM domicilio WHERE domId = $domicilio";
    return _existeEnBD($link, $sql, "obtener un domicilio por id");
}


/**
 * Devuelve true si el TipoUsuario pasado por parámetro tiene obligación de tener matrícula. Devuelve false, si no.
 */
function _requiereMatricula(mysqli $link, string $tipoUsuario)
{
    $sql = "SELECT 1 FROM tipousuario WHERE ttuTipoUsuario = $tipoUsuario AND ttuRequiereMatricula = 1";
    return _existeEnBD($link, $sql, "obtener requisito de matrícula en tipousuario");
}



function _existeEnBD(mysqli $link, string $sql, string $msg)
{
    $bool = true;
    $resultado = mysqli_query($link, $sql);
    if ($resultado === false) {
        outputError(500, "Falló la consulta al querer" . $msg . ": " . mysqli_error($link));
        die;
    }
    if (mysqli_num_rows($resultado) == 0) {
        $bool = false;
    }
    mysqli_free_result($resultado);
    return $bool;
}

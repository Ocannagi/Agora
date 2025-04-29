<?php

use Utilidades\Output;
use Utilidades\Input;

class ValidacionService implements IValidar
{

    private static $instancia = null;

    private function __construct() {}

    public static function getInstancia()
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    private function __clone() {}




    /**Si la validación falla, hay ouput con msg de error. Si la cumple, el array de datos pasado por referencia es escapado y se le agregan las comillas simples */
    function validarInputUsuario(mysqli $linkExterno, UsuarioCreacionDTO | UsuarioDTO $usuario)
    {

        $this->validarDatosObligatorios(Usuario::getObligatorios(), get_object_vars($usuario));
        $this->validarDni($usuario->usrDni);
        $this->validarApellido($usuario->usrApellido);
        $this->validarNombre($usuario->usrNombre);
        $this->validarTipoUsuario($usuario->usrTipoUsuario, $linkExterno);
        $this->validarDomicilio($usuario->usrDomicilio, $linkExterno);
        $this->validarEmail($usuario->usrEmail);

        if ($usuario instanceof UsuarioDTO) {
            $this->validarExisteUsuarioModificar($usuario->usrId, $linkExterno);
        } else
            $this->validarSiYaFueRegistrado($usuario->usrEmail, $usuario->usrDni, $linkExterno);

        $this->validarPassword($usuario->usrPassword);
        $this->validarFechaNacimiento($usuario->usrFechaNacimiento);

        //Datos no obligatorios (salvo x condiciones)

        $this->validarCuitCuil($usuario->usrCuitCuil, $usuario->usrTipoUsuario, $linkExterno);
        $this->validarRazonSocial($usuario->usrRazonSocialFantasia, $usuario->usrCuitCuil);
        $this->validarMatricula($usuario->usrMatricula, $usuario->usrTipoUsuario, $linkExterno);
        $this->validarDescripcion($usuario->usrDescripcion);
    }

    /**
     * Valida que los tipos de datos de las propiedades de la clase coincidan con los tipos de los datos del array.
     * Si no coinciden, lanza un error.
     * @param string $className Nombre de la clase a validar.
     * @param array $datos Array de datos a validar.
     */
    public function validarType(string $className, array $datos)
    {
        $refClass = new ReflectionClass($className);
        $propiedades = $refClass->getProperties();
        foreach ($propiedades as $propiedad) {
            $tipo = $propiedad->getType()->getName();
            if (array_key_exists($propiedad->getName(), $datos)) {
                if (gettype($datos[$propiedad->getName()]) !== $tipo && gettype($datos[$propiedad->getName()]) !== 'NULL') {
                    Output::outputError(400, "El campo " . $propiedad->getName() . " debe ser de tipo $tipo.");
                }
            }
        }
    }

    

    private function validarDatosObligatorios(array $keyDatos, array $datos)
    {
        $msg = $this->_existenDatos($keyDatos, $datos);
        if ($msg !== true)
            Output::outputError(400, 'Los siguientes datos deben estar completos: ' . implode(", ", $msg) . ".");
    }

    private function
    validarDni(string $dni)
    {
        if (!$this->_esStringLongitud($dni, 8, 8) || !$this->_esDigito($dni))
            Output::outputError(400, 'El dni debe tener 8 dígitos y ser de tipo string');
    }

    private function validarApellido(string $apellido)
    {
        if (!$this->_esStringLongitud($apellido, 1, 50))
            Output::outputError(400, 'El-Los apellidos deben ser string y tener al menos un carácter y máximo 50.');
        else if (!$this->_esApellidoNombreValido($apellido))
            Output::outputError(400, 'El-Los apellidos deben iniciar con mayúscula inicial y tener caracteres válidos.');
    }

    private function validarNombre(string $nombre)
    {
        if (!$this->_esStringLongitud($nombre, 1, 50))
            Output::outputError(400, 'El-Los nombres deben ser string y tener al menos un carácter y máximo 50.');
        else if (!$this->_esApellidoNombreValido($nombre))
            Output::outputError(400, 'El-Los nombres deben iniciar con mayúscula inicial y tener caracteres válidos.');
    }

    private function validarTipoUsuario(string $usrTipoUsuario, mysqli $linkExterno)
    {
        if ($this->_esStringLongitud($usrTipoUsuario, 2, 2)) {
            if (!$this->_existeTipoUsuario($linkExterno, $usrTipoUsuario))
                Output::outputError(409, 'No existe el usrTipoUsuario enviado.');
        } else
            Output::outputError(400, 'El usrTipoUsuario debe tener 2 caracteres.');
    }

    private function validarDomicilio(mixed $domicilio, mysqli $linkExterno)
    {
        if (is_int($domicilio)) {
            if (!$this->_existeDomicilio($linkExterno, $domicilio))
                Output::outputError(409, 'No está registrado el domicilo enviado');
        } else
            Output::outputError(400, 'El usrDomicilio debe ser un integer, no debe enviarse como string.');
    }

    private function validarEmail(string $email)
    {
        if (!$this->_esStringLongitud($email, 6, 100) || !$this->_esEmailValido($email))
            Output::outputError(400, 'El email no es válido.');
    }

    private function validarExisteUsuarioModificar(int $id, mysqli $linkExterno)
    {
        if (!$this->_existeUsuarioModificar($linkExterno, $id))
            Output::outputError(409, 'El usuario a modificar no existe.');
    }

    private function validarSiYaFueRegistrado(string $email, string $dni, mysqli $linkExterno)
    {
        if ($this->_existeUsuarioCrear($linkExterno, $email, $dni))
            Output::outputError(409, 'Ya se encuentra registrado el email o el dni del usuario a crear.');
    }

    private function validarPassword(string $password)
    {
        if (!$this->_esStringLongitud($password, 8, 25) || !$this->_esPasswordValido($password))
            Output::outputError(400, ['El usrPassword no es válido: ' => [
                'Debe tener al menos 8 caracteres y máximo 25.',
                'Debe tener al menos una mayúscula.',
                'Debe tener al menos una minúscula.',
                'Debe tener al menos un número.',
                'Debe tener al menos un carácter especial #?!@$%^&*- .'
            ]]);
    }

    private function validarFechaNacimiento(string $stringFecha)
    {
        if (!$this->_esFormatoFecha($stringFecha))
            Output::outputError(400, 'El formato de la fecha debe ser AAAA-MM-DD');
        list($anio, $mes, $dia) = explode('-', $stringFecha); //Revisar
        if (!checkdate($mes, $dia, $anio)) {
            Output::outputError(400, 'La fecha no es válida');
        }

        $dateTimeZone = new DateTimeZone('America/Argentina/Buenos_Aires');

        $fn = new DateTime($stringFecha, $dateTimeZone); //date_create(str_replace("'", "", $dato[$keyFechaNacimiento]), timezone_open('America/Argentina/Buenos_Aires'));
        $hoy = new DateTime('now', $dateTimeZone); //date_create('', timezone_open('America/Argentina/Buenos_Aires'));

        $hoyMenos130anios = $hoy->sub(new DateInterval('P130Y')); //date_create('', timezone_open('America/Argentina/Buenos_Aires'))->sub(new DateInterval('P130Y'));
        $hoyMenos18anios = $hoy->sub(new DateInterval('P18Y')); //date_create('', timezone_open('America/Argentina/Buenos_Aires'))->sub(new DateInterval('P18Y'));

        if ($fn < $hoyMenos130anios)
            Output::outputError(400, 'La fecha de nacimiento declarada tiene más 130 años al día de la fecha. Por favor, comuníquese con soporte técnico en caso de que la fecha sea correcta.');

        if ($fn > $hoy)
            Output::outputError(400, 'La fecha de nacimiento no puede ser mayor a hoy');

        if ($fn > $hoyMenos18anios)
            Output::outputError(400, 'Debe tener 18 años o más para poder registrarte en esta web.');
    }

    private function validarCuitCuil(string $cuitCuil, string $tipoUsuario, mysqli $linkExterno)
    {
        if (!Input::esNotNullVacioBlanco($cuitCuil) && $this->_requiereMatricula($linkExterno, $tipoUsuario))
            Output::outputError(400, 'Es obligatorio el CUIT/CUIL para el tipo de usuario declarado.');
        else if (!$this->_esCuitCuilValido($cuitCuil))
            Output::outputError(400, 'El Cuit-Cuil no es válido.');
    }

    private function validarRazonSocial(string $razonSocial, string $cuitCuil)
    {
        if (!Input::esNotNullVacioBlanco($cuitCuil) && in_array((int)substr($cuitCuil, 0, 2), [30, 33, 34])) // si es un CUIT
        {
            if (!Input::esNotNullVacioBlanco($razonSocial)) {
                Output::outputError(400, 'Es obligatoria la Razón Social si se declara un CUIT.');
            } else if (!$this->_esStringLongitud($razonSocial, 1, 100)) {
                Output::outputError(400, 'La Razón Social debe ser un string de al menos un caracter y un máximo de 100.');
            }
        } else if (Input::esNotNullVacioBlanco($razonSocial)) {
            Output::outputError(400, 'La Razón Social no puede ser declarada si no se declara un CUIT.');
        }
    }

    private function validarMatricula(string $matricula, string $tipoUsuario, mysqli $linkExterno)
    {

        if (!$this->_requiereMatricula($linkExterno, $tipoUsuario))
            Output::outputError(400, 'El tipo de usuario declarado no requiere matrícula.');
        else {
            if (!Input::esNotNullVacioBlanco($matricula))
                Output::outputError(400, 'La matrícula es obligatoria para el tipoUsuario declarado.');
            else if (!$this->_esStringLongitud($matricula, 1, 20))
                Output::outputError(400, 'La Matrícula debe ser un string de al menos un caracter y un máximo de 20.');
        }
    }

    private function validarDescripcion(string $descripcion)
    {
        if (!$this->_esStringLongitud($descripcion, 1, 500))
            Output::outputError(400, 'La Descripción del usuario debe ser un string de al menos un caracter y un máximo de 500.');
    }


    /****************** Funciones Privadas sin Conectar a BD ******************/

    /**
     * Devuelve true si las key pasadas en el primer parámetro tienen asignado un valor en el segundo parámetro, o bien,
     * devuelve un array de strings con las key sin valor.
     */
    private function _existenDatos(array $arrayKeys, array $arrayAsociativo): bool|array
    {
        if (!is_array($arrayKeys) || !is_array($arrayAsociativo))
            Output::outputError(400, 'No se enviaron los datos necesarios para la operación.');

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

    private function _esDigito(string $strNum): bool
    {
        return preg_match("/^\d+$/", $strNum) === 1;
    }

    /**Evalúa si es string y si está dentro del min/max, ambos incluidos */
    private function _esStringLongitud($val, int $min, int $max): bool
    {
        $bool = false;
        if (is_string($val)) {
            $len = strlen($val);
            if ($len === strlen(trim($val))) // Si no tiene espacios en blanco al principio o al final
                $bool = ($len >= $min) && ($len <= $max);
        }

        return $bool;
    }

    private function _esApellidoNombreValido(string $apellido): bool
    {
        return preg_match("/^[A-ZÑÄËÏÖÜÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙ]{1}[a-zñäëïöüáéíóúâêîôûàèìòù'-]*(?:[a-zñäëïöüáéíóúâêîôûàèìòù']\s?[A-ZÑÄËÏÖÜÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙ]{1}[a-zñäëïöüáéíóúâêîôûàèìòù'-]*)*$/", $apellido) === 1;
    }

    private function _esEmailValido(string $email): bool
    {
        return preg_match("/^[a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*@[a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,5}$/", $email) === 1;
    }

    /**
     * Verifica que tenga entre 8 y 25 caracteres, que tenga al menos una mayúscula, una minúscula, al menos un número y al menos un carácter especial #?!@$%^&*-"
     */
    private function _esPasswordValido(string $password): bool
    {
        return preg_match("/^(?=.*?[A-ZÑÄËÏÖÜÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙ])(?=.*?[a-zñäëïöüáéíóúâêîôûàèìòù])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/", $password) === 1;
    }

    /**
     * Verifica que tenga 11 caracteres, que sean todos dígitos, que el prefijo sea 20, 23, 24, 25, 26, 27, 30, 33 o 34 y que el dígito verificador sea correcto.
     */
    private function _esCuitCuilValido(string $cuilCuit): bool
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

    private function _esFormatoFecha(string $fecha): bool
    {
        return preg_match("/^\d{4}-{1}\d{2}-{1}\d{2}$/", $fecha) === 1;
    }



    /***************************** Funciones privadas que conectan a BD con link externo ********************************/

    //$link->real_escape_string($value)

    /**
     * Devuelve true si el mail o el dni ya se encuentran registrados. Devuelve false si no se encuentran.
     */
    private function _existeUsuarioCrear(mysqli $link, string $email, string $dni)
    {
        $email = $link->real_escape_string($email);
        $dni = $link->real_escape_string($dni);
        $sql = "SELECT 1 FROM usuario WHERE (usrEmail = '$email' OR usrDni = '$dni') AND usrFechaBaja IS NULL";
        return $this->_existeEnBD($link, $sql, 'obtener un usuario por email o dni');
    }

    /**
     * Devuelve true si el usrId ya se encuentra registrado. Devuelve false, si no.
     */
    private function _existeUsuarioModificar(mysqli $link, int $usrId)
    {
        $sql = "SELECT 1 FROM usuario WHERE usrId = $usrId";
        return $this->_existeEnBD($link, $sql, 'obtener un usuario por id');
    }


    /**
     * Devuelve true si el TipoUsuario se encuentra registrado en BD. Devuelve false, si no.
     */
    private function _existeTipoUsuario(mysqli $link, string $tipoUsuario)
    {
        $tipoUsuario = $link->real_escape_string($tipoUsuario);
        $sql = "SELECT 1 FROM tipousuario WHERE ttuTipoUsuario = '$tipoUsuario'";
        return $this->_existeEnBD($link, $sql, 'obtener un tipoUsuario por id');
    }

    private function _existeDomicilio($link, int $domicilio)
    {
        $sql = "SELECT 1 FROM domicilio WHERE domId = $domicilio";
        return $this->_existeEnBD($link, $sql, 'obtener un domicilio por id');
    }


    /**
     * Devuelve true si el TipoUsuario pasado por parámetro tiene obligación de tener matrícula. Devuelve false, si no.
     */
    private function _requiereMatricula(mysqli $link, string $tipoUsuario)
    {
        $tipoUsuario = $link->real_escape_string($tipoUsuario);
        $sql = "SELECT 1 FROM tipousuario WHERE ttuTipoUsuario = '$tipoUsuario' AND ttuRequiereMatricula = 1";
        return $this->_existeEnBD($link, $sql, 'obtener requisito de matrícula en tipousuario');
    }



    private function _existeEnBD(mysqli $link, string $query, string $msg)
    {
        $bool = true;
        $resultado = $link->query($query);
        if ($resultado === false) {
            Output::outputError(500, "Falló la consulta al querer $msg: $link->error");
            die;
        }
        if ($resultado->num_rows == 0) {
            $bool = false;
        }
        $resultado->free_result();
        return $bool;
    }
}

<?php

use Utilidades\Output;

class ValidacionService implements IValidar
{

    private static $instancia = null;

    private function __construct() {}
    
    public static function getInstancia() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    private function __clone() {}




    /**Si la validación falla, hay ouput con msg de error. Si la cumple, el array de datos pasado por referencia es escapado y se le agregan las comillas simples */
    function validarInputUsuario(mysqli $linkExterno, UsuarioCreacionDTO $usuarioCreacionDTO, bool $soyModificacion)
    {
        
        $this->validarDatosObligatorios(Usuario::getObligatorios(), get_object_vars($usuarioCreacionDTO));
        $this->validarDni($usuarioCreacionDTO->usrDni);
        $this->validarApellido($usuarioCreacionDTO->usrApellido);
        $this->validarNombre($usuarioCreacionDTO->usrNombre);
        $this->validarTipoUsuario($usuarioCreacionDTO->usrTipoUsuario, $linkExterno);
        $this->validarDomicilio($usuarioCreacionDTO->usrDomicilio, $linkExterno);
        $this->validarEmail($usuarioCreacionDTO->usrEmail);

        if ($soyModificacion) {
            $usrScoring = "usrScoring";
            $this->validarExisteUsuarioModificar($dato["usrId"]);
            $this->validarScoring($dato, $usrScoring);
        } else
            $this->validarSiYaFueRegistrado($dato[$usrEmail], $dato[$usrDni]);

        $this->validarPassword($dato, $usrPassword);
        $this->validarFechaNacimiento($dato, $usrFechaNacimiento);

        //Datos no obligatorios (salvo x condiciones)

        $this->validarCuitCuil($dato, "usrCuitCuil", $usrTipoUsuario);
        $this->validarRazonSocial($dato, "usrRazonSocialFantasia", "usrCuitCuil");
        $this->validarMatricula($dato, "usrMatricula", $usrTipoUsuario);
        $this->validarDescripcion($dato, "usrDescripcion");

        $this->escaparDatosYAgregarComillasSimples($dato, $linkExterno);
    }

    public function escaparDatosYAgregarComillasSimples(array &$datos, mysqli $linkExterno)
    {
        foreach ($datos as $key => $value) {
            if (is_string($value)) {
                $datos[$key] = "'" . $linkExterno->real_escape_string($value) . "'";
            }
        }
    }


    private function validarDatosObligatorios(array $keyDatos, array $datos)
    {
        $msg = $this->_existenDatos($keyDatos, $datos);
        if ($msg !== true)
            Output::outputError(400, "Los siguientes datos deben estar completos: " . implode(", ", $msg) . ".");
    }

    private function 
    validarDni(string $dni)
    {
        if (!$this->_esStringLongitud($dni, 8, 8) || !$this->_esDigito($dni))
            Output::outputError(400, "El dni debe tener 8 dígitos y ser de tipo string");
    }

    private function validarApellido(string $apellido)
    {
        if (!$this->_esStringLongitud($apellido, 1, 50))
            Output::outputError(400, "El-Los apellidos deben ser string y tener al menos un carácter y máximo 50.");
        else if (!$this->_esApellidoNombreValido($apellido))
            Output::outputError(400, "El-Los apellidos deben iniciar con mayúscula inicial y tener caracteres válidos.");
    }

    private function validarNombre(string $nombre)
    {
        if (!$this->_esStringLongitud($nombre, 1, 50))
            Output::outputError(400, "El-Los nombres deben ser string y tener al menos un carácter y máximo 50.");
        else if (!$this->_esApellidoNombreValido($nombre))
            Output::outputError(400, "El-Los nombres deben iniciar con mayúscula inicial y tener caracteres válidos.");
    }

    private function validarTipoUsuario(string $usrTipoUsuario, mysqli $linkExterno)
    {
        if ($this->_esStringLongitud($usrTipoUsuario, 2, 2)) {
            $usrTipoUsuario = "'" . $linkExterno->real_escape_string($usrTipoUsuario) . "'";
            if (!$this->_existeTipoUsuario($linkExterno, $usrTipoUsuario))
                Output::outputError(409, "No existe el usrTipoUsuario enviado.");
        } else
            Output::outputError(400, "El usrTipoUsuario debe tener 2 caracteres.");
    }

    private function validarDomicilio(mixed $domicilio, mysqli $linkExterno)
    {
        if (is_int($domicilio)) {
            if (!$this->_existeDomicilio($linkExterno, $domicilio))
                Output::outputError(409, "No está registrado el domicilo enviado");
        } else
            Output::outputError(400, "El usrDomicilio debe ser un integer, no debe enviarse como string.");
    }

    private function validarEmail(string $email)
    {
        if (!$this->_esStringLongitud($email, 6, 100) || !$this->_esEmailValido($email))
            Output::outputError(400, "El email no es válido.");
    }

    private function validarExisteUsuarioModificar(int $id, mysqli $linkExterno)
    {
        if (!$this->_existeUsuarioModificar($linkExterno, $id))
            Output::outputError(409, "El usuario a modificar no existe.");
    }

    private function validarScoring(array &$dato, string $keyScoring)
    {
        if (!is_int($dato[$keyScoring]))
            Output::outputError(400, "El usrScoring debe ser un integer (entero)");
    }

    private function validarSiYaFueRegistrado(string $email, string $dni, mysqli $linkExterno)
    {
        if ($this->_existeUsuarioCrear($linkExterno, $email, $dni))
            Output::outputError(409, "Ya se encuentra registrado el email o el dni del usuario a crear.");
    }

    private function validarPassword(array &$dato, string $keyPassword, mysqli $linkExterno)
    {
        if ($this->_esStringLongitud($dato[$keyPassword], 8, 25) && $this->_esPasswordValido($dato[$keyPassword]))
            $dato[$keyPassword] = "'" . mysqli_real_escape_string($linkExterno, $dato[$keyPassword]) . "'";
        else
            Output::outputError(400, ["El usrPassword no es válido: " => [
                "Debe tener al menos 8 caracteres y máximo 25.",
                "Debe tener al menos una mayúscula.",
                "Debe tener al menos una minúscula.",
                "Debe tener al menos un número.",
                "Debe tener al menos un carácter especial #?!@$%^&*- ."
            ]]);
    }

    private function validarFechaNacimiento(array &$dato, string $keyFechaNacimiento, mysqli $linkExterno)
    {
        $dato[$keyFechaNacimiento] = "'" . mysqli_real_escape_string($linkExterno, substr($dato[$keyFechaNacimiento], 0, 10)) . "'";
        if (!$this->_esFormatoFecha(str_replace("'", "", $dato[$keyFechaNacimiento])))
            Output::outputError(400, "El formato de la fecha debe ser AAAA-MM-DD");
        list($anio, $mes, $dia) = explode('-', str_replace("'", "", $dato[$keyFechaNacimiento])); //Revisar
        if (!checkdate($mes, $dia, $anio)) {
            Output::outputError(400, "La fecha no es válida");
        }

        $fn = date_create(str_replace("'", "", $dato[$keyFechaNacimiento]), timezone_open('America/Argentina/Buenos_Aires'));
        $hoy = date_create('', timezone_open('America/Argentina/Buenos_Aires'));
        $hoyMenos130anios = date_create('', timezone_open('America/Argentina/Buenos_Aires'))->sub(new DateInterval('P130Y'));
        $hoyMenos18anios = date_create('', timezone_open('America/Argentina/Buenos_Aires'))->sub(new DateInterval('P18Y'));

        if ($fn < $hoyMenos130anios)
            Output::outputError(400, "La fecha de nacimiento declarada tiene más 130 años al día de la fecha. Por favor, comuníquese con soporte técnico en caso de que la fecha sea correcta.");

        if ($fn > $hoy)
            Output::outputError(400, "La fecha de nacimiento no puede ser mayor a hoy");

        if ($fn > $hoyMenos18anios)
            Output::outputError(400, "Debe tener 18 años o más para poder registrarte en esta web.");
    }

    private function validarCuitCuil(array &$dato, string $keyCuitCuil, string $keyTipoUsuario, mysqli $linkExterno)
    {
        if (!isset($dato[$keyCuitCuil]) && $this->_requiereMatricula($linkExterno, $dato[$keyTipoUsuario]))
            Output::outputError(400, "Es obligatorio el CUIT/CUIL para el tipo de usuario declarado.");


        if (!isset($dato[$keyCuitCuil]))
            $dato[$keyCuitCuil] = "'NULL'";
        else if (!$this->_esCuitCuilValido($dato[$keyCuitCuil]))
            Output::outputError(400, "El Cuit-Cuil no es válido.");
        else
            $dato[$keyCuitCuil] = "'" . $dato[$keyCuitCuil] . "'";
    }

    private function validarRazonSocial(array &$dato, string $keyRazonSocial, string $keyCuitCuil, mysqli $linkExterno)
    {
        if (isset($dato[$keyCuitCuil]) && in_array((int)substr(str_replace("'", "", $dato[$keyCuitCuil]), 0, 2), [30, 33, 34])) // si es un CUIT
        {
            if (!isset($dato[$keyRazonSocial])) {
                Output::outputError(400, "Es obligatoria la Razón Social si se declara un CUIT.");
            } else if (!$this->_esStringLongitud($dato[$keyRazonSocial], 1, 100)) {
                Output::outputError(400, "La Razón Social debe ser un string de al menos un caracter y un máximo de 100.");
            } else
                $dato[$keyRazonSocial] = "'" . mysqli_real_escape_string($linkExterno, $dato[$keyRazonSocial]) . "'";
        } else
            $dato[$keyRazonSocial] = "'NULL'";
    }

    private function validarMatricula(array &$dato, string $keyMatricula, string $keyTipoUsuario, mysqli $linkExterno)
    {

        if (!$this->_requiereMatricula($linkExterno, $dato[$keyTipoUsuario]))
            $dato[$keyMatricula] = "'NULL'";
        else {
            if (!isset($dato[$keyMatricula]))
                Output::outputError(400, "La matrícula es obligatoria para el tipoUsuario declarado.");
            else if ($this->_esStringLongitud($dato[$keyMatricula], 1, 20))
                $dato[$keyMatricula] = "'" . mysqli_real_escape_string($linkExterno, $dato[$keyMatricula]) . "'";
            else
                Output::outputError(400, "La Matrícula debe ser un string de al menos un caracter y un máximo de 20.");
        }
    }

    private function validarDescripcion(array &$dato, string $keyDescripcion, mysqli $linkExterno)
    {
        if (!isset($dato[$keyDescripcion]))
            $dato[$keyDescripcion] = "'NULL'";
        else if ($this->_esStringLongitud($dato[$keyDescripcion], 1, 500))
            $dato[$keyDescripcion] = "'" . mysqli_real_escape_string($linkExterno, $dato[$keyDescripcion]) . "'";
        else
            Output::outputError(400, "La Descripción del usuario debe ser un string de al menos un caracter y un máximo de 500.");
    }

    /****************** Funciones Privadas sin Conectar a BD ******************/

    /**
     * Devuelve true si las key pasadas en el primer parámetro tienen asignado un valor en el segundo parámetro, o bien,
     * devuelve un array de strings con las key sin valor.
     */
    private function _existenDatos(array $arrayKeys, array $arrayAsociativo): bool|array
    {
        if (!is_array($arrayKeys) || !is_array($arrayAsociativo))
            Output::outputError(400, "No se enviaron los datos necesarios para la operación.");

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

    /**
     * Devuelve true si el mail o el dni ya se encuentran registrados. Devuelve false si no se encuentran.
     */
    private function _existeUsuarioCrear(mysqli $link, string $email, string $dni)
    {
        $sql = "SELECT 1 FROM usuario WHERE (usrEmail = $email OR usrDni = $dni) AND usrFechaBaja IS NULL";
        return $this->_existeEnBD($link, $sql, "obtener un usuario por email o dni");
    }

    /**
     * Devuelve true si el usrId ya se encuentra registrado. Devuelve false, si no.
     */
    private function _existeUsuarioModificar(mysqli $link, int $usrId)
    {
        $sql = "SELECT 1 FROM usuario WHERE usrId = $usrId";
        return $this->_existeEnBD($link, $sql, "obtener un usuario por id");
    }


    /**
     * Devuelve true si el TipoUsuario se encuentra registrado en BD. Devuelve false, si no.
     */
    private function _existeTipoUsuario(mysqli $link, string $tipoUsuario)
    {
        $sql = "SELECT 1 FROM tipousuario WHERE ttuTipoUsuario = $tipoUsuario";
        return $this->_existeEnBD($link, $sql, "obtener un tipoUsuario por id");
    }

    private function _existeDomicilio($link, int $domicilio)
    {
        $sql = "SELECT 1 FROM domicilio WHERE domId = $domicilio";
        return $this->_existeEnBD($link, $sql, "obtener un domicilio por id");
    }


    /**
     * Devuelve true si el TipoUsuario pasado por parámetro tiene obligación de tener matrícula. Devuelve false, si no.
     */
    private function _requiereMatricula(mysqli $link, string $tipoUsuario)
    {
        $sql = "SELECT 1 FROM tipousuario WHERE ttuTipoUsuario = $tipoUsuario AND ttuRequiereMatricula = 1";
        return $this->_existeEnBD($link, $sql, "obtener requisito de matrícula en tipousuario");
    }



    private function _existeEnBD(mysqli $link, string $query, string $msg)
    {
        $bool = true;
        $resultado = $link->query($query);
        if ($resultado === false) {
            Output::outputError(500, "Falló la consulta al querer" . $msg . ": " . $link->error);
            die;
        }
        if ($resultado->num_rows == 0) {
            $bool = false;
        }
        $resultado->free_result();
        return $bool;
    }
}

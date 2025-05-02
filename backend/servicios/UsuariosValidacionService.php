<?php

use Utilidades\Output;
use Utilidades\Input;

class UsuariosValidacionService extends ValidacionServiceBase
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

    public function validarInput(mysqli $linkExterno, ICreacionDTO | IDTO $usuario)
    {
        if (!($usuario instanceof UsuarioCreacionDTO) && !($usuario instanceof UsuarioDTO)) {
            Output::outputError(500, 'Error interno: el DTO proporcionado no es del tipo correcto.');
        }

        $this->validarDatosObligatorios(classModelName: 'Usuario', datos: get_object_vars($usuario));
        $this->validarDni($usuario->usrDni);
        $this->validarApellido($usuario->usrApellido);
        $this->validarNombre($usuario->usrNombre);
        $this->validarTipoUsuario($usuario->usrTipoUsuario, $linkExterno);
        $this->validarDomicilio($usuario->usrDomicilio, $linkExterno);
        $this->validarEmail($usuario->usrEmail);

        if ($usuario instanceof UsuarioDTO) {
            $this->validarExisteUsuarioModificar($usuario->usrId, $linkExterno);
            $this->validarSiYaFueRegistrado($usuario->usrEmail, $usuario->usrDni, $linkExterno, $usuario->usrId);
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

    private function validarDni(string $dni)
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

    /**
     * Valida si el email o dni ya fueron registrados en la base de datos.
     * Si ya fueron registrados, lanza un error 409.
     * @param string $email
     * @param string $dni
     * @param mysqli $linkExterno
     * @param int|null $id
     */
    private function validarSiYaFueRegistrado(string $email, string $dni, mysqli $linkExterno, ?int $id = null) : void
    {
        if ($this->_existeUsuarioCrear($linkExterno, $email, $dni, $id))
            Output::outputError(409, $id ? 'El email o dni nuevos que quiere registrar ya fueron usados por otro usuario' : 'Ya se encuentra registrado el email o el dni del usuario a crear.');
    
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

        $hoyMenos130anios = (clone $hoy)->sub(new DateInterval('P130Y')); //date_create('', timezone_open('America/Argentina/Buenos_Aires'))->sub(new DateInterval('P130Y'));
        $hoyMenos18anios = (clone $hoy)->sub(new DateInterval('P18Y')); //date_create('', timezone_open('America/Argentina/Buenos_Aires'))->sub(new DateInterval('P18Y'));

        if ($fn < $hoyMenos130anios)
            Output::outputError(400, 'La fecha de nacimiento declarada tiene más 130 años al día de la fecha. Por favor, comuníquese con soporte técnico en caso de que la fecha sea correcta.');

        if ($fn > $hoy)
            Output::outputError(400, 'La fecha de nacimiento no puede ser mayor a hoy');


        if ($fn > $hoyMenos18anios)
            Output::outputError(400, 'Debe tener 18 años o más para poder registrarte en esta web.');
    }

    private function validarCuitCuil(?string $cuitCuil, string $tipoUsuario, mysqli $linkExterno)
    {
        if (!Input::esNotNullVacioBlanco($cuitCuil) && $this->_requiereMatricula($linkExterno, $tipoUsuario))
            Output::outputError(400, 'Es obligatorio el CUIT/CUIL para el tipo de usuario declarado.');
        else if (Input::esNotNullVacioBlanco($cuitCuil) && !$this->_esCuitCuilValido($cuitCuil))
            Output::outputError(400, 'El Cuit-Cuil no es válido.');
    }

    private function validarRazonSocial(?string $razonSocial, ?string $cuitCuil)
    {
        if (Input::esNotNullVacioBlanco($cuitCuil) && in_array((int)substr($cuitCuil, 0, 2), [30, 33, 34])) // si es un CUIT
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

    private function validarMatricula(?string $matricula, string $tipoUsuario, mysqli $linkExterno)
    {

        if ($this->_requiereMatricula($linkExterno, $tipoUsuario)) {
            if (!Input::esNotNullVacioBlanco($matricula)) {
                Output::outputError(400, 'La matrícula es obligatoria para el tipoUsuario declarado.');
            } else if (!$this->_esStringLongitud($matricula, 1, 20)) {
                Output::outputError(400, 'La Matrícula debe ser un string de al menos un caracter y un máximo de 20.');
            }
        } else {
            if (Input::esNotNullVacioBlanco($matricula)) {
                Output::outputError(400, 'El tipo de usuario declarado no requiere matrícula.');
            }
        }
    }

    private function validarDescripcion(string $descripcion)
    {
        if (!$this->_esStringLongitud($descripcion, 1, 500))
            Output::outputError(400, 'La Descripción del usuario debe ser un string de al menos un caracter y un máximo de 500.');
    }

        /***************************** Funciones privadas que conectan a BD con link externo ********************************/

    /**
     * Devuelve true si el usrId ya se encuentra registrado. Devuelve false, si no.
     */
    private function _existeUsuarioModificar(mysqli $link, int $usrId)
    {
        $sql = "SELECT 1 FROM usuario WHERE usrId = $usrId AND usrFechaBaja IS NULL";
        return $this->_existeEnBD($link, $sql, 'obtener un usuario por id para modificar');
    }



     /** 
     * * Devuelve true si el email o el dni ya se encuentran registrados. Devuelve false si no se encuentran.
        * * Si el id es null, se busca si ya existe el email o dni en la base de datos. Si no es null, se busca si ya existe el email o dni en la base de datos, excluyendo el id pasado por parámetro.
     */
    private function _existeUsuarioCrear(mysqli $link, string $email, string $dni, ?int $id = null)
    {
        $email = $link->real_escape_string($email);
        $dni = $link->real_escape_string($dni);
        $sql = $id ? "SELECT 1 FROM usuario WHERE (usrEmail = '$email' OR usrDni = '$dni') AND usrFechaBaja IS NULL AND usrId <> $id" :"SELECT 1 FROM usuario WHERE (usrEmail = '$email' OR usrDni = '$dni') AND usrFechaBaja IS NULL";
        return $this->_existeEnBD($link, $sql, 'obtener un usuario por email o dni');
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


}
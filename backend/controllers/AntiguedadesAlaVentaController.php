<?php

use Utilidades\Output;
use Utilidades\Input;
use Utilidades\Querys;
use Model\CustomException;

class AntiguedadesAlaVentaController extends BaseController
{
    private ValidacionServiceBase $antiguedadesAlaVentaValidacionService;
    private ISecurity $securityService;

    use TraitGetByIdInterno; // Trait para métodos internos de obtención por ID
    use TraitCambiarEstadoAntiguedad; // Trait para cambiar el TipoEstado de una Antiguedad

    private static $instancia = null; // La única instancia de la clase

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $antiguedadesAlaVentaValidacionService)
    {
        parent::__construct($dbConnection);
        $this->securityService = $securityService;
        $this->antiguedadesAlaVentaValidacionService = $antiguedadesAlaVentaValidacionService;
    }

    public static function getInstance(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $antiguedadesAlaVentaValidacionService): AntiguedadesAlaVentaController
    {
        if (self::$instancia === null) {
            self::$instancia = new AntiguedadesAlaVentaController($dbConnection, $securityService, $antiguedadesAlaVentaValidacionService);
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    public function postAntiguedadesAlaVenta()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $mysqli->begin_transaction(); // Iniciar transacción
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());
            $data = Input::getArrayBody(msgEntidad: "la antigüedad a la venta");

            $this->antiguedadesAlaVentaValidacionService->validarType(className: "AntiguedadCreacionDTO", datos: $data);
            $antiguedadAlaVentaCreacionDTO = new AntiguedadAlaVentaCreacionDTO($data);

            $antiguedadAlaVentaCreacionDTO->antiguedad = $this->getByIdInterno(
                query: 'ANTIGUEDAD',
                classDTO: AntiguedadDTO::class,
                linkExterno: $mysqli,
                id: $antiguedadAlaVentaCreacionDTO->antiguedad->antId
            );

            $antiguedadAlaVentaCreacionDTO->domicilio = $this->getByIdInterno(
                query: 'DOMICILIO',
                classDTO: DomicilioDTO::class,
                linkExterno: $mysqli,
                id: $antiguedadAlaVentaCreacionDTO->domicilio->domId
            );

            if (isset($antiguedadAlaVentaCreacionDTO->aavPrecioVenta)) {
                $antiguedadAlaVentaCreacionDTO->aavPrecioVenta = Input::redondearNumero($antiguedadAlaVentaCreacionDTO->aavPrecioVenta, 2);
            }

            if (isset($antiguedadAlaVentaCreacionDTO->tasacion)) {
                $antiguedadAlaVentaCreacionDTO->tasacion = $this->getByIdInterno(
                    query: 'TASACIONDIGITAL',
                    classDTO: TasacionDigitalDTO::class,
                    linkExterno: $mysqli,
                    id: $antiguedadAlaVentaCreacionDTO->tasacion->tadId
                );

                $queryInsitu = "SELECT tisId, tisTadId, tisDomTasId, tisFechaTasInSituSolicitada, tisFechaTasInSituProvisoria, 
                             tisFechaTasInSituRealizada, tisFechaTasInSituRechazada, tisObservacionesInSitu, tisPrecioInSitu
                    FROM tasacioninsitu
                    INNER JOIN tasaciondigital ON tisTadId = tadId
                    WHERE tisFechaBaja IS NULL AND tisTadId = {$antiguedadAlaVentaCreacionDTO->tasacion->tadId}";

                // Si la tasación digital tiene una tasación in situ asociada, la obtenemos (se asume que puede ser nula)
                $antiguedadAlaVentaCreacionDTO->tasacion->tasacionInSitu = $this->getByIdInternoAllowsNull(
                    query: $queryInsitu,
                    classDTO: TasacionInSituDTO::class,
                    linkExterno: $mysqli
                );
            }

            $this->antiguedadesAlaVentaValidacionService->validarInput(
                linkExterno: $mysqli,
                entidadDTO: $antiguedadAlaVentaCreacionDTO,
                extraParams: $claimDTO
            );

            //Input::escaparDatos($antiguedadAlaVentaCreacionDTO, $mysqli); // No es necesario, no hay campos de tipo string
            //Input::agregarComillas_ConvertNULLtoString($antiguedadAlaVentaCreacionDTO); //No es necesario, no hay campos de tipo string

            $this->cambiarEstadoAntiguedad($mysqli, $antiguedadAlaVentaCreacionDTO->antiguedad, TipoEstadoEnum::AlaVenta());

            $query = "INSERT INTO antiguedadesalaventa (aavAntId, aavDomOrigen, aavPrecioVenta, aavTadId)
                      VALUES ({$antiguedadAlaVentaCreacionDTO->antiguedad->antId}, 
                              {$antiguedadAlaVentaCreacionDTO->domicilio->domId}, 
                              {$antiguedadAlaVentaCreacionDTO->aavPrecioVenta}, 
                              {$antiguedadAlaVentaCreacionDTO->tasacion->tadId}";

            $resultado = $mysqli->query($query);
            if ($resultado === false) {
                $error = $mysqli->error;
                throw new mysqli_sql_exception(code: 500, message: 'Falló la consulta: ' . $error);
            }
            $ret = [
                'id' => $mysqli->insert_id
            ];

            $mysqli->commit(); // Confirmar transacción si todo sale bien
            Output::outputJson($ret, 201);
        } catch (\Throwable $th) {

            if (isset($mysqli) && $mysqli instanceof mysqli) {
                $mysqli->rollback(); // Revertir transacción si hay error
            }

            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        } finally {
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }
}

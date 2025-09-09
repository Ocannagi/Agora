<?php

use Utilidades\Output;
use Utilidades\Input;
use Utilidades\Querys;
use Model\CustomException;

class CompraVentaController extends BaseController
{
    private ValidacionServiceBase $comprasVentasValidacionService;
    private ISecurity $securityService;

    private static $instancia = null; // La única instancia de la clase

    use TraitGetInterno; // Trait para usar getInterno con linkExterno
    use TraitGetByIdInterno; // Trait para usar getByIdInterno con linkExterno

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $comprasVentasValidacionService)
    {
        parent::__construct($dbConnection);
        $this->comprasVentasValidacionService = $comprasVentasValidacionService;
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $comprasVentasValidacionService): CompraVentaController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService, $comprasVentasValidacionService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    public function postCompraVenta()
    {
        $mysqli = $this->dbConnection->conectarBD();
        $stmtDetalle = $mysqli->prepare("INSERT INTO compraventadetalle (cvdCovId, cvdAavId, cvdFechaEntregaPrevista)
                                          VALUES (?, ?, ?)");
        try {
            $mysqli->begin_transaction(); // Iniciar transacción
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());
            $data = Input::getArrayBody(msgEntidad: "la CompraVenta");

            $this->comprasVentasValidacionService->validarType(className: CompraVentaCreacionDTO::class, datos: $data);
            $compraVentaCreacionDTO = new CompraVentaCreacionDTO($data);
            // Verifica que el usrId del token coincida con el usrId del comprador
            if (!TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico() && $claimDTO->usrId !== $compraVentaCreacionDTO->usuarioComprador->usrId) {
                throw new CustomException(code: 403, message: "El usuario no tiene permiso para realizar esta acción.");
            }
            
            $compraVentaCreacionDTO->usuarioComprador = $this->getByIdInterno(id: $compraVentaCreacionDTO->usuarioComprador->usrId,
                                                                              classDTO: UsuarioDTO::class,
                                                                              linkExterno: $mysqli,
                                                                              query: 'USUARIO');

            $compraVentaCreacionDTO->domicilioDestino = $this->getByIdInterno(id: $compraVentaCreacionDTO->domicilioDestino->domId,
                                                                             classDTO: DomicilioDTO::class,
                                                                             linkExterno: $mysqli,
                                                                             query: 'DOMICILIO');


            if (count($compraVentaCreacionDTO->detalles) === 0) {
                throw new CustomException(code: 400, message: "Se deben agregar al menos un detalle a la compra/venta.");
            }

            //TODO : cambiar este mecanismo por un stmt
            $queryAntiguedadAlaVenta = "SELECT aavId, aavAntId, aavDomOrigen, aavPrecioVenta, aavTadId, aavFechaPublicacion, aavFechaRetiro, aavHayVenta, tisId
                                         FROM antiguedadesalaventa
                                         LEFT  JOIN tasacioninsitu ON aavTadId = tisTadId
                                                       AND tisFechaBaja IS NULL
                                                       AND tisFechaTasInSituRealizada IS NOT NULL --IMPORTANTE: Solo se consideran las tasaciones in situ realizadas
                                         WHERE aavId = %id AND aavFechaRetiro IS NULL AND aavHayVenta = FALSE";
            for ($i=0; $i < count($compraVentaCreacionDTO->detalles); $i++) { 
                //getByIdInterno se asegura que exista la antiguedad a la venta y que no esté retirada ni vendida
                $compraVentaCreacionDTO->detalles[$i]->antiguedadAlaVenta = $this->getByIdInterno(id: $compraVentaCreacionDTO->detalles[$i]->antiguedadAlaVenta->aavId,
                                                                                      classDTO: AntiguedadALaVentaDTO::class,
                                                                                      linkExterno: $mysqli,
                                                                                      query: $queryAntiguedadAlaVenta);
            }
            
                                                        
            $this->comprasVentasValidacionService->validarInput($mysqli, $compraVentaCreacionDTO, $claimDTO);
            Input::escaparDatos($compraVentaCreacionDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($compraVentaCreacionDTO);

            $query = "INSERT INTO compraventa (covUsrComprador, covDomDestino, covTipoMedioPago)
                      VALUES ({$compraVentaCreacionDTO->usuarioComprador->usrId},
                              {$compraVentaCreacionDTO->domicilioDestino->domId},
                              '{$compraVentaCreacionDTO->covTipoMedioPago->value}')";

            $resultado = $mysqli->query($query);
             if ($resultado === false) {
                $error = $mysqli->error;
                throw new mysqli_sql_exception(code: 500, message: 'Falló la consulta: ' . $error);
            }
            $covId = $mysqli->insert_id;
            $cvdIds = [];

            foreach ($compraVentaCreacionDTO->detalles as $detalle) {
                $stmtDetalle->bind_param("iis", $covId, $detalle->antiguedadAlaVenta->aavId, $detalle->cvdFechaEntregaPrevista);
                if (!$stmtDetalle->execute()) {
                    throw new mysqli_sql_exception("Error al insertar la Compraventa Detalle: " . $stmtDetalle->error);
                }
                $cvdIds[] = $stmtDetalle->insert_id;
            }
            
            $mysqli->commit(); // Confirmar la transacción
            Output::outputJson(['covId' => $covId, 'cvdIds' => $cvdIds], 201); // Retornar el ID de la compra-venta creada y los IDs de los detalles

        } catch (\Throwable $th) {

            if (isset($mysqli) && $mysqli instanceof mysqli) {
                $mysqli->rollback(); // Revertir transacción si hay error
            }

            if ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } elseif ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        } finally {
            if (isset($stmtDetalle) && $stmtDetalle instanceof mysqli_stmt) {
                $stmtDetalle->close(); // Cerrar el statement
            }
            
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }
}

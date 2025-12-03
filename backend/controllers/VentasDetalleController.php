<?php

use Utilidades\Output;
use Utilidades\Input;
use Utilidades\Querys;
use Model\CustomException;


class VentasDetalleController extends BaseController
{
    private ISecurity $securityService;
    private static $instancia = null; // La única instancia de la clase

    use TraitGetInterno;
    use TraitGetByIdInterno;
    use TraitGetPaginado; // Trait para obtener paginados genéricos

    private function __construct(IDbConnection $dbConnection, ISecurity $securityService)
    {
        parent::__construct($dbConnection);
        $this->securityService = $securityService;
    }

    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService): VentasDetalleController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    public function getVentasDetallePaginado($paginado)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());

            if (!TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico()) {
                $where = " aavUsrIdVendedor = {$claimDTO->usrId} AND cvdFechaBaja IS NULL";
            } else {
                $where = " cvdFechaBaja IS NULL";
            }

            $query = "SELECT DISTINCT cvdId, covId, covUsrComprador, covDomDestino, covFechaCompra,
                                        aavId,
                                        covTipoMedioPago, cvdFechaEntregaPrevista, cvdFechaEntregaReal
                        FROM compraventadetalle
                        INNER JOIN compraventa ON cvdCovId = covId
                        INNER JOIN antiguedadalaventa ON cvdAavId = aavId
                        WHERE" . $where . "
                        ORDER BY cvdId DESC";


            $paginadoResponseDTO = $this->getPaginadoResponseDTO(
                paginado: $paginado,
                mysqli: $mysqli,
                baseCount: 'compraventadetalle INNER JOIN compraventa ON cvdCovId = covId INNER JOIN antiguedadalaventa ON cvdAavId = aavId',
                whereCount: $where,
                msgCount: 'obtener el total de ventas-detalle para paginado',
                queryClassDTO: $query,
                classDTO: VentaDetalleDTO::class
            );

            $arrayVentasDetalleDTO = $paginadoResponseDTO->arrayEntidad;

            for ($i = 0; $i < count($arrayVentasDetalleDTO); $i++) {
                $arrayVentasDetalleDTO[$i] = $this->obtenerVentaDetalleCompleto($arrayVentasDetalleDTO[$i], $mysqli);
            }

            $paginadoResponseDTO->arrayEntidad = $arrayVentasDetalleDTO;

            Output::outputJson($paginadoResponseDTO);

        } catch (\Throwable $th) {
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
            if (isset($mysqli) && $mysqli instanceof mysqli) { // Verificar si la conexión fue establecida
                $mysqli->close(); // Cerrar la conexión a la base de datos
            }
        }
    }

    public function getVentasDetalle()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());
            // Si no es soporte técnico, solo puede ver sus propias ventas
            if (!TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico()) {
                $where = " WHERE aavUsrIdVendedor = {$claimDTO->usrId} AND cvdFechaBaja IS NULL";
            } else {
                $where = " WHERE cvdFechaBaja IS NULL";
            }

            $query = "SELECT DISTINCT cvdId, covId, covUsrComprador, covDomDestino, covFechaCompra,
                                        aavId,
                                        covTipoMedioPago, cvdFechaEntregaPrevista, cvdFechaEntregaReal
                        FROM compraventadetalle
                        INNER JOIN compraventa ON cvdCovId = covId
                        INNER JOIN antiguedadalaventa ON cvdAavId = aavId
                        " . $where . "
                        ORDER BY cvdId DESC";

            $arrayVentasDetalleDTO = $this->getInterno(
                classDTO: VentaDetalleDTO::class,
                query: $query,
                linkExterno: $mysqli
            );

            foreach ($arrayVentasDetalleDTO as $ventaDetalleDTO) {
                $ventaDetalleDTO = $this->obtenerVentaDetalleCompleto($ventaDetalleDTO, $mysqli);
            }

            Output::outputJson($arrayVentasDetalleDTO);
        } catch (\Throwable $th) {
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
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }

    public function getVentasDetalleById($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());

            $ventaDetalleDTO = $this->getByIdInterno(
                classDTO: VentaDetalleDTO::class,
                linkExterno: $mysqli,
                query: "SELECT cvdId, covId, covUsrComprador, covDomDestino, covFechaCompra,
                                        aavId,
                                        covTipoMedioPago, cvdFechaEntregaPrevista, cvdFechaEntregaReal
                                FROM compraventadetalle
                                INNER JOIN compraventa ON cvdCovId = covId
                                INNER JOIN antiguedadalaventa ON cvdAavId = aavId
                                WHERE cvdId = %id AND cvdFechaBaja IS NULL",
                id: $id
            );

            $ventaDetalleDTO = $this->obtenerVentaDetalleCompleto($ventaDetalleDTO, $mysqli);

            // Si no es soporte técnico, solo puede ver sus propias ventas
            if (!TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico()) {
                if ($ventaDetalleDTO->antiguedadAlaVenta->vendedor->usrId !== $claimDTO->usrId) {
                    throw new CustomException(code: 403, message: "No tiene permiso para ver este detalle de venta.");
                }
            }

            Output::outputJson($ventaDetalleDTO);
        } catch (\Throwable $th) {
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
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }

    //Solo se permite al vendedor (no al comprador) o al soporte técnico actualizar la fecha de entrega real
    public function patchVentasDetalle($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());
            settype($id, 'integer');

            $data = Input::getArrayBody(msgEntidad: "la Venta Detalle");

            $data['cvdId'] = $id; // Asegura que el ID a modificar sea el de la URL

            $cvdId = $data['cvdId'];

            if (!array_key_exists('cvdFechaEntregaReal', $data) || !is_string($data['cvdFechaEntregaReal']) || empty(trim($data['cvdFechaEntregaReal']))) {
                throw new InvalidArgumentException("El campo 'cvdFechaEntregaReal' es obligatorio y debe ser una cadena no vacía.");
            }

            $cvdFechaEntregaReal = trim($data['cvdFechaEntregaReal']);
            Input::esFechaValidaYNoPasada($cvdFechaEntregaReal);

            try {
                // Obtener el detalle de venta para verificar permisos
                $ventaDetalleDTO = $this->getByIdInterno(
                    classDTO: VentaDetalleDTO::class,
                    linkExterno: $mysqli,
                    query: "SELECT cvdId, covId, covUsrComprador, covDomDestino, covFechaCompra,
                                        aavId,
                                        covTipoMedioPago, cvdFechaEntregaPrevista, cvdFechaEntregaReal
                                FROM compraventadetalle
                                INNER JOIN compraventa ON cvdCovId = covId
                                INNER JOIN antiguedadalaventa ON cvdAavId = aavId
                                WHERE cvdId = %id AND cvdFechaBaja IS NULL AND cvdFechaEntregaReal IS NULL",
                    id: $cvdId
                );
            } catch (\Throwable $th) {
                if ($th instanceof CustomException && $th->getCode() === 404) {
                    throw new CustomException(code: 400, message: "No se puede actualizar la fecha de entrega real porque ya fue establecida previamente o el detalle de venta no existe.");
                } else {
                    throw $th;
                }
            }


            $hoy = (new DateTime())->format('Y-m-d');
            Input::esFechaMayorOIgual($hoy, $cvdFechaEntregaReal);

            $ventaDetalleDTO = $this->obtenerVentaDetalleCompleto($ventaDetalleDTO, $mysqli);


            // Verificar que el usuario sea el vendedor o soporte técnico
            if ($ventaDetalleDTO->antiguedadAlaVenta->vendedor->usrId !== $claimDTO->usrId && !TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico()) {
                throw new CustomException(code: 403, message: "No tiene permiso para actualizar este detalle de venta.");
            }
            // Actualizar la fecha de entrega real
            $updateQuery = "UPDATE compraventadetalle SET cvdFechaEntregaReal = '{$cvdFechaEntregaReal}' WHERE cvdId = {$cvdId} AND cvdFechaBaja IS NULL";
            $result = $mysqli->query($updateQuery);
            if ($result === false) {
                throw new CustomException(code: 500, message: "Error al actualizar la fecha de entrega real: " . $mysqli->error);
            }

            Output::outputJson([]);
        } catch (\Throwable $th) {
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
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }

    private function obtenerVentaDetalleCompleto(VentaDetalleDTO $ventaDetalleDTO, mysqli $mysqli): VentaDetalleDTO
    {
        $ventaDetalleDTO->usuarioComprador = $this->getByIdInterno(
            classDTO: UsuarioDTO::class,
            linkExterno: $mysqli,
            query: "USUARIO",
            id: $ventaDetalleDTO->usuarioComprador->usrId
        );

        $ventaDetalleDTO->domicilioDestino = $this->getByIdInterno(
            classDTO: DomicilioDTO::class,
            linkExterno: $mysqli,
            query: "DOMICILIO",
            id: $ventaDetalleDTO->domicilioDestino->domId
        );

        $ventaDetalleDTO->antiguedadAlaVenta = $this->getByIdInterno(
            classDTO: AntiguedadAlaVentaDTO::class,
            linkExterno: $mysqli,
            query: "SELECT aavId, aavAntId, aavUsrIdVendedor, aavDomOrigen, aavPrecioVenta, aavTadId, aavFechaPublicacion, aavFechaRetiro, aavHayVenta, tisId
                            FROM antiguedadalaventa
                            LEFT  JOIN tasacioninsitu
                                ON aavTadId = tisTadId
                                AND tisFechaBaja IS NULL
                                AND tisFechaTasInSituRealizada IS NOT NULL
                            WHERE aavId = {$ventaDetalleDTO->antiguedadAlaVenta->aavId}",
            id: $ventaDetalleDTO->antiguedadAlaVenta->aavId
        ); //IMPORTANTE: Solo se consideran las tasaciones in situ realizadas

        $ventaDetalleDTO->antiguedadAlaVenta->antiguedad = $this->getByIdInterno(
            classDTO: AntiguedadDTO::class,
            linkExterno: $mysqli,
            query: "SELECT antId, antNombre, antDescripcion, antFechaEstado, antTipoEstado
                        ,perId, perDescripcion
                        ,scatId, catId, catDescripcion, scatDescripcion
                        ,usrId, usrNombre, usrApellido, usrEmail, usrTipoUsuario, usrRazonSocialFantasia,usrDescripcion,usrScoring,usrCuitCuil,usrMatricula
                        ,domId, domCPA, domCalleRuta, domNroKm, domPiso, domDepto
                        ,locId, locDescripcion, provId, provDescripcion
                    FROM antiguedad
                    INNER JOIN periodo ON antPerId = perId
                    INNER JOIN subcategoria ON antScatId = scatId
                    INNER JOIN categoria ON scatCatId = catId
                    INNER JOIN usuario ON antUsrId = usrId
                    INNER JOIN domicilio ON usrDomicilio = domId
                    INNER JOIN localidad ON locId = domLocId
                    INNER JOIN provincia ON provId = locProvId
                  WHERE antId = %id",
            id: $ventaDetalleDTO->antiguedadAlaVenta->antiguedad->antId
        );

        $ventaDetalleDTO->antiguedadAlaVenta->antiguedad->imagenes = $this->getInterno(
            classDTO: ImagenAntiguedadDTO::class,
            linkExterno: $mysqli,
            query: "SELECT imaId, imaUrl, imaAntId, imaOrden, imaNombreArchivo FROM imagenantiguedad WHERE imaAntId = {$ventaDetalleDTO->antiguedadAlaVenta->antiguedad->antId} ORDER BY imaOrden"
        );

        $ventaDetalleDTO->antiguedadAlaVenta->vendedor = $this->getByIdInterno(
            classDTO: UsuarioDTO::class,
            linkExterno: $mysqli,
            query: "USUARIO",
            id: $ventaDetalleDTO->antiguedadAlaVenta->vendedor->usrId
        );

        $ventaDetalleDTO->antiguedadAlaVenta->domicilioOrigen = $this->getByIdInterno(
            classDTO: DomicilioDTO::class,
            linkExterno: $mysqli,
            query: "DOMICILIO",
            id: $ventaDetalleDTO->antiguedadAlaVenta->domicilioOrigen->domId
        );

        $ventaDetalleDTO->antiguedadAlaVenta->tasacion = null; // No queremos mostrar la tasación en el detalle de la venta

        return $ventaDetalleDTO;
    }
}

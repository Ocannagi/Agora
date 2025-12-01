<?php

use Utilidades\Output;
use Utilidades\Input;
use Utilidades\Querys;
use Model\CustomException;

class ComprasVentasController extends BaseController
{
    private ValidacionServiceBase $comprasVentasValidacionService;
    private ISecurity $securityService;

    private static $instancia = null; // La única instancia de la clase

    use TraitGetInterno; // Trait para usar getInterno con linkExterno
    use TraitGetByIdInterno; // Trait para usar getByIdInterno con linkExterno
    use TraitCambiarEstadoAntiguedad; // Trait para cambiar el estado de la antiguedad
    use TraitGetPaginado; // Trait para obtener paginados genéricos

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $comprasVentasValidacionService)
    {
        parent::__construct($dbConnection);
        $this->comprasVentasValidacionService = $comprasVentasValidacionService;
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $comprasVentasValidacionService): ComprasVentasController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService, $comprasVentasValidacionService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    public function getComprasVentasPaginado($paginado)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());

            if (!TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico()) {
                $where = " covUsrComprador = {$claimDTO->usrId} AND covFechaBaja IS NULL";
            } else {
                $where = " covFechaBaja IS NULL";
            }

            $query = "SELECT covId, covUsrComprador, covDomDestino, covFechaCompra, covTipoMedioPago
                      FROM compraventa WHERE" . $where . " ORDER BY covFechaCompra DESC, covId DESC ";

            $paginadoResponseDTO = $this->getPaginadoResponseDTO(
                paginado: $paginado,
                mysqli: $mysqli,
                baseCount: 'compraventa',
                whereCount: $where,
                msgCount: 'obtener el total de compras-ventas para paginado',
                queryClassDTO: $query,
                classDTO: CompraVentaDTO::class
            );

            $arrayComprasVentasDTO = $paginadoResponseDTO->arrayEntidad;

            for ($i = 0; $i < count($arrayComprasVentasDTO); $i++) {
                $arrayComprasVentasDTO[$i] = $this->obtenerCompraVentaDTOCompleto($arrayComprasVentasDTO[$i]->covId, $mysqli);
            }

            $paginadoResponseDTO->arrayEntidad = $arrayComprasVentasDTO;

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

    public function getComprasVentas()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());
            // Si no es soporte técnico, solo puede ver sus propias compras
            if (!TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico()) {
                $where = " WHERE covUsrComprador = {$claimDTO->usrId} AND covFechaBaja IS NULL";
            } else {
                $where = " WHERE covFechaBaja IS NULL";
            }

            $arrayComprasVentasDTO = $this->getInterno(
                classDTO: CompraVentaDTO::class,
                linkExterno: $mysqli,
                query: "SELECT covId, covUsrComprador, covDomDestino, covFechaCompra, covTipoMedioPago
                                                           FROM compraventa " . $where
            );

            for ($i = 0; $i < count($arrayComprasVentasDTO); $i++) {
                $arrayComprasVentasDTO[$i] = $this->obtenerCompraVentaDTOCompleto($arrayComprasVentasDTO[$i]->covId, $mysqli);
            }

            Output::outputJson($arrayComprasVentasDTO);
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

    public function getComprasVentasById($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            settype($id, 'integer');
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::compradorVendedorToArray());

            if (!TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico()) {
                // Si no es soporte técnico, solo puede ver sus propias compras
                if (!Querys::existeEnBD(
                    link: $mysqli,
                    query: "SELECT 1
                                             FROM compraventa
                                             WHERE covId = $id
                                               AND covUsrComprador = {$claimDTO->usrId}
                                               AND covFechaBaja IS NULL",
                    msg: "saber si la compra/venta pertenece al usuario"
                )) {
                    throw new CustomException(code: 403, message: "El usuario no tiene permiso para ver esta compra/venta.");
                }
            }

            $compraVentaDTO = $this->obtenerCompraVentaDTOCompleto($id, $mysqli);

            Output::outputJson($compraVentaDTO);
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

    private function obtenerCompraVentaDTOCompleto(int $idCompraVenta, mysqli $mysqli): CompraVentaDTO
    {
        settype($idCompraVenta, 'integer');
        $compraVentaDTO = $this->getByIdInterno(
            id: $idCompraVenta,
            classDTO: CompraVentaDTO::class,
            linkExterno: $mysqli,
            query: "SELECT covId, covUsrComprador, covDomDestino, covFechaCompra, covTipoMedioPago
                                                           FROM compraventa
                                                           WHERE covId = %id
                                                           AND covFechaBaja IS NULL"
        );

        $compraVentaDTO->usuarioComprador = $this->getByIdInterno(
            id: $compraVentaDTO->usuarioComprador->usrId,
            classDTO: UsuarioDTO::class,
            linkExterno: $mysqli,
            query: 'USUARIO'
        );

        $compraVentaDTO->domicilioDestino = $this->getByIdInterno(
            id: $compraVentaDTO->domicilioDestino->domId,
            classDTO: DomicilioDTO::class,
            linkExterno: $mysqli,
            query: 'DOMICILIO'
        );

        $compraVentaDTO->detalles = $this->getInterno(
            classDTO: CompraVentaDetalleDTO::class,
            linkExterno: $mysqli,
            query: "SELECT cvdId, cvdCovId, cvdAavId, cvdFechaEntregaPrevista, cvdFechaEntregaReal
                                                                 FROM compraventadetalle
                                                                 WHERE cvdCovId = {$compraVentaDTO->covId}
                                                                 AND cvdFechaBaja IS NULL"
        );

        //var_dump($compraVentaDTO->detalles);

        for ($i = 0; $i < count($compraVentaDTO->detalles); $i++) {

            $compraVentaDTO->detalles[$i]->antiguedadAlaVenta = $this->getByIdInterno(
                id: $compraVentaDTO->detalles[$i]->antiguedadAlaVenta->aavId,
                classDTO: AntiguedadAlaVentaDTO::class,
                linkExterno: $mysqli,
                query: "SELECT aavId, aavAntId, aavUsrIdVendedor, aavDomOrigen, aavPrecioVenta, aavTadId, aavFechaPublicacion, aavFechaRetiro, aavHayVenta, tisId
                                                                                               FROM antiguedadalaventa
                                                                                               LEFT  JOIN tasacioninsitu ON aavTadId = tisTadId
                                                                                                             AND tisFechaBaja IS NULL
                                                                                                             AND tisFechaTasInSituRealizada IS NOT NULL 
                                                                                               WHERE aavId = {$compraVentaDTO->detalles[$i]->antiguedadAlaVenta->aavId}"
            ); //IMPORTANTE: Solo se consideran las tasaciones in situ realizadas

            $compraVentaDTO->detalles[$i]->antiguedadAlaVenta->vendedor = $this->getByIdInterno(
                id: $compraVentaDTO->detalles[$i]->antiguedadAlaVenta->vendedor->usrId,
                classDTO: UsuarioDTO::class,
                linkExterno: $mysqli,
                query: 'USUARIO'
            );

            $compraVentaDTO->detalles[$i]->antiguedadAlaVenta->antiguedad = $this->getByIdInterno(
                id: $compraVentaDTO->detalles[$i]->antiguedadAlaVenta->antiguedad->antId,
                classDTO: AntiguedadDTO::class,
                linkExterno: $mysqli,
                query: 'ANTIGUEDAD'
            );
            $compraVentaDTO->detalles[$i]->antiguedadAlaVenta->antiguedad->imagenes = $this->getInterno(
                query: "SELECT imaId, imaUrl, imaAntId, imaOrden, imaNombreArchivo FROM imagenantiguedad WHERE imaAntId = {$compraVentaDTO->detalles[$i]->antiguedadAlaVenta->antiguedad->antId} ORDER BY imaOrden",
                classDTO: ImagenAntiguedadDTO::class,
                linkExterno: $mysqli
            );
            $compraVentaDTO->detalles[$i]->antiguedadAlaVenta->domicilioOrigen = $this->getByIdInterno(
                id: $compraVentaDTO->detalles[$i]->antiguedadAlaVenta->domicilioOrigen->domId,
                classDTO: DomicilioDTO::class,
                linkExterno: $mysqli,
                query: 'DOMICILIO'
            );

            $compraVentaDTO->detalles[$i]->antiguedadAlaVenta->tasacion = null; // No queremos traer la tasación digital completa en este contexto
        }

        // var_dump($compraVentaDTO->detalles);
        return $compraVentaDTO;
    }

    public function postComprasVentas()
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

            if (!isset($compraVentaCreacionDTO->usuarioComprador) || !isset($compraVentaCreacionDTO->usuarioComprador->usrId)) {
                throw new CustomException(code: 400, message: "El usuario comprador es obligatorio.");
            }

            $compraVentaCreacionDTO->usuarioComprador = $this->getByIdInterno(
                id: $compraVentaCreacionDTO->usuarioComprador->usrId,
                classDTO: UsuarioDTO::class,
                linkExterno: $mysqli,
                query: 'USUARIO'
            );

            if (!isset($compraVentaCreacionDTO->domicilioDestino) || !isset($compraVentaCreacionDTO->domicilioDestino->domId)) {
                throw new CustomException(code: 400, message: "El domicilio de destino es obligatorio.");
            }
            $compraVentaCreacionDTO->domicilioDestino = $this->getByIdInterno(
                id: $compraVentaCreacionDTO->domicilioDestino->domId,
                classDTO: DomicilioDTO::class,
                linkExterno: $mysqli,
                query: 'DOMICILIO'
            );


            if (count($compraVentaCreacionDTO->detalles) === 0) {
                throw new CustomException(code: 400, message: "Se deben agregar al menos un detalle a la compra/venta.");
            }

            //TODO : cambiar este mecanismo por un stmt
            $queryAntiguedadAlaVenta = "SELECT aavId, aavAntId, aavUsrIdVendedor, aavDomOrigen, aavPrecioVenta, aavTadId, aavFechaPublicacion, aavFechaRetiro, aavHayVenta, tisId
                                         FROM antiguedadalaventa
                                         LEFT  JOIN tasacioninsitu ON aavTadId = tisTadId
                                                       AND tisFechaBaja IS NULL
                                                       AND tisFechaTasInSituRealizada IS NOT NULL
                                         WHERE aavId = %id AND aavFechaRetiro IS NULL AND aavHayVenta = FALSE"; //IMPORTANTE: Solo se consideran las tasaciones in situ realizadas
            
            
            for ($i = 0; $i < count($compraVentaCreacionDTO->detalles); $i++) {

                //getByIdInterno se asegura que exista la antiguedad a la venta y que no esté retirada ni vendida
                $compraVentaCreacionDTO->detalles[$i]->antiguedadAlaVenta = $this->getByIdInterno(
                    id: $compraVentaCreacionDTO->detalles[$i]->antiguedadAlaVenta->aavId,
                    classDTO: AntiguedadAlaVentaDTO::class,
                    linkExterno: $mysqli,
                    query: $queryAntiguedadAlaVenta
                );

                $precioVtaFront = $compraVentaCreacionDTO->detalles[$i]->antiguedadAlaVenta->aavPrecioVenta;

                // Verificar si el precio de venta coincide con el actual en la base de datos
                if (isset($precioVtaFront)) {
                    if ($compraVentaCreacionDTO->detalles[$i]->antiguedadAlaVenta->aavPrecioVenta != $precioVtaFront) {
                        throw new CustomException(code: 400, message: "El precio de venta de la antigüedad a la venta con ID {$compraVentaCreacionDTO->detalles[$i]->antiguedadAlaVenta->aavId} no coincide con el precio actual.");
                    }
                }

                $compraVentaCreacionDTO->detalles[$i]->antiguedadAlaVenta->antiguedad = $this->getByIdInterno(
                    id: $compraVentaCreacionDTO->detalles[$i]->antiguedadAlaVenta->antiguedad->antId,
                    classDTO: AntiguedadDTO::class,
                    linkExterno: $mysqli,
                    query: 'ANTIGUEDAD'
                );
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

                $this->cerrarTasaciones($mysqli, $detalle->antiguedadAlaVenta);
                $this->pasarAHayVenta($mysqli, $detalle->antiguedadAlaVenta);
                $this->cambiarEstadoAntiguedadToComprado($mysqli, $detalle->antiguedadAlaVenta, $compraVentaCreacionDTO->usuarioComprador->usrId);
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

    //El soporte técnico lo único que puede modificar es el domicilio de destino
    //y solo si no se ha entregado ninguna antigüedad aún.
    public function patchComprasVentas($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            settype($id, 'integer');
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());
            $data = Input::getArrayBody(msgEntidad: "la CompraVenta");

            $data['covId'] = $id; // Asegura que el ID a modificar sea el de la URL

            $this->comprasVentasValidacionService->validarType(className: CompraVentaDTO::class, datos: $data);
            if (!array_key_exists('covId', $data)) {
                throw new CustomException(code: 400, message: "El campo covId es obligatorio para modificar una compra/venta.");
            }
            if (!array_key_exists('domicilioDestino', $data) && !array_key_exists('covDomDestino', $data) && !array_key_exists('domId', $data)) {
                throw new CustomException(code: 400, message: "El campo domicilio de destino es obligatorio para modificar una compra/venta.");
            }

            $compraVentaDTO = new CompraVentaDTO($data);

            $this->ValidacionCustomPatchCompraVenta($compraVentaDTO, $mysqli);

            $query = "UPDATE compraventa
                      SET covDomDestino = {$compraVentaDTO->domicilioDestino->domId}
                      WHERE covId = {$compraVentaDTO->covId}";
            $resultado = $mysqli->query($query);
            if ($resultado === false) {
                $error = $mysqli->error;
                throw new mysqli_sql_exception(code: 500, message: 'Falló la consulta: ' . $error);
            }
            Output::outputJson(['covId' => $compraVentaDTO->covId], 200); // Retornar el ID de la compra-venta modificada
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

    private function ValidacionCustomPatchCompraVenta(CompraVentaDTO $compraVentaDTO, mysqli $mysqli)
    {
        if (!Querys::existeEnBD(
            link: $mysqli,
            query: "SELECT 1
                                FROM compraventa
                                WHERE covId = {$compraVentaDTO->covId}
                                AND covFechaBaja IS NULL",
            msg: "saber si la compra/venta existe y no está dada de baja"
        )) {
            throw new CustomException(code: 404, message: "No existe la compra/venta o ya está dada de baja.");
        }

        if (Querys::existeEnBD(
            link: $mysqli,
            query: "   SELECT cvdId
                                            FROM compraventadetalle
                                            WHERE cvdCovId = {$compraVentaDTO->covId}
                                            AND cvdFechaEntregaReal IS NOT NULL
                                            LIMIT 1",
            msg: "saber si existe al menos un detalle con fecha de entrega real"
        )) {
            throw new CustomException(code: 400, message: "No se puede modificar la compra-venta porque ya se ha entregado al menos una antigüedad.");
        }

        if (Querys::existeEnBD(
            link: $mysqli,
            query: "SELECT 1
                                        FROM compraventadetalle
                                        INNER JOIN antiguedadalaventa 
                                            ON cvdAavId = aavId
                                        WHERE cvdCovId = {$compraVentaDTO->covId}
                                        AND aavDomOrigen = {$compraVentaDTO->domicilioDestino->domId}",
            msg: "saber si el domicilio de origen es el mismo que el de destino"
        )) {
            throw new CustomException(code: 400, message: "El domicilio de origen y destino no pueden ser el mismo.");
        }

        if (!Querys::existeEnBD(
            link: $mysqli,
            query: "SELECT 1
                            FROM compraventa as cv
                            INNER JOIN usuariodomicilio as ud
                            ON ud.udomUsr = cv.covUsrComprador
                            WHERE
                            cv.covId = {$compraVentaDTO->covId}
                            AND ud.udomDom = {$compraVentaDTO->domicilioDestino->domId}",
            msg: "saber si el domicilio de origen es el mismo que el de destino"
        )) {
            throw new CustomException(code: 400, message: "El domicilio de destino no pertenece al usuario comprador.");
        }
    }

    //El soporte técnico solo puede eliminar compras/ventas que tengan menos de 10 días desde su creación (Boton de arrepentimiento).
    public function deleteComprasVentas($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $mysqli->begin_transaction(); // Iniciar transacción
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());
            settype($id, 'integer');

            //getByIdInterno devuelve una excepción si no existe la compra/venta o ya está dada de baja
            $compraVentaDTO = $this->getByIdInterno(
                id: $id,
                classDTO: CompraVentaDTO::class,
                linkExterno: $mysqli,
                query: 'SELECT covId, covUsrComprador, covDomDestino, covFechaCompra, covTipoMedioPago
                                                          FROM compraventa
                                                          WHERE covId = %id AND covFechaBaja IS NULL'
            );

            $compraVentaDTO->detalles = $this->getInterno(
                classDTO: CompraVentaDetalleDTO::class,
                linkExterno: $mysqli,
                query: "SELECT cvdId, cvdCovId, cvdAavId, cvdFechaEntregaPrevista, cvdFechaEntregaReal
                                                                 FROM compraventadetalle
                                                                 WHERE cvdCovId = {$compraVentaDTO->covId}
                                                                 AND cvdFechaBaja IS NULL"
            );

            for ($i = 0; $i < count($compraVentaDTO->detalles); $i++) {
                $compraVentaDTO->detalles[$i]->antiguedadAlaVenta = $this->getByIdInterno(
                    id: $compraVentaDTO->detalles[$i]->antiguedadAlaVenta->aavId,
                    classDTO: AntiguedadAlaVentaDTO::class,
                    linkExterno: $mysqli,
                    query: "SELECT aavId, aavAntId, aavUsrIdVendedor, aavDomOrigen, aavPrecioVenta, aavTadId, aavFechaPublicacion, aavFechaRetiro, aavHayVenta, tisId
                                                                                              FROM antiguedadalaventa
                                                                                              LEFT  JOIN tasacioninsitu ON aavTadId = tisTadId
                                                                                                            AND tisFechaBaja IS NULL
                                                                                                            AND tisFechaTasInSituRealizada IS NOT NULL 
                                                                                              WHERE aavId = {$compraVentaDTO->detalles[$i]->antiguedadAlaVenta->aavId}"
                ); //IMPORTANTE: Solo se consideran las tasaciones in situ realizadas

                $compraVentaDTO->detalles[$i]->antiguedadAlaVenta->vendedor = $this->getByIdInterno(
                    id: $compraVentaDTO->detalles[$i]->antiguedadAlaVenta->vendedor->usrId,
                    classDTO: UsuarioDTO::class,
                    linkExterno: $mysqli,
                    query: 'USUARIO'
                );

                $compraVentaDTO->detalles[$i]->antiguedadAlaVenta->antiguedad = $this->getByIdInterno(
                    id: $compraVentaDTO->detalles[$i]->antiguedadAlaVenta->antiguedad->antId,
                    classDTO: AntiguedadDTO::class,
                    linkExterno: $mysqli,
                    query: 'ANTIGUEDAD'
                );
            }

            try {
                Input::esFechaMayorOIgual(fechaInicio: (new DateTime('now'))->format('Y-m-d'), fechaFin: Input::agregarDiasAFecha(substr($compraVentaDTO->covFechaCompra, 0, 10), 10));
            } catch (\Throwable $th) {
                if ($th instanceof InvalidArgumentException) {
                    throw new CustomException(code: 400, message: "No se puede eliminar la compra/venta porque han pasado más de 10 días desde su creación.");
                } else {
                    throw $th;
                }
            }

            $querybajaLogica = "UPDATE compraventa
                               SET covFechaBaja = NOW()
                               WHERE covId = {$compraVentaDTO->covId}
                                 AND covFechaBaja IS NULL";

            if (!$mysqli->query($querybajaLogica)) {
                throw new CustomException(code: 500, message: "Error al dar de baja la compra/venta: " . $mysqli->error);
            }

            $queryActualizarAntiguedadesAlaVenta = "UPDATE antiguedadalaventa
                                            SET aavHayVenta = FALSE
                                            WHERE aavId IN (SELECT cvdAavId
                                                            FROM compraventadetalle
                                                            WHERE cvdCovId = {$compraVentaDTO->covId}
                                                              AND cvdFechaBaja IS NULL)";

            if (!$mysqli->query($queryActualizarAntiguedadesAlaVenta)) {
                throw new CustomException(code: 500, message: "Error al actualizar las antigüedades a la venta: " . $mysqli->error);
            }


            foreach ($compraVentaDTO->detalles as $detalle) {
                $this->revertirAntiguedadComprada($mysqli, $detalle->antiguedadAlaVenta->antiguedad, $detalle->antiguedadAlaVenta->vendedor->usrId);
            }

            $queryDetalles = "UPDATE compraventadetalle
                              SET cvdFechaBaja = NOW()
                              WHERE cvdCovId = {$compraVentaDTO->covId}
                                AND cvdFechaBaja IS NULL";

            if (!$mysqli->query($queryDetalles)) {
                throw new CustomException(code: 500, message: "Error al dar de baja los detalles de la compra/venta: " . $mysqli->error);
            }

            $mysqli->commit(); // Confirmar la transacción
            Output::outputJson([], 200); // Retornar vacío con código 200
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
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }
}

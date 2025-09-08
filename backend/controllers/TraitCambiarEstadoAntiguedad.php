<?php

use Utilidades\Querys;

trait TraitCambiarEstadoAntiguedad
{
    use TraitGetByIdInterno;

    public function cambiarEstadoAntiguedad(mysqli $linkExterno, AntiguedadDTO $antiguedadDTO, TipoEstadoEnum $nuevoEstado): void
    {
        $query = "UPDATE antiguedad SET antTipoEstado = '{$nuevoEstado->value}', antFechaEstado = NOW() WHERE antId = {$antiguedadDTO->antId}";

        $resultado = $linkExterno->query($query);
        if ($resultado === false) {
            $error= $linkExterno->error;
            throw new mysqli_sql_exception(code:500, message:'Falló la consulta: ' . $error);
        }
    }

    public function ifCambiarEstadoAntiguedadFromTItoTD(mysqli $linkExterno, TasacionDigitalDTO $tasDigitalDTO)
    {
        $queryQuedanTasacionesInSitu = "SELECT 1 FROM tasacioninsitu as ti
                                            INNER JOIN tasaciondigital as td ON ti.tisTadId = td.tadId
                                            WHERE ti.tisFechaBaja IS NULL
                                            AND ti.tisFechaTasInSituRealizada IS NOT NULL
                                            AND td.tadAntId = {$tasDigitalDTO->antiguedad->antId}
                                            AND td.tadUsrPropId = {$tasDigitalDTO->propietario->usrId}
                                            AND td.tadFechaBaja IS NULL";


            if(!Querys::existeEnBD($linkExterno, $queryQuedanTasacionesInSitu, "verificar si quedan tasaciones in situ para la antiguedad"))
            {
                $this->cambiarEstadoAntiguedad($linkExterno, $tasDigitalDTO->antiguedad, TipoEstadoEnum::TasadoDigital());
            }
    }

    public function ifCambiarEstadoAntiguedadFromTDtoRD(mysqli $linkExterno, TasacionDigitalDTO $tasDigitalDTO): void
    {
        $queryQuedanTasacionesDigitales = "SELECT 1 FROM tasaciondigital
                                              WHERE tadAntId = {$tasDigitalDTO->antiguedad->antId}
                                              AND tadUsrPropId = {$tasDigitalDTO->propietario->usrId}
                                              AND tadFechaTasDigitalRealizada IS NOT NULL
                                              AND tadFechaBaja IS NULL";

        if (!Querys::existeEnBD($linkExterno, $queryQuedanTasacionesDigitales, "verificar si quedan tasaciones digitales para la antiguedad")) {
            $this->cambiarEstadoAntiguedad($linkExterno, $tasDigitalDTO->antiguedad, TipoEstadoEnum::RetiradoDisponible());
        }
    }

    public function ifCambiarEstadoAntiguedadFromRDtoTD(mysqli $linkExterno, AntiguedadDTO $antiguedadDTO): void
    {
        if($antiguedadDTO->tipoEstado->isRetiradoDisponible())
        {
            $this->cambiarEstadoAntiguedad($linkExterno, $antiguedadDTO, TipoEstadoEnum::TasadoDigital());
        }
    }

    public function ifCambiarEstadoAntiguedadFromTDtoTI(mysqli $linkExterno, TasacionInSituDTO $tasInSituDTO): void
    {
       $tasDigitalDTO = $this->getByIdInterno('TASACIONDIGITAL', TasacionDigitalDTO::class, $linkExterno, $tasInSituDTO->tadId);
       if(!isset($tasDigitalDTO) || !($tasDigitalDTO instanceof TasacionDigitalDTO))
           throw new InvalidArgumentException("No se encontró la tasación digital");

       $antiguedadDTO = $this->getByIdInterno('ANTIGUEDAD', AntiguedadDTO::class, $linkExterno, $tasDigitalDTO->antiguedad->antId);
       if(!isset($antiguedadDTO) || !($antiguedadDTO instanceof AntiguedadDTO))
           throw new InvalidArgumentException("No se encontró la antigüedad");

        if($antiguedadDTO->tipoEstado->isTasadoDigital())
        {
            $this->cambiarEstadoAntiguedad($linkExterno, $antiguedadDTO, TipoEstadoEnum::TasadoInSitu());
        }
    }

    public function calcularEstadoAntiguedad(mysqli $linkExterno, AntiguedadDTO $antiguedadDTO): TipoEstadoEnum
    {
       
       
       $queryQuedanTasacionesInSitu = "SELECT 1 FROM tasacioninsitu as ti
                                            INNER JOIN tasaciondigital as td ON ti.tisTadId = td.tadId
                                            WHERE ti.tisFechaBaja IS NULL
                                            AND ti.tisFechaTasInSituRealizada IS NOT NULL
                                            AND td.tadAntId = {$antiguedadDTO->antId}
                                            AND td.tadUsrPropId = {$antiguedadDTO->usuario->usrId}
                                            AND td.tadFechaBaja IS NULL";

        if (Querys::existeEnBD($linkExterno, $queryQuedanTasacionesInSitu, "verificar si quedan tasaciones in situ para la antiguedad")) {
            return TipoEstadoEnum::TasadoInSitu();
        }

        $queryQuedanTasacionesDigitales = "SELECT 1 FROM tasaciondigital
                                              WHERE tadAntId = {$antiguedadDTO->antId}
                                              AND tadUsrPropId = {$antiguedadDTO->usuario->usrId}
                                              AND tadFechaTasDigitalRealizada IS NOT NULL
                                              AND tadFechaBaja IS NULL";

        if (Querys::existeEnBD($linkExterno, $queryQuedanTasacionesDigitales, "verificar si quedan tasaciones digitales para la antiguedad")) {
            return TipoEstadoEnum::TasadoDigital();
        }

        return TipoEstadoEnum::RetiradoDisponible();
    }
}
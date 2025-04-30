<?php
use Utilidades\Obligatorio;

abstract class ClassBase {

    public static function getObligatorios(): array
    {
        $refClass = new ReflectionClass(get_called_class());
        $propiedades = $refClass->getProperties();
        $obligatorios = [];
        foreach ($propiedades as $propiedad) {
            if ($propiedad->getAttributes(Obligatorio::class)) {
                $obligatorios[] = $propiedad->getName();
            }
        }
        return $obligatorios;
    }
}
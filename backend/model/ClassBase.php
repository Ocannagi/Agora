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

    abstract public static function fromCreacionDTO(ICreacionDTO $dto): self;

    public static function fromArray(array $data): self
    {
        $instance = new self();
        $refClass = new ReflectionClass(__CLASS__);
        $properties = $refClass->getProperties();

        foreach ($data as $key => $value) {
            if (property_exists($instance, $key)) {
                foreach ($properties as $property) {
                    if ($property->getName() === $key) {
                        $type = $property->getType();

                        if ($value === null || $value === "") {
                            if ($type && $type->allowsNull()) {
                                $instance->$key = null;
                            } else {
                                throw new InvalidArgumentException("La propiedad {$key} no puede ser nula.");
                            }
                        } elseif ($type && $type->getName() === 'DateTime') {
                            $date = DateTime::createFromFormat('Y-m-d', $value);
                            if ($date) {
                                $instance->$key = $date;
                            } else {
                                throw new InvalidArgumentException("Formato de fecha inválido para {$key}.");
                            }
                        } elseif ($type) {
                            settype($value, $type->getName());
                            $instance->$key = $value;
                        } else {
                            $instance->$key = $value;
                        }
                    }
                }
            } else {
                $className = get_called_class();
                throw new InvalidArgumentException("La propiedad {$key} no existe en la clase $className.");
            }
        }

        return $instance;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
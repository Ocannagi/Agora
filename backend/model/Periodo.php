<?php

use Utilidades\Obligatorio;

class Periodo extends ClassBase {
    private int $perId;
    #[Obligatorio]
    private string $perDescripcion;
    private ?DateTime $perFechaBaja;
}
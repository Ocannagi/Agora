<?php

interface IBaseControllerParams
{
    public function getByParams(string $query, string $classDTO): array;
}
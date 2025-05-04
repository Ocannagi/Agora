<?php

interface IBaseController
{
    public function get(string $query, string $classDTO);
    public function getById(string $query, string $classDTO);
    public function post(string $query, mysqli $link) : never;
    public function patch(string $query, mysqli $link);
    public function delete(string $queryBusqueda, string $queryBajaLogica);
}
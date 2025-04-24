<?php

interface IBaseController
{
    public function get(string $query, string $classDTO);
    public function getConParametros(string $query, string $classDTO);
    public function post(string $query, mysqli $link) : never;
    public function patch($id);
    public function delete($id);
}
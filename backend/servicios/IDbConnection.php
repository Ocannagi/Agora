<?php
interface IDbConnection
{
    function conectarBD() : mysqli;
}
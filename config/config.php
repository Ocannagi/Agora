<?php

define ('JWT_ALG', 'HS512'); // JWT_ALG de codificación/firma
define ('JWT_KEY', 'Qrt%35_%py796KF$mdlki*Dws9&-2449Y3jnvOuhF%$kvb&Kjhfsa8548Gq&Jdlk__ÑerT%'); //String largo y "complicado"
define ('JWT_EXP', 3600); // Duración en segundos de la validez del token


function sf__restablecerSql () {
	$sqls = array_map(function ($v) {return trim($v);}, explode(PHP_EOL, file_get_contents(__DIR__ . '/../storage/bd_dump.sql')));
	$nuevoSql = implode(PHP_EOL, array_slice($sqls, array_search('-- #####CORTE#####', $sqls)));
	return $nuevoSql;
}
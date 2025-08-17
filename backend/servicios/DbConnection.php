<?php
//require_once(__DIR__ . '//persistencia.php');
use Utilidades\Output;


class DbConnection implements IDbConnection
{
    private static $instancia = null;
    private function __construct() {}

    public static function getInstancia() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    private function __clone() {}

    
    public function conectarBD() : mysqli
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBBASE);
        if ($mysqli->connect_error) {
            Output::outputError(500, "Falló la conexión: " . $mysqli->connect_error);
        }
        mysqli_set_charset($mysqli, 'utf8');
        return $mysqli;
    }
    
}



/* function postRestablecer()
{
    $db = conectarBD();
    $sql = sf__restablecerSql();
    $result = mysqli_multi_query($db, $sql);
    if ($result === false) {
        print_r(mysqli_error($db));
        outputError(500);
    }
    mysqli_close($db);
    outputJson([], 201);
}
 */

/*  function sf__restablecerSql () {
	$sqls = array_map(function ($v) {return trim($v);}, explode(PHP_EOL, file_get_contents(__DIR__ . '/../storage/bd_dump.sql')));
	$nuevoSql = implode(PHP_EOL, array_slice($sqls, array_search('-- #####CORTE#####', $sqls)));
	return $nuevoSql;
} */
<?php

define('HOST', 'host');
define('PORT', 'porta');
define('DBNAME', 'banco');
define('USER', 'indicadores_internet');
define('PASSWORD', 'indicadores_internet');

$con_string = "host=". HOST ." port=" . PORT . " dbname=" . DBNAME . " user=" . USER . " password=" . PASSWORD;
if(!$dbcon = pg_connect($con_string)) die ("Erro ao conectar ao banco<br>" . pg_last_error($dbcon));

?>

<?php
require_once 'conexaodb.php';

/*******************************************************/
//Table Setores

$filtro = "EXTRACT(YEAR FROM t.periodo) = $ano";

if ($mes <> '-')
{
  $filtro = "$filtro AND EXTRACT(MONTH FROM t.periodo) = $mes";
}
if ($dia <> '-')
{
  $filtro = "$filtro AND EXTRACT(DAY FROM t.periodo) = $dia";
}
$filtro = "$filtro AND t.fonte_id = (SELECT id FROM fonte WHERE nome = '$fonte')";

if ($setor <> 'Todos') {
  $filtro1 = "$filtro AND s.nome = '$setor'";
  $filtro2 = "$filtro AND (s.nome <> '$setor' OR s.nome IS NULL)";
}
else {
  $filtro1 = $filtro;
  $filtro2 = $filtro;
}

$result = pg_query($dbcon, "SELECT tb.setor, (tb.bytes/(1024*1024*1024))::NUMERIC(9,2) AS gigas, (tb.gasto/(1024*1024*1024))::NUMERIC(9,2) AS gasto FROM
                            (
                            	(SELECT coalesce(s.nome, 'não informado') AS setor, sum(t.bytes) AS bytes, sum(t.bytes * c.custo_por_gb)::NUMERIC AS gasto
                            	    FROM trafego t
                            	    INNER JOIN custo_trafego c ON c.id = t.custo_trafego_id
                            	    LEFT JOIN setor s ON s.id = t.setor_id
                            	    WHERE $filtro1
                            	    GROUP BY coalesce(s.nome, 'não informado')
                            	)

                            	UNION ALL

                            	(SELECT 'Todos' as setor, sum(t.bytes) as bytes, sum(t.bytes * c.custo_por_gb)::NUMERIC AS gasto
                            	    FROM trafego t
                            	    INNER JOIN custo_trafego c ON c.id = t.custo_trafego_id
                                  LEFT JOIN setor s ON s.id = t.setor_id
                            	    WHERE $filtro2
                            	)
                            ) AS tb
                            ORDER BY gasto DESC;");
if (!$result) {
  echo "Erro na consulta Table Setores.<br>";
  exit;
}
$dataTablePorSetor = $result;


/*******************************************************/
//Table Usuarios

if ($setor <> 'Todos') {
    $filtro = "$filtro AND s.nome = '$setor'";
}

$result = pg_query($dbcon, "SELECT tb.usuario, tb.setor, (tb.bytes/(1024*1024*1024))::NUMERIC(15,2) AS gigas, (tb.gasto/(1024*1024*1024))::NUMERIC(15,2) AS gasto FROM
                            (
                          		SELECT t.usuario, coalesce(s.nome, 'não informado') AS setor, sum(t.bytes) as bytes, sum(t.bytes * c.custo_por_gb) AS gasto
                          		   FROM trafego t
                          		   INNER JOIN custo_trafego c ON c.id = t.custo_trafego_id
                                 INNER JOIN fonte f ON f.id = t.fonte_id
                          		   LEFT JOIN setor s ON s.id = t.setor_id
                          		   WHERE $filtro AND fonte_id = (SELECT id FROM fonte WHERE nome = '$fonte')
                          		   GROUP BY usuario, coalesce(s.nome, 'não informado')
                            ) AS tb
                            ORDER BY gasto DESC;");

if (!$result) {
  echo "Erro na consulta Table Usuarios.<br>";
  exit;
}
$dataTableInternetPorUsuario = $result;

/*******************************************************/

pg_close($dbcon)
?>

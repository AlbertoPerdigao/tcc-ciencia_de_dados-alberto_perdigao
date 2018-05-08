<?php
require_once 'conexaodb.php';

/*******************************************************/
//PieChart Setores

$filtro = "EXTRACT(YEAR FROM t.periodo) = $ano";
$filtroSubselect = "EXTRACT(YEAR FROM t2.periodo) = $ano";

if ($mes <> '-') {
  $filtro = "$filtro AND EXTRACT(MONTH FROM t.periodo) = $mes";
  $filtroSubselect = "$filtroSubselect AND EXTRACT(MONTH FROM t2.periodo) = $mes";
}
if ($dia <> '-') {
  $filtro = "$filtro AND EXTRACT(DAY FROM t.periodo) = $dia";
  $filtroSubselect = "$filtroSubselect AND EXTRACT(DAY FROM t2.periodo) = $dia";
}
$filtro = "$filtro AND t.fonte_id = (SELECT id FROM fonte WHERE nome = '$fonte')";
$filtroSubselect = "$filtroSubselect AND t2.fonte_id = (SELECT id FROM fonte WHERE nome = '$fonte')";

if ($setor == 'Todos') {
  $result = pg_query($dbcon, "SELECT tb.setor, (tb.gasto/(1024*1024*1024))::NUMERIC(15,2) AS gasto FROM
                              (
                              	(SELECT COALESCE(s.nome, 'não informado') AS setor, (SUM(t.bytes * c.custo_por_gb))::NUMERIC AS gasto
                              	    FROM trafego t
                              	    INNER JOIN custo_trafego c ON c.id = t.custo_trafego_id
                              	    LEFT JOIN setor s ON s.id = t.setor_id
                              	    WHERE $filtro
                              	    GROUP BY COALESCE(s.nome, 'não informado')
                              	    ORDER BY gasto DESC LIMIT 5
                              	)

                              	UNION ALL

                              	(SELECT 'restante dos setores'::TEXT AS setor, COALESCE((SUM(t.bytes * c.custo_por_gb))::NUMERIC, 0.00) AS gasto
                              	    FROM trafego t
                              	    INNER JOIN custo_trafego c ON c.id = t.custo_trafego_id
                              	    LEFT JOIN setor s ON s.id = t.setor_id
                              	    WHERE $filtro
                                    AND s.nome NOT IN (SELECT COALESCE(s2.nome, 'não informado') AS setor
                                    								    FROM trafego t2
                                    								    INNER JOIN custo_trafego c2 ON c2.id = t2.custo_trafego_id
                                    								    LEFT JOIN setor s2 ON s2.id = t2.setor_id
                                    								    WHERE $filtroSubselect
                                    								    GROUP BY COALESCE(s2.nome, 'não informado')
                                    								    ORDER BY (SUM(t2.bytes * c2.custo_por_gb))::NUMERIC DESC LIMIT 5)
                              	)
                              ) AS tb
                              ORDER BY gasto DESC;");
}
else {
  $result = pg_query($dbcon, "SELECT tb.setor, (tb.gasto/(1024*1024*1024))::NUMERIC(15,2) AS gasto FROM
                              (
                              	(SELECT s.nome AS setor, (SUM(t.bytes * c.custo_por_gb))::NUMERIC AS gasto
                              	    FROM trafego t
                              	    INNER JOIN custo_trafego c ON c.id = t.custo_trafego_id
                              	    LEFT JOIN setor s ON s.id = t.setor_id
                              	    WHERE $filtro AND s.nome = '$setor'
                              	    GROUP BY s.nome
                              	)

                              	UNION ALL

                              	(SELECT 'restante dos setores'::TEXT AS setor, COALESCE((SUM(t.bytes * c.custo_por_gb))::NUMERIC, 0.00) AS gasto
                              	    FROM trafego t
                              	    INNER JOIN custo_trafego c ON c.id = t.custo_trafego_id
                              	    LEFT JOIN setor s ON s.id = t.setor_id
                              	    WHERE $filtro AND (s.nome <> '$setor' OR s.nome IS NULL)
                              	)
                              ) AS tb
                              ORDER BY gasto DESC;");
}

if (!$result) {
  echo "Erro na consulta PieChart Setores.<br>";
  exit;
}

  $dataPieChartLabelsGastoPorSetor = array();
  $dataPieChartValuesGastoPorSetor = array();
  //"Setor", "gasto"
  while ($row = pg_fetch_row($result)) {
    array_push($dataPieChartLabelsGastoPorSetor, "$row[0]");
    array_push($dataPieChartValuesGastoPorSetor, $row[1]);
  }


/*******************************************************/
//PieChart Usuarios

$filtro = "EXTRACT(YEAR FROM t.periodo) = $ano";
$filtroSubselect = "EXTRACT(YEAR FROM t2.periodo) = $ano";

if ($dia <> '-') {
  $filtro = "$filtro AND EXTRACT(DAY FROM t.periodo) = $dia";
  $filtroSubselect = "$filtroSubselect AND EXTRACT(DAY FROM t2.periodo) = $dia";
}
if ($mes <> '-') {
  $filtro = "$filtro AND EXTRACT(MONTH FROM t.periodo) = $mes";
  $filtroSubselect = "$filtroSubselect AND EXTRACT(MONTH FROM t2.periodo) = $mes";
}
if ($setor <> 'Todos') {
  $filtro = "$filtro AND s.nome = '$setor'";
  $filtroSubselect = "$filtroSubselect AND s2.nome = '$setor'";
}

$filtro = "$filtro AND t.fonte_id = (SELECT id FROM fonte WHERE nome = '$fonte')";
$filtroSubselect = "$filtroSubselect AND t2.fonte_id = (SELECT id FROM fonte WHERE nome = '$fonte')";

$result = pg_query($dbcon, "SELECT tb.usuario, (tb.gasto/(1024*1024*1024))::NUMERIC(15,2) AS gasto FROM
                            (
                            (SELECT t.usuario, (SUM(t.bytes * c.custo_por_gb))::NUMERIC AS gasto
                                FROM trafego t
                                INNER JOIN custo_trafego c ON c.id = t.custo_trafego_id
                                LEFT JOIN setor s ON s.id = t.setor_id
                                WHERE $filtro
                                GROUP BY t.usuario
                                ORDER BY gasto DESC LIMIT 5)

                            UNION ALL

                            (SELECT 'restante do setor'::TEXT AS usuario, COALESCE((SUM(t.bytes * c.custo_por_gb))::NUMERIC, 0.00) AS gasto
                                FROM trafego t
                                INNER JOIN custo_trafego c ON c.id = t.custo_trafego_id
                                LEFT JOIN setor s ON s.id = t.setor_id
                                WHERE $filtro
                                AND t.usuario NOT IN (SELECT t2.usuario
                                                        FROM trafego t2
                                                        INNER JOIN custo_trafego c2 ON c2.id = t2.custo_trafego_id
                                                        LEFT JOIN setor s2 ON s2.id = t2.setor_id
                                                        WHERE $filtroSubselect
                                                        GROUP BY t2.usuario
                                                        ORDER BY (SUM(t2.bytes * c2.custo_por_gb))::NUMERIC DESC LIMIT 5)
                            )
                            ) AS tb
                            ORDER BY gasto DESC;");

if (!$result) {
  echo "Erro na consulta PieChart Usuarios.<br>";
  exit;
}

$dataPieChartLabelsGastoPorUsuario = array();
$dataPieChartValuesGastoPorUsuario = array();

while ($row = pg_fetch_row($result)) {
  array_push($dataPieChartLabelsGastoPorUsuario, "$row[0]");
  array_push($dataPieChartValuesGastoPorUsuario, $row[1]);
}

/*******************************************************/
//Economia

$filtro = "EXTRACT(YEAR FROM e.periodo) = $ano";

if ($mes <> '-') {
  $filtro = "$filtro AND EXTRACT(MONTH FROM e.periodo) = $mes";
}
if ($dia <> '-') {
  $filtro = "$filtro AND EXTRACT(DAY FROM e.periodo) = $dia";
}

$result = pg_query($dbcon, "SELECT tb.tipo, (tb.bytes/(1024*1024*1024))::NUMERIC(9,2) AS gb, (tb.gasto/(1024*1024*1024))::NUMERIC(9,2) AS gasto FROM
                              (SELECT e.tipo, SUM(e.bytes) AS bytes, (SUM(e.bytes * c.custo_por_gb))::NUMERIC AS gasto
                                FROM economia e
                                INNER JOIN custo_trafego c ON c.id = e.custo_trafego_id
                                WHERE $filtro
                                GROUP BY e.tipo) AS tb
                            ORDER BY gasto;");

if (!$result) {
  echo "Erro na consulta PieChart Economia.<br>";
  exit;
}

$valorEconomiaAcessoBloqueado = "";
$gbEconomiaAcessoBloqueado = "";
$valorEconomiaAcessoCache = "";
$gbEconomiaAcessoCache = "";

while ($row = pg_fetch_row($result)) {
  switch ($row[0]) {
      case "acesso_bloqueado":
          $valorEconomiaAcessoBloqueado = $row[2];
          $gbEconomiaAcessoBloqueado = $row[1];
          break;
      case "acesso_cache":
          $valorEconomiaAcessoCache = $row[2];
          $gbEconomiaAcessoCache = $row[1];
          break;
  }
}


/*******************************************************/
//LineChart Historico Economia

$result = pg_query($dbcon, "SELECT tb.mes, tb.tipo, (tb.gasto/(1024*1024*1024))::NUMERIC(9,2) AS gasto FROM
                              (SELECT EXTRACT(month from e.periodo) as mes, tipo, SUM(e.bytes) AS bytes, (SUM(e.bytes * c.custo_por_gb))::NUMERIC AS gasto
                                FROM economia e
                                INNER JOIN custo_trafego c ON c.id = e.custo_trafego_id
                                WHERE EXTRACT(YEAR FROM e.periodo) = $ano
                                GROUP BY EXTRACT(month FROM e.periodo), e.tipo) AS tb
                            ORDER BY tb.mes;");

if (!$result) {
  echo "Erro na consulta PieChart Economia.<br>";
  exit;
}

$dataPieChartValuesHistoricoMensalEconomiaBloqueado = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
$dataPieChartValuesHistoricoMensalEconomiaCache = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

while ($row = pg_fetch_row($result)) {
  if (strtolower($row[1]) == "acesso_bloqueado") {
    $dataPieChartValuesHistoricoMensalEconomiaBloqueado[$row[0] - 1] = $row[2];
  }
  elseif (strtolower($row[1]) == "acesso_cache") {
    $dataPieChartValuesHistoricoMensalEconomiaCache[$row[0] - 1] = $row[2];
  }
}

/*******************************************************/
//LineChart Historico Mensal

$filtro = "EXTRACT(YEAR FROM t.periodo) = $ano";

if ($setor <> 'Todos') {
  $filtro = "$filtro AND s.nome = '$setor'";
}

$result = pg_query($dbcon, "SELECT tb.mes, tb.fonte, (tb.gasto/(1024*1024*1024))::NUMERIC(9,2) AS gasto FROM
                            (
                              SELECT EXTRACT(MONTH FROM t.periodo) as mes, f.nome AS fonte, (SUM(t.bytes * c.custo_por_gb))::NUMERIC AS gasto
                              FROM trafego t
                              INNER JOIN custo_trafego c ON t.custo_trafego_id = c.id
                              INNER JOIN fonte f ON f.id = t.fonte_id
                              LEFT JOIN setor s ON s.id = t.setor_id
                              WHERE $filtro
                              GROUP BY fonte, EXTRACT(MONTH FROM t.periodo)
                            ) AS tb
                            ORDER BY tb.mes, tb.fonte;");

if (!$result) {
  echo "Erro na consulta LineChart Historico Mensal.<br>";
  exit;
}
$dataLineChartValuesHistoricoMensalInternet = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
$dataLineChartValuesHistoricoMensalFacebook = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
$dataLineChartValuesHistoricoMensalYoutube = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
$dataLineChartValuesHistoricoMensalInstagram = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

while ($row = pg_fetch_row($result)) {

  if (strtolower($row[1]) == "internet") {
    $dataLineChartValuesHistoricoMensalInternet[$row[0] - 1] = $row[2];
  }
  elseif (strtolower($row[1]) == "facebook") {
    $dataLineChartValuesHistoricoMensalFacebook[$row[0] - 1] = $row[2];
  }
  elseif (strtolower($row[1]) == "youtube") {
    $dataLineChartValuesHistoricoMensalYoutube[$row[0] - 1] = $row[2];
  }
  elseif (strtolower($row[1]) == "instagram") {
    $dataLineChartValuesHistoricoMensalInstagram[$row[0] - 1] = $row[2];
  }
}


/*******************************************************/
//LineChart Historico Diário

$filtro = "EXTRACT(YEAR FROM t.periodo) = $ano";
$mesAtual = ($mes <> '-') ? $mes : date("m");
$filtro = "$filtro AND EXTRACT(MONTH FROM t.periodo) = $mesAtual";

if ($setor <> 'Todos') {
  $filtro = "$filtro AND s.nome = '$setor'";
}

$result = pg_query($dbcon, "SELECT tb.dia, tb.fonte, (tb.gasto/(1024*1024*1024))::NUMERIC(9,2) AS gasto FROM
                            (
                              SELECT EXTRACT(DAY FROM t.periodo) as dia, f.nome AS fonte, (SUM(t.bytes * c.custo_por_gb))::NUMERIC AS gasto
                              FROM trafego t
                              INNER JOIN custo_trafego c ON t.custo_trafego_id = c.id
                              INNER JOIN fonte f ON f.id = t.fonte_id
                              LEFT JOIN setor s ON s.id = t.setor_id
                              WHERE $filtro
                              GROUP BY fonte, EXTRACT(DAY FROM t.periodo)
                            ) AS tb
                            ORDER BY tb.dia, tb.fonte;");

if (!$result) {
  echo "Erro na consulta LineChart Histórico Diário.<br>";
  exit;
}
$dataLineChartValuesHistoricoDiarioInternet = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
$dataLineChartValuesHistoricoDiarioFacebook = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
$dataLineChartValuesHistoricoDiarioYoutube = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
$dataLineChartValuesHistoricoDiarioInstagram = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

while ($row = pg_fetch_row($result)) {

  if (strtolower($row[1]) == "internet") {
    $dataLineChartValuesHistoricoDiarioInternet[$row[0] - 1] = $row[2];
  }
  elseif (strtolower($row[1]) == "facebook") {
    $dataLineChartValuesHistoricoDiarioFacebook[$row[0] - 1] = $row[2];
  }
  elseif (strtolower($row[1]) == "youtube") {
    $dataLineChartValuesHistoricoDiarioYoutube[$row[0] - 1] = $row[2];
  }
  elseif (strtolower($row[1]) == "instagram") {
    $dataLineChartValuesHistoricoDiarioInstagram[$row[0] - 1] = $row[2];
  }
}


/*******************************************************/

pg_close($dbcon)
?>

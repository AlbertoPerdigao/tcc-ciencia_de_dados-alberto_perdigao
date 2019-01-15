<?php
require_once 'conexaodb.php';

$dia = (empty($_POST['dia'])) ? '-' : $_POST['dia'];
$mes = (empty($_POST['mes'])) ? '-' : $_POST['mes'];
$ano = (empty($_POST['ano'])) ? date("Y") : $_POST['ano'];
$setor = (empty($_POST['setor'])) ? 'Todos' : $_POST['setor'];
$fonte = (empty($_POST['fonte'])) ? 'Internet' : $_POST['fonte'];
$pagina = $_POST['pagina'];

//Custo do Tráfego
$result = pg_query($dbcon, "SELECT replace(custo_por_gb::text, '.', ','), data_inicio FROM adm_indicadores_internet.custo_trafego where data_inicio <= now() ORDER BY data_inicio DESC LIMIT 1;");

if (!$result) {
  echo "Erro na consulta de Custo.<br>";
  exit;
}
$dataTableCusto = $result;

while ($row = pg_fetch_row($dataTableCusto)) {
  $custo = $row[0];
  $data_custo = date_create($row[1]);
  $data_custo = date_format($data_custo, 'd/m/Y');
}

//Fontes
$result = pg_query($dbcon, "SELECT nome FROM fonte WHERE nome NOT IN ('Internet Economia', 'Outras Fontes') ORDER BY id;");

if (!$result) {
  echo "Erro na consulta de Fontes.<br>";
  exit;
}
$dataTableFontes = $result;

//Setores
$result = pg_query($dbcon, "SELECT nome FROM setor ORDER BY nome;");

if (!$result) {
  echo "Erro na consulta de Setores.<br>";
  exit;
}
$dataTableSetores = $result;

//Anos
$result = pg_query($dbcon, "SELECT DISTINCT EXTRACT(YEAR FROM periodo) FROM adm_indicadores_internet.trafego ORDER BY 1 DESC;");
if (!$result) {
  echo "Erro na consulta de Anos.<br>";
  exit;
}
$dataTableAnos = $result;

while ($row = pg_fetch_row($dataTableAnos)) {
  $valoresComboAno[] = $row[0];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title>Dashboard de Tráfego de Internet na PGE</title>
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
  <!-- CSS Files -->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="assets/css/light-bootstrap-dashboard.css?v=2.0.1" rel="stylesheet" />
  <!--meta http-equiv="refresh" content="30"-->
  <link href="assets/css/custom.css" rel="stylesheet" />
</head>

<body>
  <div class="wrapper">
    <form method="post" action="" id="formulario">
      <div class="sidebar" data-image="assets/img/sidebar-pge.png">
        <div class="sidebar-wrapper">
          <div class="logo">
            <a href="index.php" class="simple-text">
              Tráfego de Internet
            </a>
          </div>

          <ul class="nav">
            <li class="nav-item">
              <!--button class="btn nav-link" type="submit" name="pagina" value="dashboard">
                <i class="nc-icon nc-chart-pie-35"></i>
                  DASHBOARD
              </button-->
              <button class="btn nav-link" type="submit" name="pagina" value="dashboard">
                <i class="nc-icon nc-chart-pie-35"></i>
		            <span class="pull-right">DASHBOARD</span>
              </button>
            </li>
            <li class="nav-item">
              <!--button class="btn nav-link" type="submit" name="pagina" value="tabelas">
                <i class="nc-icon nc-notes"></i>
                  TABELAS
              </button-->
              <button class="btn nav-link" type="submit" name="pagina" value="tabelas">
                <i class="nc-icon nc-notes"></i>
		            <span class="pull-right">TABELAS</span>
              </button>
            </li>
          </ul>

          <ul class="nav">
            <li>
              <button class="btn nav-link" type="button" value="">
		            <span class="pull-left"><?php echo "Custo atual do Gigabyte:"; ?></span>
                <i class="nc-icon nc-money-coins"></i>
                <span class="pull-left"><?php echo "R$ $custo"; ?></span>
                <span class="pull-left"><?php echo "Data de início: $data_custo"; ?></span>
              </button>
            </li>
          </ul>

        </div>
        </div>
        <div class="main-panel">
            <!-- Navbar -->
          <nav class="navbar navbar-expand-lg" color-on-scroll="500">
            <div class="container-fluid ">
              <div class="col-md-0">
                <label class="form-check-label">Data:</label>
              </div>
              <div class="col-md-1">
                <select class="form-control input-sm" id="combo_dia" name="dia">
                  <option value="-">dia</option>
                  <?php
                    $valoresComboDia = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31");
                    foreach($valoresComboDia as $d) {
                      $selected = ($_POST['dia'] == $d) ? 'selected' : '';
                      echo '<option value="'.$d.'" '.$selected.'>'.$d.'</option>';
                    }
                  ?>
                </select>
              </div>
              <div class="col-md-1">
                <select class="form-control input-sm" id="combo_mes" name="mes">
                  <option value="-">mês</option>
                  <?php
                    $valoresComboMes = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");
                    foreach($valoresComboMes as $m) {
                      $selected = ($_POST['mes'] == $m) ? 'selected' : '';
                      echo '<option value="'.$m.'" '.$selected.'>'.$m.'</option>';
                    }
                  ?>
                </select>
              </div>
              <div class="col-md-1">
                <select class="form-control input-sm" id="combo_ano" name="ano">
                  <?php
                    //$valoresComboAno = array("2018", "2017");
                    foreach($valoresComboAno as $a) {
                      $selected = ($_POST['ano'] == $a) ? 'selected' : '';
                      echo '<option value="'.$a.'" '.$selected.'>'.$a.'</option>';
                    }
                  ?>
                </select>
              </div>
              <div class="col-md-0">
                <label class="form-check-label">Setor:</label>
              </div>
              <div class="col-md-3">
                <select class="form-control input-sm" id="combo_setor" name="setor">
                  <option value="Todos">Todos</option>
                  <?php
                    while ($row = pg_fetch_row($dataTableSetores)) {
                      $selected = ($_POST['setor'] == $row[0]) ? 'selected' : '';
                      echo '<option value="'.$row[0].'" '.$selected.'>'.$row[0].'</option>';
                    }
                  ?>
                </select>
              </div>
              <div class="col-md-0">
                <label class="form-check-label">Fonte:</label>
              </div>
              <div class="col-md-2">
                <select class="form-control input-sm" id="combo_fonte" name="fonte">
                  <?php
                    while ($row = pg_fetch_row($dataTableFontes)) {
                      $selected = ($_POST['fonte'] == $row[0]) ? 'selected' : '';
                      echo '<option value="'.$row[0].'" '.$selected.'>'.$row[0].'</option>';
                    }
                  ?>
                </select>
              </div>
              <div class="col-md-2">
                <button class="btn btn-default form-control" type="submit" name="pagina" value="<?php echo $_POST['pagina'] ?>">
                  <i class="nc-icon nc-zoom-split"></i>
                    CONSULTAR
                </button>
              </div>
              <div class="collapse navbar-collapse justify-content-end" id="navigation">
                  <?php //echo "Custo atual do Gigabyte: &nbsp<b>R$ $custo</b>&nbsp (início: $data_custo)"; ?>
              </div>
            </div>
          </nav>
          <div id="conteudo" class="content">
            <?php
              if ($_POST['pagina'] == "tabelas") {
                include "tabelas.php";
              }
              else
              {
                include "dashboard.php";
              }
           ?>
          </div>
        </div>
      </form>
    </div>
  </div>
</body>
<!--   Core JS Files   -->
<script src="assets/js/core/jquery.3.2.1.min.js" type="text/javascript"></script>
<script src="assets/js/core/popper.min.js" type="text/javascript"></script>
<script src="assets/js/core/bootstrap.min.js" type="text/javascript"></script>
<!--  Plugin for Switches, full documentation here: http://www.jque.re/plugins/version3/bootstrap.switch/ -->
<script src="assets/js/plugins/bootstrap-switch.js"></script>
<!--  Google Maps Plugin    -->
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY_HERE"></script>
<!--  Chartist Plugin  -->
<script src="assets/js/plugins/chartist.min.js"></script>
<!-- Control Center for Light Bootstrap Dashboard: scripts for the example pages etc -->
<script src="assets/js/light-bootstrap-dashboard.js?v=2.0.1" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
<!--script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"> </script-->
<script type="text/javascript">
/*
     var autoLoad = setInterval(function ()
                                {
                                    $('#PieChartUsoInternetPorSetor').load('index.php').fadeIn("slow");
                                    $('#PieChartUsoInternetPorUsuario').load('index.php').fadeIn("slow");
                                }, 10000); // refresh page every 10 seconds
* /
</script>

</html>

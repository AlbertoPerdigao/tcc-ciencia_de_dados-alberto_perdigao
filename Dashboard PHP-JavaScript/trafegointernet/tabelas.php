<?php
include 'dadostabelas.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-6">
        <div class="card table-plain-bg">
          <div class="card-header ">
            <h4 class="card-title">Gasto de <?php echo $fonte; ?> por Setor</h4>
            <!--p class="card-category">Custo Atual do Gigabyte: R$ <?php //echo $custo; ?></p-->
          </div>
          <div class="card-body table-full-width table-responsive">
            <table class="table table-sm table-responsive table-striped">
              <thead class="">
                <th>Setor</th>
                <th>Gigas</th>
                <th>Gasto (R$)</th>
              </thead>
              <tbody>
                <?php while ($row = pg_fetch_row($dataTablePorSetor)) {
                ?>
                <tr>
                  <td><small><?php echo $row[0]; ?></td>
                  <td><small><?php echo $row[1]; ?></td>
                  <td><small><?php echo $row[2]; ?></td>
                </tr>
                <?php }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card  table-plain-bg">
          <div class="card-header ">
            <h4 class="card-title">Gasto de <?php echo $fonte; ?> por Usuário</h4>
              <!--p class="card-category">Custo Atual do Gigabyte: R$ <?php //echo $custo; ?></p-->
          </div>
          <div class="card-body table-full-width table-responsive">
            <table class="table table-sm table-responsive table-striped">
              <thead>
                <th>Usuário</th>
                <th>Setor</th>
                <th>Gigas</th>
                <th>Gasto (R$)</th>
              </thead>
              <tbody>
                <?php while ($row = pg_fetch_row($dataTableInternetPorUsuario)) {
                ?>
                <tr>
                  <td><small><?php echo $row[0]; ?></td>
                  <td><small><?php echo $row[1]; ?></td>
                  <td><small><?php echo $row[2]; ?></td>
                  <td><small><?php echo $row[3]; ?></td>
                </tr>
              <?php }
              ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
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
<!--  Notifications Plugin    -->
<script src="assets/js/plugins/bootstrap-notify.js"></script>
<!-- Control Center for Light Bootstrap Dashboard: scripts for the example pages etc -->
<script src="assets/js/light-bootstrap-dashboard.js?v=2.0.1" type="text/javascript"></script>
<!-- Light Bootstrap Dashboard DEMO methods, don't include it in your project! -->
<!--script src="../assets/js/demo.js"></script-->
<script type="text/javascript">
</script>

</html>

<?php
include 'dadosdashboard.php';
?>

<body>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-3">
        <div class="card size-two">
          <div class="card-header">
            <h5 class="card-title">Top 5 Gasto de <?php echo $fonte; ?> por Setor</h5>
            <!--p class="card-category"><?php //echo "Custo Atual do Gigabyte: R$ $custo"; ?></p-->
          </div>
          <div class="card-body ">
            <canvas id="PieChartGastoPorSetor"></canvas>
          </div>
          <div class="card-footer ">
            <p class="card-category"><?php if ($setor <> 'Todos') { echo "Setor: $setor"; } else { echo "Setor: Todos"; }; ?></p>
            <p class="card-category"><?php echo "Data: $dia/$mes/$ano" ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card size-two">
          <div class="card-header ">
            <h5 class="card-title">Top 5 Gasto de <?php echo $fonte; ?> por Usuário</h5>
            <!--p class="card-category"><?php //echo "Custo Atual do Gigabyte: R$ $custo"; ?></p-->
          </div>
          <div class="card-body ">
            <canvas id="PieChartGastoPorUsuario"></canvas>
          </div>
          <div class="card-footer ">
            <p class="card-category"><?php if ($setor <> 'Todos') { echo "Setor: $setor"; } else { echo "Setor: Todos"; }; ?></p>
            <p class="card-category"><?php echo "Data: $dia/$mes/$ano" ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card size-one">
          <div class="card-header">
            <h5 class="card-title">Economia - Acesso Bloqueado</h5>
            <p class="card-category"><?php echo "$gbEconomiaAcessoBloqueado GB"; ?></p>
          </div>
          <div class="card-body">
            <h3 class="economy-value"><?php echo "R$ $valorEconomiaAcessoBloqueado"; ?></h3>
          </div>
          <div class="card-footer ">
            <p class="card-category">Todos os Setores</p>
            <p class="card-category"><?php echo "Data: $dia/$mes/$ano"; ?></p>
          </div>
        </div>
        <div class="card size-one">
          <div class="card-header">
            <h5 class="card-title">Economia - Acesso ao Cache</h5>
            <p class="card-category"><?php echo "$gbEconomiaAcessoCache GB"; ?></p>
          </div>
          <div class="card-body ">
            <h3 class="economy-value"><?php echo "R$ $valorEconomiaAcessoCache"; ?></h3>
          </div>
          <div class="card-footer ">
            <p class="card-category">Todos os Setores</p>
            <p class="card-category"><?php echo "Data: $dia/$mes/$ano"; ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card size-three">
          <div class="card-header ">
            <h5 class="card-title">Histórico Mensal de Economia</h5>
            <p class="card-category">Todos os Setores</p>
            <p class="card-category"><?php echo "Ano: $ano"; ?></p>
          </div>
          <div class="card-body ">
            <canvas id="LineChartHistoricoEconomiaMensal"></canvas>
          </div>
          <!--div class="card-footer ">
          </div-->
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="card size-four">
          <div class="card-header ">
            <h5 class="card-title">Histórico Mensal de Gastos</h5>
            <p class="card-category"><?php echo "Setor: $setor"; ?></p>
            <p class="card-category"><?php echo "Ano: $ano"; ?></p>
          </div>
          <div class="card-body ">
            <canvas id="LineChartHistoricoMensal"></canvas>
          </div>
          <!--div class="card-footer ">
          </div-->
        </div>
      </div>
      <div class="col-md-6">
        <div class="card size-four">
          <div class="card-header ">
            <h5 class="card-title">Histórico Diário de Gastos</h5>
            <p class="card-category"><?php echo "Setor: $setor"; ?></p>
            <p class="card-category"><?php echo "Mês/Ano: $mesAtual/$ano"; ?></p>
          </div>
          <div class="card-body ">
            <canvas id="LineChartHistoricoDiario"></canvas>
          </div>
          <!--div class="card-footer ">
          </div-->
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
<!-- Control Center for Light Bootstrap Dashboard: scripts for the example pages etc -->
<script src="assets/js/light-bootstrap-dashboard.js?v=2.0.1" type="text/javascript"></script>
<!-- Light Bootstrap Dashboard DEMO methods, don't include it in your project! -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
<script type="text/javascript">

  pieChartGastoPorSetor();
  pieChartGastoPorUsuario();
  lineChartHistoricoEconomiaMensal();
  lineChartHistoricoMensal();
  lineChartHistoricoDiario();

  function pieChartGastoPorSetor()
  {
    var ctx = document.getElementById('PieChartGastoPorSetor').getContext('2d');
    var pieChart = new Chart(ctx,{
      type: 'doughnut',
      data: {
            labels: <?php echo json_encode($dataPieChartLabelsGastoPorSetor, JSON_NUMERIC_CHECK); ?>,
            datasets: [{
                data: <?php echo json_encode($dataPieChartValuesGastoPorSetor, JSON_NUMERIC_CHECK); ?>,
                backgroundColor: ["#3e95cd", "#8e5ea2","#3cba9f","#e8c3b9","#c45850"]
            }]
      },
      options: {legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                                  generateLabels: function(chart) {
                                      var data = chart.data;
                                      if (data.labels.length && data.datasets.length) {
                                        chart.legend.afterFit = function () {
                                                                  var width = this.width; // guess you can play with this value to achieve needed layout
                                                                  this.lineWidths = this.lineWidths.map(function(){return width;});
                                                                };
                                        return data.labels.map(function(label, i) {
                                            var meta = chart.getDatasetMeta(0);
                                            var ds = data.datasets[0];
                                            var arc = meta.data[i];
                                            var custom = arc && arc.custom || {};
                                            var getValueAtIndexOrDefault = Chart.helpers.getValueAtIndexOrDefault;
                                            var arcOpts = chart.options.elements.arc;
                                            var fill = custom.backgroundColor ? custom.backgroundColor : getValueAtIndexOrDefault(ds.backgroundColor, i, arcOpts.backgroundColor);
                                            var stroke = custom.borderColor ? custom.borderColor : getValueAtIndexOrDefault(ds.borderColor, i, arcOpts.borderColor);
                                            var bw = custom.borderWidth ? custom.borderWidth : getValueAtIndexOrDefault(ds.borderWidth, i, arcOpts.borderWidth);

                							          var value = chart.config.data.datasets[arc._datasetIndex].data[arc._index];

                                        return {
                                            text: label + " : R$ " + value,
                                            fillStyle: fill,
                                            strokeStyle: stroke,
                                            lineWidth: bw,
                                            hidden: isNaN(ds.data[i]) || meta.data[i].hidden,
                                            index: i
                                        };
                                    });
                                      }
                                      else {
                                        return [];
                                      }
                                    }
                              }
                        }
      }
    });
  }

  function pieChartGastoPorUsuario()
  {
    var ctx = document.getElementById('PieChartGastoPorUsuario').getContext('2d');
    var pieChart = new Chart(ctx,{
      type: 'doughnut',
      data: {
            labels: <?php echo json_encode($dataPieChartLabelsGastoPorUsuario, JSON_NUMERIC_CHECK); ?>,
            datasets: [{
                data: <?php echo json_encode($dataPieChartValuesGastoPorUsuario, JSON_NUMERIC_CHECK); ?>,
                backgroundColor: ["#3e95cd", "#8e5ea2","#3cba9f","#e8c3b9","#c45850"]
            }]
      },
      options: {legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                                  generateLabels: function(chart) {
                                      var data = chart.data;
                                      if (data.labels.length && data.datasets.length) {
                                        chart.legend.afterFit = function () {
                                                                  var width = this.width; // guess you can play with this value to achieve needed layout
                                                                  this.lineWidths = this.lineWidths.map(function(){return width;});
                                                                };
                                        return data.labels.map(function(label, i) {
                                            var meta = chart.getDatasetMeta(0);
                                            var ds = data.datasets[0];
                                            var arc = meta.data[i];
                                            var custom = arc && arc.custom || {};
                                            var getValueAtIndexOrDefault = Chart.helpers.getValueAtIndexOrDefault;
                                            var arcOpts = chart.options.elements.arc;
                                            var fill = custom.backgroundColor ? custom.backgroundColor : getValueAtIndexOrDefault(ds.backgroundColor, i, arcOpts.backgroundColor);
                                            var stroke = custom.borderColor ? custom.borderColor : getValueAtIndexOrDefault(ds.borderColor, i, arcOpts.borderColor);
                                            var bw = custom.borderWidth ? custom.borderWidth : getValueAtIndexOrDefault(ds.borderWidth, i, arcOpts.borderWidth);

                							              var value = chart.config.data.datasets[arc._datasetIndex].data[arc._index];

                                            return {
                                                text: label + " : R$ " + value,
                                                fillStyle: fill,
                                                strokeStyle: stroke,
                                                lineWidth: bw,
                                                hidden: isNaN(ds.data[i]) || meta.data[i].hidden,
                                                index: i
                                            };
                                        });
                                    }
                                    else {
                                      return [];
                                    }
                                  }
                        }
                }
      }
    });
  }

  function lineChartHistoricoEconomiaMensal()
  {
    var ctx = document.getElementById('LineChartHistoricoEconomiaMensal').getContext('2d');
    var chart = new Chart(ctx, {
    // The type of chart we want to create
      type: 'line',
      // The data for our dataset
      data: {
          labels: ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"],
          datasets: [{
              label: "Acesso Bloqueado",
              borderColor: "#3e95cd",
              //backgroundColor: "#3e95cd",
              fill: false,
              data: <?php echo json_encode($dataPieChartValuesHistoricoMensalEconomiaBloqueado, JSON_NUMERIC_CHECK); ?>,
          },
          {
              label: "Acesso Cache",
              borderColor: "#8e5ea2",
              fill: false,
              data: <?php echo json_encode($dataPieChartValuesHistoricoMensalEconomiaCache, JSON_NUMERIC_CHECK); ?>,
          }]
      },
      // Configuration options go here
      options: {
        scales: {
                  yAxes: [{
                    scaleLabel: {
                      display: true,
                      labelString: 'Economia em R$'
                    }
                  }],
                  xAxes: [{
                    scaleLabel: {
                      display: true,
                      labelString: 'Meses'
                    }
                  }]
                }
      }
      });
  }

  function lineChartHistoricoMensal()
  {
    var ctx = document.getElementById('LineChartHistoricoMensal').getContext('2d');
    var chart = new Chart(ctx, {
    // The type of chart we want to create
      type: 'line',
      // The data for our dataset
      data: {
          labels: ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"],
          datasets: [{
              label: "Internet",
              borderColor: "#3e95cd",
              //backgroundColor: "#3e95cd",
              fill: false,
              data: <?php echo json_encode($dataLineChartValuesHistoricoMensalInternet, JSON_NUMERIC_CHECK); ?>,
          },
          {
              label: "Facebook",
              borderColor: "#8e5ea2",
              fill: false,
              data: <?php echo json_encode($dataLineChartValuesHistoricoMensalFacebook, JSON_NUMERIC_CHECK); ?>,
          },
          {
              label: "Youtube",
              borderColor: "#3cba9f",
              fill: false,
              data: <?php echo json_encode($dataLineChartValuesHistoricoMensalYoutube, JSON_NUMERIC_CHECK); ?>,
          },
          {
              label: "Instagram",
              borderColor: "#e8c3b9",
              fill: false,
              data: <?php echo json_encode($dataLineChartValuesHistoricoMensalInstagram, JSON_NUMERIC_CHECK); ?>,
          }]
      },
      // Configuration options go here
      options: {
        scales: {
                  yAxes: [{
                    scaleLabel: {
                      display: true,
                      labelString: 'Gasto em R$'
                    }
                  }],
                  xAxes: [{
                    scaleLabel: {
                      display: true,
                      labelString: 'Meses'
                    }
                  }]
                }
      }
      });
  }

  function lineChartHistoricoDiario()
  {
    var ctx = document.getElementById('LineChartHistoricoDiario').getContext('2d');
    var chart = new Chart(ctx, {
    // The type of chart we want to create
      type: 'line',
      // The data for our dataset
      data: {
          labels: ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31"],
          datasets: [{
              label: "Internet",
              borderColor: "#3e95cd",
              //backgroundColor: "#3e95cd",
              fill: false,
              data: <?php echo json_encode($dataLineChartValuesHistoricoDiarioInternet, JSON_NUMERIC_CHECK); ?>,
          },
          {
              label: "Facebook",
              borderColor: "#8e5ea2",
              fill: false,
              data: <?php echo json_encode($dataLineChartValuesHistoricoDiarioFacebook, JSON_NUMERIC_CHECK); ?>,
          },
          {
              label: "Youtube",
              borderColor: "#3cba9f",
              fill: false,
              data: <?php echo json_encode($dataLineChartValuesHistoricoDiarioYoutube, JSON_NUMERIC_CHECK); ?>,
          },
          {
              label: "Instagram",
              borderColor: "#e8c3b9",
              fill: false,
              data: <?php echo json_encode($dataLineChartValuesHistoricoDiarioInstagram, JSON_NUMERIC_CHECK); ?>,
          }]
      },
      // Configuration options go here
      options: {
        scales: {
                  yAxes: [{
                    scaleLabel: {
                      display: true,
                      labelString: 'Gasto em R$'
                    }
                  }],
                  xAxes: [{
                    scaleLabel: {
                      display: true,
                      labelString: 'Dias'
                    }
                  }]
                }
      }
      });
  }

</script>

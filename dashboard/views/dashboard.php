<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>3D Visualiser</title>

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
  <link rel="stylesheet" href="../css/datepicker.css">

	<!-- Latest compiled and minified JavaScript -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->

  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">KPI Visualizer</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="#">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <br />
    <br />
    <br />
    <div class="container">

    <div class="row">
      <div class="col-md-12" id="datapointsCount">
        <center><h3>Analyzing 0 datapoints</h3></center>
      </div>
    </div>


		<form action="process" method="POST" role="form" class="dataForm">
		  <legend>Data Selector</legend>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="">Start Date</label>
            <input type="text" name="startDate" id="inputStartDate" class="form-control datepicker" value="<?php echo date('Y-m-d'); ?>" required="required" title="" data-date-format="yyyy-mm-dd">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="">Period(in Days)</label>
            <select name="period" id="inputPeriod" class="form-control" required="required">
              <option value="1">1 Day</option>
              <option value="7">1 Week</option>
              <option value="30">1 Month</option>
              <option value="90">3 Months</option>
              <option value="180">6 Months</option>
              <option value="365">1 Year</option>
            </select>
          </div>
        </div>
      </div>



      <div class="row">
        <div class="col-md-9">


        <!-- Begin xAxis Row !-->

        <div class="panel panel-default">
          <div class="panel-body">

            <div class="row">
              <div class="col-md-6">
                  <div class="row">
                    <div class="col-xs-2">
                      <label for="">xAxis</label>
                    </div>
                    <div class="col-xs-10">
                      <select name="xAxis" id="xAxis" class="form-control xparam" data-width="100%">
                        <option value=""></option>
                        <?php
                          foreach($kpis as $kpi)
                          {
                            echo '<option value="'.$kpi['id'].'">'.$kpi['name'].'</option>';
                          }
                        ?>
                      </select>
                    </div>
                </div>
              </div> <!-- End XKPI Selector col !-->

              <div class="col-md-6">
                  <div class="row">
                    <div class="col-md-4">
                      <label for="">Scaling Factor</label>
                    </div>
                    <div class="col-md-8">
                      <input type="text" name="xAxisScalingFactor" id="inputXAxisScalingFactor" class="form-control xparam"  required="required" title="" placeholder="1.000" value="1.0">
                    </div>
                  </div>
              </div> <!-- End Scaling col !-->

              <br />
              <br />
              <br />

              <div class="col-md-5 col-md-offset-1">
                <div class="form-group form-inline">
                  <label for="">X Aggregation</label>
                  <select name="xAggregation" id="xAggregation" class="form-control xparam">
					<option value=""></option>
                    <option value="min">Min</option>
                    <option value="max">Max</option>
                    <option value="avg">Avg</option>
                    <option value="sum">Sum</option>
                  </select>
                </div>  
              </div> <!-- End Aggregation !-->
              <div class="col-md-2" id="xMin">
                Min: 
              </div>
              <div class="col-md-2" id="xMax">
                Max: 
              </div>
              <div class="col-md-2" id="xAvg">
                Avg: 
              </div>

            </div> <!-- End Row !-->
          </div> <!-- End Panel Content !-->
        </div> <!-- End Panel !-->

        <!-- End xAxis Row !-->



        <!-- Begin yAxis Row !-->

        <div class="panel panel-default">
          <div class="panel-body">

            <div class="row">
              <div class="col-md-6">
                  <div class="row">
                    <div class="col-xs-2">
                      <label for="">yAxis</label>
                    </div>
                    <div class="col-xs-10">
                      <select name="yAxis" id="yAxis" class="form-control yparam" data-width="100%">
                        <option value=""></option>
                        <?php
                          foreach($kpis as $kpi)
                          {
                            echo '<option value="'.$kpi['id'].'">'.$kpi['name'].'</option>';
                          }
                        ?>
                      </select>
                    </div>
                </div>
              </div> <!-- End YKPI Selector col !-->

              <div class="col-md-6">
                  <div class="row">
                    <div class="col-md-4">
                      <label for="">Scaling Factor</label>
                    </div>
                    <div class="col-md-8">
                      <input type="text" name="yAxisScalingFactor" id="inputYAxisScalingFactor" class="form-control yparam"  required="required" title="" placeholder="1.000" value="1.0">
                    </div>
                  </div>
              </div> <!-- End Scaling col !-->

              <br />
              <br />
              <br />

              <div class="col-md-5 col-md-offset-1">
                <div class="form-group form-inline">
                  <label for="">Y Aggregation</label>
                  <select name="yAggregation" id="yAggregation" class="form-control yparam">
				    <option value=""></option>
                    <option value="min">Min</option>
                    <option value="max">Max</option>
                    <option value="avg">Avg</option>
                    <option value="sum">Sum</option>
                  </select>
                </div>  
              </div> <!-- End Aggregation !-->
              <div class="col-md-2" id="yMin">
                Min: 
              </div>
              <div class="col-md-2" id="yMax">
                Max: 
              </div>
              <div class="col-md-2" id="yAvg">
                Avg: 
              </div>

            </div> <!-- End Row !-->
          </div> <!-- End Panel Content !-->
        </div> <!-- End Panel !-->



        <!-- Begin zAxis Row !-->

        <div class="panel panel-default">
          <div class="panel-body">

            <div class="row">
              <div class="col-md-6">
                  <div class="row">
                    <div class="col-xs-2">
                      <label for="">zAxis</label>
                    </div>
                    <div class="col-xs-10">
                      <select name="zAxis" id="zAxis" class="form-control zparam" data-width="100%">
                        <option value=""></option>
                        <?php
                          foreach($kpis as $kpi)
                          {
                            echo '<option value="'.$kpi['id'].'">'.$kpi['name'].'</option>';
                          }
                        ?>
                      </select>
                    </div>
                </div>
              </div> <!-- End YKPI Selector col !-->

              <div class="col-md-6">
                  <div class="row">
                    <div class="col-md-4">
                      <label for="">Scaling Factor</label>
                    </div>
                    <div class="col-md-8">
                      <input type="text" name="zAxisScalingFactor" id="inputZAxisScalingFactor" class="form-control zparam"  required="required" title="" placeholder="1.000" value="1.0">
                    </div>
                  </div>
              </div> <!-- End Scaling col !-->

              <br />
              <br />
              <br />

              <div class="col-md-5 col-md-offset-1">
                <div class="form-group form-inline">
                  <label for="">Z Aggregation</label>
                  <select name="zAggregation" id="zAggregation" class="form-control zparam">
				    <option value=""></option>
                    <option value="min">Min</option>
                    <option value="max">Max</option>
                    <option value="avg">Avg</option>
                    <option value="sum">Sum</option>
                  </select>
                </div>  
              </div> <!-- End Aggregation !-->
              <div class="col-md-2" id="zMin">
                Min: 
              </div>
              <div class="col-md-2" id="zMax">
                Max: 
              </div>
              <div class="col-md-2" id="zAvg">
                Avg: 
              </div>

            </div> <!-- End Row !-->
          </div> <!-- End Panel Content !-->
        </div> <!-- End Panel !-->




        </div> <!-- End Parameters Col !-->





        <div class="col-md-3">
            <label for="">Sites</label>
            <select multiple name="sites[]" id="sites[]" class="form-control sites" style="height:440px;">
            <option value="all">All Sites</option>
            <?php
              foreach($sites as $site)
              {
                echo '<option value="'.$site['id'].'">'.$site['id'].'</option>';
              }
            ?>
            </select>
        </div>
      </div>

    <select name="graphType" id="inputGraphType" class="form-control">
      <option value="">-- Select One --</option>
      <option value="immersive">Immersive</option>
      <option value="statisical">Statistical</option>
      <option value="boxplot">Box Plot</option>
    </select>
		
    <div class="checkbox">
      <label>
        <input type="checkbox" name="useVR" id="useVR" value="1">
        Use Oculus Rift
      </label>
    </div>

		
		<button type="submit" class="btn btn-primary">View Visualization</button>&nbsp
		<button type="reset" class="btn btn-primary">Reset</button>&nbsp
		
		<button type='button' id="save" class="btn btn-primary">Save Configuration</button>&nbsp
		<button type='button' id="load" class="btn btn-primary">Load Configuration</button>&nbsp
		<label for="">Select Configuration</label>
		<select name="configuration" id="configuration" width="100">
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
			<option value="5">5</option>
			<option value="6">6</option>
			<option value="7">7</option>
			<option value="8">8</option>
			<option value="9">9</option>
			<option value="10">10</option>
        </select>
		</form>
    </div><!-- /.container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="../js/bootstrap-datepicker.js"></script>
  </body>
</html>

<script>
$(document).ready(function() {
	$("#save").on("click", function() {
		var ajaxRequest = new XMLHttpRequest();
		var configuration = document.getElementById('configuration').value;
		var inputStartDate = document.getElementById('inputStartDate').value;
		var inputPeriod = document.getElementById('inputPeriod').value;
		var xAxis = document.getElementById('xAxis').value;
		var yAxis = document.getElementById('yAxis').value;
		var zAxis = document.getElementById('zAxis').value;
		var xAggregation = document.getElementById('xAggregation').value;
		var yAggregation = document.getElementById('yAggregation').value;
		var zAggregation = document.getElementById('zAggregation').value;
		var inputXAxisScalingFactor = document.getElementById('inputXAxisScalingFactor').value;
		var inputYAxisScalingFactor = document.getElementById('inputYAxisScalingFactor').value;
		var inputZAxisScalingFactor = document.getElementById('inputZAxisScalingFactor').value;
		var inputGraphType = document.getElementById('inputGraphType').value;
		var formData = "?c="+encodeURIComponent(configuration)
		+ "&sd="+encodeURIComponent(inputStartDate)
		+ "&ip="+encodeURIComponent(inputPeriod)
		+ "&x="+encodeURIComponent(xAxis)
		+ "&y="+encodeURIComponent(yAxis)
		+ "&z="+encodeURIComponent(zAxis)
		+ "&xA="+encodeURIComponent(xAggregation)
		+ "&yA="+encodeURIComponent(yAggregation)
		+ "&zA="+encodeURIComponent(zAggregation)
		+ "&xSF="+encodeURIComponent(inputXAxisScalingFactor) 
		+ "&ySF="+encodeURIComponent(inputYAxisScalingFactor)
		+ "&zSF="+encodeURIComponent(inputZAxisScalingFactor)
		+ "&iGT="+encodeURIComponent(inputGraphType);
		ajaxRequest.open("GET", "../save.php" + formData, true);
		ajaxRequest.send(null); 
	//}
	});
});

$(document).ready(function() {
	$("#load").on("click", function(){
		var ajaxRequest = new XMLHttpRequest();
		var configuration = document.getElementById('configuration').value;
		configuration = "?c="+configuration;
		ajaxRequest.open("GET", "../load.php"+ configuration, true);
		ajaxRequest.send(null);
		
		ajaxRequest.onreadystatechange = function(){
			if(ajaxRequest.readyState == 4){
				//var ajaxDisplay = document.getElementById('ajaxDiv');
				//ajaxDisplay.innerHTML = ajaxRequest.responseText;
				var data = ajaxRequest.responseText;
				var dataByLines = data.split("\n");
				$(inputStartDate).val(dataByLines[0]);
				$(inputPeriod).val(dataByLines[1].replace( /\s/g, ""));
				$(xAxis).val(dataByLines[2].replace( /\s/g, ""));
				$(yAxis).val(dataByLines[3].replace( /\s/g, ""));
				$(zAxis).val(dataByLines[4].replace( /\s/g, ""));
				$(xAggregation).val(dataByLines[5].replace( /\s/g, ""));
				$(yAggregation).val(dataByLines[6].replace( /\s/g, ""));
				$(zAggregation).val(dataByLines[7].replace( /\s/g, ""));
				$(inputXAxisScalingFactor).val(dataByLines[8]);
				$(inputYAxisScalingFactor).val(dataByLines[9]);
				$(inputZAxisScalingFactor).val(dataByLines[10]);
				$(inputGraphType).val(dataByLines[11].replace( /\s/g, ""));
			}
		}
	});
});

jQuery(document).ready(function(){
  $('.datepicker').datepicker()
}); 

$(".dataForm").change(function(){

console.log($( ".dataForm" ).serialize());

$.post( "api/numberOfDataPoints", $( ".dataForm" ).serialize())
  .done(function( data ) {
    console.log( "Data Loaded: " + data );
    var jsonData = jQuery.parseJSON( data );
    $("#datapointsCount").html("<center><h3>Analyzing "+jsonData.dataPoints+" datapoints</h3></center>")
  });

});

$(".xparam, .sites").change(function(){
  var dataString = $(".sites").serialize() +"&"+ $("#inputStartDate").serialize() + "&" + $("#inputPeriod").serialize();
  dataString += "&axis=" + $("#xAxis").val() + "&scalingFactor=" + $("#inputXAxisScalingFactor").val() + "&aggregation=" +  $("#xAggregation").val();

  console.log(dataString);
  $.get("api/minAndMaxValues", dataString)
  .done(function(data) {
    var data = jQuery.parseJSON( data );
    $("#xMin").html("Min: "+data.minimum);
    $("#xMax").html("Max: "+data.maximum);
    $("#xAvg").html("Avg: "+data.average);
  });
});

$(".yparam, .sites").change(function(){
  var dataString = $(".sites").serialize() +"&"+ $("#inputStartDate").serialize() + "&" + $("#inputPeriod").serialize();
  dataString += "&axis=" + $("#yAxis").val() + "&scalingFactor=" + $("#inputYAxisScalingFactor").val() + "&aggregation=" +  $("#yAggregation").val();

  console.log(dataString);
  $.get("api/minAndMaxValues", dataString)
  .done(function(data) {
    var data = jQuery.parseJSON( data );
    $("#yMin").html("Min: "+data.minimum);
    $("#yMax").html("Max: "+data.maximum);
    $("#yAvg").html("Avg: "+data.average);
  });
});

$(".zparam, .sites").change(function(){
  var dataString = $(".sites").serialize() +"&"+ $("#inputStartDate").serialize() + "&" + $("#inputPeriod").serialize();
  dataString += "&axis=" + $("#zAxis").val() + "&scalingFactor=" + $("#inputZAxisScalingFactor").val() + "&aggregation=" +  $("#zAggregation").val();

  console.log(dataString);
  $.get("api/minAndMaxValues", dataString)
  .done(function(data) {
    var data = jQuery.parseJSON( data );
    $("#zMin").html("Min: "+data.minimum);
    $("#zMax").html("Max: "+data.maximum);
    $("#zAvg").html("Avg: "+data.average);
  });
});

</script>

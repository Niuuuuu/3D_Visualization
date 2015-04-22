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
  <link rel="stylesheet" href="css/datepicker.css">

	<!-- Latest compiled and minified JavaScript -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<?php

include('config.php');

$pdo = new PDO("mysql:host=$dbhost;port=$dbport;dbname=$dbname",$dbuser,$dbpass);

function getSites()
{
	global $pdo;
	$sql = "SELECT id from sites
		ORDER BY id
		LIMIT 1000";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$data = array();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		$data[] = $row['id'];
	}

	return $data;
}

function getKPIs()
{
  global $pdo;
  $sql = "SELECT id, name from kpis
    ORDER BY name";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $data = array();
  while($row = $stmt->fetch(PDO::FETCH_ASSOC))
  {
    $data[$row['id']] = $row['name'];
  }

  return $data;
}

?>


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
          <a class="navbar-brand" href="#">Project name</a>
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


		<form action="formaction.php" method="POST" role="form" class="dataForm">
		  <legend>Data Selector</legend>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="">Start Date</label>
            <input type="text" name="startDate" id="inputStartDate" class="form-control datepicker" value="" required="required" title="" data-date-format="yyyy-mm-dd">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="">Period</label>
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
                      <select name="xAxis" id="xAxis" class="form-control" data-width="100%">
                        <option value=""></option>
                        <?php
                          $data = getKPIs();
                          foreach($data as $key => $value)
                          {
                            echo '<option value="'.$key.'">'.$value.'</option>';
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
                      <input type="text" name="xAxisScalingFactor" id="inputXAxisScalingFactor" class="form-control"  required="required" pattern="" title="" placeholder="1.000">
                    </div>
                  </div>
              </div> <!-- End Scaling col !-->

              <br />
              <br />
              <br />

              <div class="col-md-11 col-md-offset-1">
                <div class="form-group form-inline">
                  <label for="">X Aggregation</label>
                  <select name="xAggregation" id="xAggregation" class="form-control">
                    <option value="min">Min</option>
                    <option value="max">Max</option>
                    <option value="mean">Mean</option>
                    <option value="median">Median</option>
                  </select>
                </div>  
              </div> <!-- End Aggregation !-->

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
                      <select name="yAxis" id="yAxis" class="form-control" data-width="100%">
                        <option value=""></option>
                        <?php
                          $data = getKPIs();
                          foreach($data as $key => $value)
                          {
                            echo '<option value="'.$key.'">'.$value.'</option>';
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
                      <input type="text" name="yAxisScalingFactor" id="inputYAxisScalingFactor" class="form-control"  required="required" pattern="" title="" placeholder="1.000">
                    </div>
                  </div>
              </div> <!-- End Scaling col !-->

              <br />
              <br />
              <br />

              <div class="col-md-11 col-md-offset-1">
                <div class="form-group form-inline">
                  <label for="">Y Aggregation</label>
                  <select name="yAggregation" id="yAggregation" class="form-control">
                    <option value="min">Min</option>
                    <option value="max">Max</option>
                    <option value="mean">Mean</option>
                    <option value="median">Median</option>
                  </select>
                </div>  
              </div> <!-- End Aggregation !-->

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
                      <select name="zAxis" id="zAxis" class="form-control" data-width="100%">
                        <option value=""></option>
                        <?php
                          $data = getKPIs();
                          foreach($data as $key => $value)
                          {
                            echo '<option value="'.$key.'">'.$value.'</option>';
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
                      <input type="text" name="zAxisScalingFactor" id="inputZAxisScalingFactor" class="form-control"  required="required" pattern="" title="" placeholder="1.000">
                    </div>
                  </div>
              </div> <!-- End Scaling col !-->

              <br />
              <br />
              <br />

              <div class="col-md-11 col-md-offset-1">
                <div class="form-group form-inline">
                  <label for="">Z Aggregation</label>
                  <select name="zAggregation" id="zAggregation" class="form-control">
                    <option value="min">Min</option>
                    <option value="max">Max</option>
                    <option value="mean">Mean</option>
                    <option value="median">Median</option>
                  </select>
                </div>  
              </div> <!-- End Aggregation !-->

            </div> <!-- End Row !-->
          </div> <!-- End Panel Content !-->
        </div> <!-- End Panel !-->








        </div> <!-- End Parameters Col !-->





        <div class="col-md-3">
            <label for="">Sites</label>
            <select multiple name="sites[]" id="sites[]" class="form-control" style="height:440px;">
            <option value="all">All Sites</option>
            <?php
              $data = getSites();
              foreach($data as $entry)
              {
                echo '<option value="'.$entry.'">'.$entry.'</option>';
              }
            ?>
            </select>
        </div>
      </div>
		

		
		<button type="submit" class="btn btn-primary">Submit</button>
		</form>

    </div><!-- /.container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="js/bootstrap-datepicker.js"></script>
  </body>
</html>

<script>
jQuery(document).ready(function(){
  $('.datepicker').datepicker()
}); 

$(".dataForm").change(function(){

console.log($( ".dataForm" ).serialize());

$.post( "api/numberOfDataPoints.php", $( ".dataForm" ).serialize())
  .done(function( data ) {
    console.log( "Data Loaded: " + data );
    var jsonData = jQuery.parseJSON( data );
    $("#datapointsCount").html("<center><h3>Analyzing "+jsonData.dataPoints+" datapoints</h3></center>")
  });

});
</script>

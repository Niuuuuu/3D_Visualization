<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\DelegatingEngine;
use Symfony\Bridge\Twig\TwigEngine;


$app = new Silex\Application();
$app['debug'] = true;

//Regiser TWIG for rendering templates
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

// Build the templating service

$app['templating.engines'] = $app->share(function() {
    return array(
        'twig',
        'php'
    );
});

$app['templating.loader'] = $app->share(function() {
    return new FilesystemLoader(__DIR__.'/../views/%name%');
});

$app['templating.template_name_parser'] = $app->share(function() {
    return new TemplateNameParser();
});

$app['templating.engine.php'] = $app->share(function() use ($app) {
    return new PhpEngine($app['templating.template_name_parser'], $app['templating.loader']);
});

$app['templating.engine.twig'] = $app->share(function() use ($app) {
    return new TwigEngine($app['twig'], $app['templating.template_name_parser']);
});

$app['templating'] = $app->share(function() use ($app) {
    $engines = array();

    foreach ($app['templating.engines'] as $i => $engine) {
        if (is_string($engine)) {
            $engines[$i] = $app[sprintf('templating.engine.%s', $engine)];
        }
    }

    return new DelegatingEngine($engines);
});


function getKPIName($kpiID) {
	global $app;
	$kpis = $app['db']->fetchAll("SELECT id, name from kpis WHERE id = $kpiID");
	return $kpis[0]['name'];
}

//Register doctrine for DB access
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_mysql',
        'dbname'	=> 'Nameless',
        'host' 		=> '127.0.0.1',
        'user' 		=> 'root',
        'password'	=> 'root',
        'port'		=>  8889
    ),
));


//Frontend Requests
$app->get('/', function() use ($app) {

	$kpis = $app['db']->fetchAll("SELECT id, name from kpis ORDER BY name");
	$sites = $app['db']->fetchAll("SELECT id from sites ORDER BY id");
    return $app['templating']->render('dashboard.php', array('kpis' => $kpis, 'sites' => $sites));

});

$app->post('/process', function(Request $request) use ($app) {


	$data = http_build_query(array(
		'xAxis' => $request->get('xAxis'), 
		'yAxis' => $request->get('yAxis'),
		'zAxis' => $request->get('zAxis'),
		'xAggregation' => $request->get('xAggregation'), 
		'yAggregation' => $request->get('yAggregation'),
		'zAggregation' => $request->get('zAggregation'),
		'xAxisScalingFactor' => $request->get('xAxisScalingFactor'),
		'yAxisScalingFactor' => $request->get('yAxisScalingFactor'),
		'zAxisScalingFactor' => $request->get('zAxisScalingFactor'),
		'sites' => $request->get('sites'),
		'startDate' => $request->get('startDate'),
		'period' => $request->get('period'),
	));

	$labels = array(
		'xAxis' => getKPIName($request->get('xAxis')),
		'yAxis' => getKPIName($request->get('yAxis')),
		'zAxis' => getKPIName($request->get('zAxis'))
	);



	if($request->get('graphType') == 'immersive')
	{
		return $app['templating']->render('scatterplot.php', array("data" => $data, 'labels' => $labels)); 
	}
	else if($request->get('graphType') == 'boxplot')
	{
		return $app['templating']->render('boxplot.php', array("data" => $data, 'labels' => $labels)); 
	}
	else
	{
		return $app['templating']->render('3dChart.php', array("data" => $data, 'labels' => $labels));
	}



});

$app->get('/3dChart', function() use($app) {
	return $app['templating']->render('3dChart.php');  
});




//API Requests
$app->post('/api/numberOfDataPoints', function(Request $request) use($app) {

	if(!empty($request->get('startDate')) && count($request->get('sites')) > 0)
	{
		//Calculate the period to check for datapoints
		$startDate = $request->get('startDate');
		$endDate = date("Y-m-d", strtotime($startDate. ' + '.$request->get('period').' days'));

		$sites = $request->get('sites');

		$query = "SELECT count(*) as count from metrics 
				  WHERE date BETWEEN :startDate AND :endDate";


		$bindArray = array(
			':startDate' => $startDate,
			':endDate' => $endDate,
		);


		if($request->get('sites')[0] != 'all')
		{
			$string = implode("','", $request->get('sites'));
			$queryString = "'".$string."'";

			$query .=  ' AND `site_id` IN ('.$queryString.')';
		}

		$axisString = "'".$request->get('xAxis')."', "."'".$request->get('yAxis')."', "."'".$request->get('zAxis')."'";
		$query .= ' AND `kpi_id` IN ('.$axisString.')';

		$stmt = $app['db']->prepare($query);
		//$stmt->bindParam(":startDate", $startDate);
		//$stmt->bindParam(":endDate", $endDate);


		foreach($bindArray as $key => $value)
		{
			$stmt->bindParam($key, $bindArray[$key]);
		}
		

		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			$count = $row['count'];
		}

		$response['dataPoints'] = $count;
	}
	else
	{
		$response['dataPoints'] = 0;
	}

	return json_encode($response);
});

$app->post('/api/threeAxisData', function(Request $request) use($app) {
	
	$dataPoints = array();

	//Preprocess the sites, and date boundaries
	$sites = $request->get('sites');
	$startDate = $request->get('startDate');
	$endDate = date("Y-m-d", strtotime($startDate. ' + '.$request->get('period').' days'));

	//Process xAxis
	$sql = "	SELECT site_id, ".$request->get('xAggregation')."(value)*:xScale as value, date from metrics";
	$sql .= " WHERE kpi_id = :kpi_id AND date between :startDate AND :endDate";

	if($sites[0] != 'all')
	{
		$string = implode("','", $request->get('sites'));
		$queryString = "'".$string."'";
		$sql .=  ' AND `site_id` IN ('.$queryString.')';
	}
	$sql .= " GROUP BY `site_id` ";

	$stmt = $app['db']->prepare($sql);

	$stmt->bindValue("kpi_id", $request->get('xAxis'));
	$stmt->bindValue("startDate", $startDate);
	$stmt->bindValue("endDate", $endDate);
	$stmt->bindValue("xScale", $request->get('xAxisScalingFactor'));
	$stmt->execute();

	$xAxis = $stmt->fetchAll();


	foreach($xAxis as $dataSet)
	{
		$dataPoints[$dataSet['site_id']]['x'] = $dataSet['value'];
	}

	//Process yAxis
	$sql = "	SELECT site_id, ".$request->get('yAggregation')."(value)*:yScale as value, date from metrics";
	$sql .= " WHERE kpi_id = :kpi_id AND date between :startDate AND :endDate";

	if($sites[0] != 'all')
	{
		$string = implode("','", $request->get('sites'));
		$queryString = "'".$string."'";
		$sql .=  ' AND `site_id` IN ('.$queryString.')';
	}
	$sql .= " GROUP BY `site_id` ";

	$stmt = $app['db']->prepare($sql);

	$stmt->bindValue("kpi_id", $request->get('yAxis'));
	$stmt->bindValue("startDate", $startDate);
	$stmt->bindValue("endDate", $endDate);
	$stmt->bindValue("yScale", $request->get('yAxisScalingFactor'));
	$stmt->execute();

	$yAxis = $stmt->fetchAll();


	foreach($yAxis as $dataSet)
	{
		$dataPoints[$dataSet['site_id']]['y'] = $dataSet['value'];
	}

	//Process zAxis
	$sql = "	SELECT site_id, ".$request->get('zAggregation')."(value)*:zScale as value, date from metrics";
	$sql .= " WHERE kpi_id = :kpi_id AND date between :startDate AND :endDate";

	if($sites[0] != 'all')
	{
		$string = implode("','", $request->get('sites'));
		$queryString = "'".$string."'";
		$sql .=  ' AND `site_id` IN ('.$queryString.')';
	}
	$sql .= " GROUP BY `site_id` ";

	$stmt = $app['db']->prepare($sql);

	$stmt->bindValue("kpi_id", $request->get('zAxis'));
	$stmt->bindValue("startDate", $startDate);
	$stmt->bindValue("endDate", $endDate);
	$stmt->bindValue("zScale", $request->get('zAxisScalingFactor'));
	$stmt->execute();

	$zAxis = $stmt->fetchAll();


	foreach($zAxis as $dataSet)
	{
		$dataPoints[$dataSet['site_id']]['z'] = $dataSet['value'];
	}


	return json_encode($dataPoints);
});

$app->get('api/minAndMaxValues', function(Request $request) use($app) {

	$sites = $request->get('sites');
	if(!empty($sites))
	{

		$startDate = $request->get('startDate');
		$endDate = date("Y-m-d", strtotime($startDate. ' + '.$request->get('period').' days'));

		//find Min
		$sql = " SELECT min(a.value) as minimum, max(a.value) as maximum, avg(a.value) as average from
				(
				SELECT site_id, ".$request->get('aggregation')."(value)*:scale as value, date from metrics
				WHERE kpi_id = :kpi_id
				AND date between :startDate AND :endDate";
		
		if($sites[0] != 'all')
		{
			$string = implode("','", $request->get('sites'));
			$queryString = "'".$string."'";
			$sql .=  ' AND `site_id` IN ('.$queryString.')';
		}	
		$sql .=	" GROUP BY `site_id`
				) as a";

		
		$stmt = $app['db']->prepare($sql);

		$stmt->bindValue("kpi_id", $request->get('axis'));
		$stmt->bindValue("startDate", $startDate);
		$stmt->bindValue("endDate", $endDate);
		$stmt->bindValue("scale", $request->get('scalingFactor'));
		$stmt->execute();

		$values = $stmt->fetchAll();

		$response = array();
		$response['minimum'] = round($values[0]['minimum'], 2);
		$response['maximum'] = round($values[0]['maximum'], 2);
		$response['average'] = round($values[0]['average'], 2);

		return json_encode($response);
	}
	else
	{
		return json_encode(array("minimum" => 0, "maximum" => 0, "average" => 0));
	}
});

$app->run();

<?php
require_once 'config/autoload.php';

$config = new Config();
/*$receipents = $config->email_list();
//print_r($receipents); exit();
$message = '';
$result = send_email($receipents, $message);
exit();*/

$db = new Database();
//$db_ods = $db->connect_ods();
$db_mro = $db->connect_mov();

$date = array('start' => isset($_GET['start']) ? $_GET['start'] : date('Y-m-d'),
		'end' => isset($_GET['end']) ? $_GET['end'] : date('Y-m-d')
	) ;

//print_r($date); exit();
//$a = get_data_ods($db_ods, $date);
$b = get_data_mov($db_mro, $date);
//print_r($b); exit();
$result = array();

foreach ($b as $rows) {
	$result[] = array(
		'COL_FLIGHT_NUMBER' => $rows['COL_FLIGHT_NUMBER'],
		'COL_DEPARTURE_NUMBER' => $rows['COL_DEPARTURE_NUMBER'],
		'COL_DEPARTURE_STATION' => $rows['COL_DEPARTURE_STATION'],
		'COL_PLAN_DEPARTURE_DATE' => $rows['COL_PLAN_DEPARTURE_DATE'],
		'COL_AIRCRAFT_REGISTRATION' => $rows['COL_AIRCRAFT_REGISTRATION'],
		'COL_CHOX_OFF_DATE' => $rows['COL_CHOX_OFF_DATE'],
		'COL_CHOX_OFF_TIME' => $rows['COL_CHOX_OFF_TIME'],
		'COL_WHEELS_OFF_DATE' => $rows['COL_WHEELS_OFF_DATE'],
		'COL_WHEELS_OFF_TIME' => $rows['COL_WHEELS_OFF_TIME'],
		'COL_EST_DEP_DATE' => $rows['COL_EST_DEP_DATE'],
		'COL_EST_DEP_TIME' => $rows['COL_EST_DEP_TIME'],
		'COL_EST_WHEELS_OFF_DATE' => $rows['COL_EST_WHEELS_OFF_DATE'],
		'COL_EST_WHEELS_OFF_TIME' => $rows['COL_EST_WHEELS_OFF_TIME'],
		'COL_CHOX_ON_DATE' => $rows['COL_CHOX_ON_DATE'],
		'COL_CHOX_ON_TIME' => $rows['COL_CHOX_ON_TIME'],
		'COL_WHEELS_ON_DATE' => $rows['COL_WHEELS_ON_DATE'],
		'COL_WHEELS_ON_TIME' => $rows['COL_WHEELS_ON_TIME'],
		'COL_EST_ARR_DATE' => $rows['COL_EST_ARR_DATE'],
		'COL_EST_ARR_TIME' => $rows['COL_EST_ARR_TIME'],
		'COL_EST_WHEELS_ON_DATE' => $rows['COL_EST_WHEELS_ON_DATE'],
		'COL_EST_WHEELS_ON_TIME' => $rows['COL_EST_WHEELS_ON_TIME'],
		'COL_ARR_STATION' => $rows['COL_ARR_STATION'],
		'COL_FLIGHT_TYPE' => $rows['COL_FLIGHT_TYPE']
	);
}

file_put_contents('data_mov.json', json_encode($result));
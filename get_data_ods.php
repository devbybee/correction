<?php
require_once 'config/autoload.php';

$config = new Config();
/*$receipents = $config->email_list();
//print_r($receipents); exit();
$message = '';
$result = send_email($receipents, $message);
exit();*/

$db = new Database();
$db_ods = $db->connect_ods();
//$db_mro = $db->connect_mov();

$date = array('start' => isset($_GET['start']) ? $_GET['start'] : date('Y-m-d'),
		'end' => isset($_GET['end']) ? $_GET['end'] : date('Y-m-d')
	) ;

//print_r($date); exit();
$a = get_data_ods($db_ods, $date);
//$b = get_data_mov($db_mro, $date);
//print_r($b); exit();
$result = array();

foreach ($a as $rows) {
	$result[] = array(
		'FL_NUM' => $rows['FL_NUM'],
		'DEP_NUM' => $rows['DEP_NUM'],
		'DEP_STAT' => $rows['DEP_STAT'],
		'PLAN_DATE' => $rows['PLAN_DATE'],
		'AC_REG' => $rows['AC_REG'],
		'CHOX_OFF' => $rows['CHOX_OFF'],
		'WHEELS_OFF' => $rows['WHEELS_OFF'],
		'WHEELS_ON' => $rows['WHEELS_ON'],
		'CHOX_ON' => $rows['CHOX_ON'],
		'E_CHOX_OFF' => $rows['E_CHOX_OFF'],
		'E_WHEELS_OFF' => $rows['E_WHEELS_OFF'],
		'E_WHEELS_ON' => $rows['E_WHEELS_ON'],
		'E_CHOX_ON' => $rows['E_CHOX_ON'],
		'ARR_STAT' => $rows['ARR_STAT'],
		'FL_TYPE' => $rows['FL_TYPE'],
		'CANCELLATION' => $rows['CANCELLATION'],
		'ENT_DATE' => $rows['ENT_DATE'],
		'REF_NUM' => $rows['REF_NUM'],
		'SUFFIX' => $rows['SUFFIX']
	);
}

file_put_contents('data_ods.json', json_encode($result));
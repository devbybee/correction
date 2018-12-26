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
$db_mro = $db->connect_mov();

$date = array('start' => isset($_GET['start']) ? $_GET['start'] : date('Y-m-d'),
		'end' => isset($_GET['end']) ? $_GET['end'] : date('Y-m-d')
	) ;

//print_r($date); exit();
$a = get_data_ods($db_ods, $date);
$b = get_data_mov($db_mro, $date);
//print_r($b); exit();
$result = array();

foreach ($a as $val) {
	$chox_off = is_plus($val['CHOX_OFF']);
	$wheels_off =  is_plus($val['WHEELS_OFF']);
	$wheels_on = is_plus($val['WHEELS_ON']);
	$chox_on = is_plus($val['CHOX_ON']);

	$e_chox_off = is_plus_est($val['E_CHOX_OFF'], $val['CHOX_OFF']);
	$e_wheels_off =  is_plus_est($val['E_WHEELS_OFF'], $val['WHEELS_OFF']);
	$e_wheels_on = is_plus_est($val['E_WHEELS_ON'], $val['WHEELS_ON']);
	$e_chox_on = is_plus_est($val['E_CHOX_ON'], $val['CHOX_ON']);

	$x = 'GA|' . $val['FL_NUM'] . $val['SUFFIX'] . '|' . dep_number($val['DEP_NUM']) . '|' . $val['DEP_STAT'] . '|' . $val['PLAN_DATE'] . '|' . $val['AC_REG'] . '|' . $chox_off['date'] . '|' . $chox_off['time'] . '|' . $wheels_off['date'] . '|' . $wheels_off['time'] . '|' . $e_chox_off['date'] . '|' . $e_chox_off['time'] . '|' . $e_wheels_off['date'] . '|' . $e_wheels_off['time'] . '|' . $chox_on['date'] . '|' . $chox_on['time'] . '|' . $wheels_on['date'] . '|' . $wheels_on['time'] . '|' . $e_chox_on['date'] . '|' . $e_chox_on['time'] . '|' . $e_wheels_on['date'] . '|' . $e_wheels_on['time'] . '|' . $val['ARR_STAT']. '|' . $val['FL_TYPE'];
	
	$key = 'GA' . $val['FL_NUM'] . $val['SUFFIX'] . dep_number($val['DEP_NUM']) . $val['PLAN_DATE'] . $val['DEP_STAT'] . $val['ARR_STAT'];

	$dupl = 'GA' . '|' . $val['FL_NUM'] . $val['SUFFIX'] . '|' . dep_number($val['DEP_NUM']) . '|' . $val['DEP_STAT'] . '|' . $val['PLAN_DATE'] . '|' . $val['ARR_STAT'];

	if (isset($b [$key] )) {
		if (strlen($x) > strlen( $b[$key] ) ) {
			$idx = get_idx($db_mro);
			insert_data($db_mro, $idx, $val, $dupl, $x);
		}	
	}
	else {
		//$result[] = $x;
		$idx = get_idx($db_mro);
		insert_data($db_mro, $idx, $val, $dupl, $x);
	}
}
		
//print_r($result); exit();		
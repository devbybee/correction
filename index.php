<?php
require_once 'config/autoload.php';

$config = new Config();

$db = new Database();
$db_ods = $db->connect_ods();
$db_mro = $db->connect_mov();
$db_mro_dev = $db->connect_mov_dev();

$date = array('start' => isset($_GET['start']) ? $_GET['start'] : date('Y-m-d'),
		'end' => isset($_GET['end']) ? $_GET['end'] : date('Y-m-d')
	) ;


$a = get_data_ods($db_ods, $date);
$b = get_data_mov($db_mro, $date);
/*$a = json_decode(file_get_contents('data_ods.json'), TRUE);
$temp = json_decode(file_get_contents('data_mov.json'), TRUE);*/

/*$b = array();
foreach ($temp as $rows) {
	$b[ 
			'GA' . 
			$rows['COL_FLIGHT_NUMBER'] .
			$rows['COL_DEPARTURE_NUMBER'] .
			$rows['COL_PLAN_DEPARTURE_DATE'] . 
			$rows['COL_DEPARTURE_STATION'] . 
			$rows['COL_ARR_STATION']
		]  = 'GA|' . 
			$rows['COL_FLIGHT_NUMBER'] . '|' . 
			$rows['COL_DEPARTURE_NUMBER'] . '|' .
			$rows['COL_DEPARTURE_STATION'] . '|' .
			$rows['COL_PLAN_DEPARTURE_DATE'] . '|' .  
			$rows['COL_AIRCRAFT_REGISTRATION'] . '|' .
			empty_date_time($rows['COL_CHOX_OFF_DATE']) . '|' .
			empty_date_time($rows['COL_CHOX_OFF_DATE'], remove_zulu($rows['COL_CHOX_OFF_TIME'])) . '|' .
			empty_date_time($rows['COL_WHEELS_OFF_DATE']) . '|' .
			empty_date_time($rows['COL_WHEELS_OFF_DATE'], remove_zulu($rows['COL_WHEELS_OFF_TIME'])) . '|' .
			empty_date_time($rows['COL_EST_DEP_DATE']) . '|' .
			empty_date_time($rows['COL_EST_DEP_DATE'], remove_zulu($rows['COL_EST_DEP_TIME'])) . '|' .
			empty_date_time($rows['COL_EST_WHEELS_OFF_DATE']) . '|' .
			empty_date_time($rows['COL_EST_WHEELS_OFF_DATE'], remove_zulu($rows['COL_EST_WHEELS_OFF_TIME'])) . '|' .
			empty_date_time($rows['COL_CHOX_ON_DATE']) . '|' .
			empty_date_time($rows['COL_CHOX_ON_DATE'], remove_zulu($rows['COL_CHOX_ON_TIME'])) . '|' .
			empty_date_time($rows['COL_WHEELS_ON_DATE']) . '|' .
			empty_date_time($rows['COL_WHEELS_ON_DATE'], remove_zulu($rows['COL_WHEELS_ON_TIME'])) . '|' .
			empty_date_time($rows['COL_EST_ARR_DATE']) . '|' .
			empty_date_time($rows['COL_EST_ARR_DATE'], remove_zulu($rows['COL_EST_ARR_TIME'])) . '|' .
			empty_date_time($rows['COL_EST_WHEELS_ON_DATE']) . '|' .
			empty_date_time($rows['COL_EST_WHEELS_ON_DATE'], remove_zulu($rows['COL_EST_WHEELS_ON_TIME'])) . '|' .
			$rows['COL_ARR_STATION'] . '|' .
			$rows['COL_FLIGHT_TYPE']
		;
}
*/
$msg_delta = '';
$msg_no_landing = '';
$msg_miss_route = '';
$msg_add = '';
$msg_rta = '';

$group_acreg = array();
$no = 1;
$no2 = 1;
$no3 = 1;
$no4 = 1;
$no5 =  1;

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

	/* insert delta data*/
	if (isset($b [$key] )) {
		if (strlen($x) > strlen( $b[$key] ) ) {
			$idx = get_idx($db_mro);
			insert_data($db_mro_dev, $idx, $val, $dupl, $x);
			//adding message
			$msg_delta .= msg_delta($val, $no);
			$no++;
		}	
	}
	else {
		//$result[] = $x;
		$idx = get_idx($db_mro);
		insert_data($db_mro_dev, $idx, $val, $dupl, $x);
		$msg_delta .= msg_delta($val, $no);
		$no++;
	}

	/* check landing data and take off */
	if ( (!empty($val['WHEELS_OFF'] ) && empty($val['WHEELS_ON'])) || (empty($val['WHEELS_OFF'] ) && !empty($val['WHEELS_ON'])) ) {
		$msg_no_landing .= msg_delta($val, $no2, TRUE);
		$no2++;
		//$no_landing[] = $val;
	}
	/*check no landing*/

	/*grouping an array by ac_reg*/
	if ( !empty($val['WHEELS_OFF']) ) {
		$group_acreg[ $val['AC_REG'] ][] = array(
			'FL_NUM' => $val['FL_NUM'],
			'PLAN_DATE' => $val['PLAN_DATE'],
			'CHOX_OFF' => $val['CHOX_OFF'],
			'WHEELS_OFF' => $val['WHEELS_OFF'],
			'WHEELS_ON' => $val['WHEELS_ON'],
			'CHOX_ON' => $val['CHOX_ON'],
			'AC_REG' => $val['AC_REG'],
			'DEP_STAT' => $val['DEP_STAT'],
			'ARR_STAT' => $val['ARR_STAT'],
			'SUFFIX' => $val['SUFFIX']
		);
	}
	/*end grouping an array by ac_reg*/

	/* status 
	which fields must to have sign
	*/
	$status = 1;

	//flight doesnt more than 20 hours or less than 10 mins
	if ( !empty($val['WHEELS_OFF']) && !empty($val['WHEELS_ON']) ) {
		$mins = reduction_time($val['WHEELS_OFF'], $val['WHEELS_ON']); 
		if ($mins >= (20*60) || $mins <= (10)) {
			//var_dump($mins); exit();
			$msg_add .= msg_delta($val, $no4, FALSE, $mins, $status);
			$no4++;
		}
	}

	if ( !empty($val['CHOX_OFF']) && !empty($val['WHEELS_OFF']) ) {
		$mins = reduction_time($val['CHOX_OFF'], $val['WHEELS_OFF']); 
		if ($mins >= 60) {
			$status = 2;
			$msg_add .= msg_delta($val, $no4, FALSE, $mins, $status);
			$no4++;
		}
	}

	if ( !empty($val['WHEELS_ON']) && !empty($val['CHOX_ON']) ) {
		$mins = reduction_time($val['WHEELS_ON'], $val['CHOX_ON']); 
		if ($mins >= 60) {
			$status = 3;
			$msg_add .= msg_delta($val, $no4, FALSE, $mins, $status);
			$no4++;
		}
	}
	//end  not ideal flight

	/* RTA Condition */
	if ($val['DEP_STAT'] === $val['ARR_STAT'] && strtolower($val['DEP_NUM']) == 'other' ) {
		$msg_rta .= msg_delta($val, $no5);
		$no5++;
	}
	/* End RTA Condition */

}	

//print_r($group_acreg); exit();
//sorting an array by date
foreach ($group_acreg as $reg => $value) {
	usort($value, "sortFunction");
	//print_r($value); exit();
	foreach ($value as $key => $rute) {
		if ($key >= 1) {
			//print_r($value[ $key ]['DEP_STAT']); exit();
			if ($value[ $key - 1]['ARR_STAT'] <> $value[ $key ]['DEP_STAT'])	{
				//print_r($value); exit();
				//echo $value[ $key - 1]['ARR_STAT']; exit();
				$msg_miss_route .= msg_rowspan($value[ $key - 1 ], $no3);
				$msg_miss_route .= msg_rowspan($rute, $no3, $no3);
				$no3++;
			}
		}
	}
}

//print_r($group_acreg); exit();

if ( !empty($msg_delta) || !empty($msg_no_landing) || !empty($msg_miss_route) || !empty($msg_add) || !empty($msg_rta) ) {
	$messages = generate_message($msg_delta, $msg_no_landing, $msg_miss_route, $msg_add, $msg_rta);

	//sending email
	//$receipents = $config->email_list();
	//$result = send_email($receipents, $messages);

	echo '<pre>' . $messages . '</pre>';
}

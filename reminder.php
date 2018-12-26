<?php
require_once 'config/autoload.php';

$config = new Config();

$db = new Database();
$db_ods = $db->connect_ods();

$date = array('start' => isset($_GET['start']) ? $_GET['start'] : get_param_correction(3),
		'end' => isset($_GET['end']) ? $_GET['end'] : get_param_correction()
	) ;


$a = get_data_ods($db_ods, $date);

$msg_no_landing = '';
$msg_miss_route = '';
$msg_add = '';
$msg_rta = '';

$group_acreg = array();
$no2 = 1;
$no3 = 1;
$no4 = 1;
$no5 =  1;

foreach ($a as $val) {
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

if (!empty($msg_no_landing) || !empty($msg_miss_route) || !empty($msg_add) || !empty($msg_rta) ) {
	$messages = generate_message_reminder($msg_no_landing, $msg_miss_route, $msg_add, $msg_rta);

	//sending email
	$receipents = $config->email_list();
	$result = send_email($receipents, $messages);

	//echo '<pre>' . $messages . '</pre>';
}
else {
	echo 'No abnormal data';
}

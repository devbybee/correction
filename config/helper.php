<?php
/*
0 means time must be subtracy by 1
*/

function get_param_correction($param = 1) {
	$newdate = strtotime('-' . $param. 'day' , strtotime(date('Y-m-d'))) ;
	$newdate = date( 'Y-m-d' , $newdate );

	return $newdate;
}

function is_plus($datetime) {
	$result = array('date' => '', 'time' => '');
	
	if (!empty($datetime)) {
		$datetime =  explode(' ', $datetime);
		$new_time = explode(':', $datetime[1]);
   		$time = $datetime[1];

	   	if ($new_time[0] == '17' || $new_time[0] == '07' || $new_time[0] == '00') {
	   		if ($new_time[1] == '00' && $new_time[2] == '00') {
	   			$time = $new_time[0] . ':' . $new_time[1] . ':01';
	       	}
		}

	   	$result = array('date' => $datetime[0], 'time' => $time);
	}

	return $result;
}

function is_plus_est($datetime, $act) {
	$result = array('date' => '', 'time' => '');
	
	if (!empty($datetime)) {
		$datetime =  explode(' ', $datetime);
   		$time = $datetime[1];

	   	$result = array('date' => $datetime[0], 'time' => $time);
	}
	else {
		if(!empty($act)) {
			$datetime =  explode(' ', $act);
   			$time = $datetime[1];

	   		$result = array('date' => $datetime[0], 'time' => $time);
		}
	}

	return $result;
}

function dep_number($param) {
	$cdn = strtolower($param);
	$return = '01';

	if ($cdn == 'groundreturn' || $cdn == 'airreturn') $return = '02';

	return $return;
}

function remove_zulu($param) {
	$result = preg_replace('/^([^.]*).*$/', '$1', $param);
	return $result;
}

function send_email($recipients, $message/*, $loop = 1*/)
{
	$params["host"]    = 'mail.gmf-aeroasia.co.id';
	$params["auth"]    = TRUE; // note: there are *no delimiters*
	$params['persist'] = TRUE; 
	$params["username"]    = 'app.notif@gmf-aeroasia.co.id';
	$params["password"]    = 'app.notif';
	$params["debug"]    = FALSE; 
	$mail_message =& Mail::factory('smtp', $params);

	$subject = 'Notifikasi GA - Flight';
	$from = 'app.notif@gmf-aeroasia.co.id';

	$headers["From"] = $from;
	$headers["To"]    = $recipients;
	$headers["Subject"] = $subject;
	$headers["reply-to"] = $from;

	$crlf = "<br>";
	$mime = new Mail_mime();
	$mime->setTXTBody(strip_tags($message));
	$mime->setHTMLBody($message);
	$message_get = $mime->get();
	$headers = $mime->headers($headers);

	$is_send = $mail_message->send($recipients, $headers, $message_get);
	//print_r($is_send); exit();
	if ( !isset($is_send->code)) {
		echo 'e-mail has been sent...';
		//return TRUE;
	}
	else {
		//if wanna limit the process
		/*if ($loop == 5) {
			die('stop sending email');
		}

		$loop++;*/
		send_email($recipients, $message/*, $loop*/);
	}
}

function msg_rowspan($val, $no, $old_no = 0) {
	$msg = '';
	$bg = '#eee';
	$tdrowspan = '<td rowspan="2"> ' . $no .' </td>';

	if( ($no % 2) == 0 ) {
		$bg = '#fff';
	}

	if ($no === $old_no) {
		$tdrowspan = '';
	}

	$message = "<tr style='background: $bg;'>
			" .  $tdrowspan . "
			<td>". $val['FL_NUM'] . $val['SUFFIX'] ." </td>
			<td>". $val['PLAN_DATE'] ." </td>
			<td>". $val['AC_REG'] ." </td>
			<td>". $val['DEP_STAT'] ." </td>
			<td>". $val['ARR_STAT'] ." </td>
			" . $msg . "
		</tr>";
	return $message;

}

function msg_delta($val, $no, $incomplete = FALSE, $abnormal = NULL, $status = 1 ) {
	$msg = '';
	$bg = '#eee';

	if( ($no % 2) == 0 ) {
		$bg = '#fff';
	}

	if ($incomplete) {
		$msg .= "<td>". $val['WHEELS_OFF'] ." </td>";
		$msg .= "<td>". $val['WHEELS_ON'] ." </td>";
	}

	if ( $abnormal !== NULL ) {
		$a = '';
		$b = '';
		$c = '';
		$d = '';

		if ($status == 1) {
			$b = " style='background:#ffffc1'";
			$c = $b;
		}
		elseif ($status == 2) {
			$a = " style='background:#ffffc1'";
			$b = $a;	
		}
		else {
			$c = " style='background:#ffffc1'";
			$d = $c;
		}

		$msg .= "<td$a>". $val['CHOX_OFF'] ." </td>";
		$msg .= "<td$b>". $val['WHEELS_OFF'] ." </td>";
		$msg .= "<td$c>". $val['WHEELS_ON'] ." </td>";
		$msg .= "<td$d>". $val['CHOX_ON'] ." </td>";
		$msg .= "<td>". $abnormal ." </td>";
	}

	$message = "<tr style='background: $bg;'>
			<td>". $no ."</strong> </td>
			<td>". $val['FL_NUM'] . $val['SUFFIX'] ." </td>
			<td>". $val['PLAN_DATE'] ." </td>
			<td>". $val['AC_REG'] ." </td>
			<td>". $val['DEP_STAT'] ." </td>
			<td>". $val['ARR_STAT'] ." </td>
			" . $msg . "
		</tr>";
	return $message;
}

function msg_route($val) {
	$message = "<tr style='background: #eee;'>
			<td><strong>". $val['AC_REG'] ."</strong> </td>
		</tr>";
	return $message;
}

function msg_delta_header($title = 'MISSING FLIGHT', $incomplete = FALSE, $abnormal = FALSE) {
	$msg = '';

	if ($incomplete) {
		$msg .= "<td><strong>Wheels Off</strong> </td>";
		$msg .= "<td><strong>Wheels On</strong> </td>";
	}

	if ($abnormal) {
		$msg .= "<td><strong>Chox Off</strong> </td>";
		$msg .= "<td><strong>Wheels Off</strong> </td>";
		$msg .= "<td><strong>Wheels On</strong> </td>";
		$msg .= "<td><strong>Chox On</strong> </td>";
		$msg .= "<td><strong>diff (mins)</strong> </td>";
	}

	$message = '<div>' . $title . '</div><hr />';
	$message .= '<table rules="all" style="border-color: #666;" cellpadding="10">';
	$message .= "<tr style='background: #eee;'>
			<td><strong>No</strong> </td>
			<td><strong>Flight Number</strong> </td>
			<td><strong>Plan Date</strong> </td>
			<td><strong>A/C Reg</strong> </td>
			<td><strong>Dep Stat</strong> </td>
			<td><strong>Arr Stat</strong> </td>
			" . $msg . "
		</tr>";

	return $message;
}

function msg_delta_footer() {
	$message = "</table><hr /><br />";
	return $message;
}

function foot_note() {
	$msg = '<br /><br /><div>Demikian dan Terima kasih atas kerja samanya</div><br /><br /><div>Best regards</div>';
	return $msg;
}

function generate_message($a, $b, $c, $d, $e) {
	$messages = msg_delta_header('Berikut ini terlampir flight yang available di ODS-Garuda tetapi tidak diterima di database GMF') . $a . msg_delta_footer();
	$messages .= msg_delta_header('Mohon bantuannya untuk update data DEP_DT, ARR_DT, AIRBORNE_DT, dan LANDING_DT', TRUE) . $b . msg_delta_footer();
	$messages .= msg_delta_header('Berikut ini terlampir flight (registrasi) yang memiliki rute tidak sesuai') . $c . msg_delta_footer();
	$messages .= msg_delta_header('Mohon diverifikasi untuk airtime yang abnormal', FALSE, TRUE) . $d . msg_delta_footer();
	$messages .= msg_delta_header('Berikut adalah schedule flight dengan station yang sama, mohon dibantu verifikasi apakah Non Revenue, RTA, RTB, dsb') . $e . msg_delta_footer();
	$messages .= foot_note();
	return $messages;
}

function generate_message_reminder($b, $c, $d, $e) {
	$messages = '';
	if ( !empty ($b)) {
		$messages .= msg_delta_header('Mohon bantuannya untuk update data DEP_DT, ARR_DT, AIRBORNE_DT, dan LANDING_DT', TRUE) . $b . msg_delta_footer();
	}
	if ( !empty ($c)) {
		$messages .= msg_delta_header('Berikut ini terlampir flight (registrasi) yang memiliki rute tidak sesuai') . $c . msg_delta_footer();
	}
	if ( !empty ($d)) {
		$messages .= msg_delta_header('Mohon diverifikasi untuk airtime yang abnormal', FALSE, TRUE) . $d . msg_delta_footer();
	}
	if ( !empty ($e)) {
		$messages .= msg_delta_header('Berikut adalah schedule flight dengan station yang sama, mohon dibantu verifikasi apakah Non Revenue, RTA, RTB, dsb') . $e . msg_delta_footer();
	}
	
	$messages .= foot_note();
	return $messages;
}

function generate_message_correction($a) {
	$messages = msg_delta_header('Berikut ini terlampir flight yang available di ODS-Garuda tetapi tidak diterima di database GMF') . $a . msg_delta_footer();
	$messages .= foot_note();
	return $messages;
}

function sortFunction( $a, $b ) {
    return strtotime($a["WHEELS_OFF"]) - strtotime($b["WHEELS_OFF"]);
}

function reduction_time($a, $b) {
	$mins = (strtotime($b) - strtotime($a)) / 60;
	return $mins;
}

function empty_date_time($date, $time = '') {
	if ( empty($time) ) {
		if ($date == '1900-01-01') {
			return '';
		}

		return $date;
	}

	else {
		if ($date == '1900-01-01') {
			return '';
		}
		else {
			return $time;
		}
	}
}
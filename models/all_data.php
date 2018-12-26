<?php

function get_data_ods($conn, $date = array()) {
	$query = "SELECT  
			FLTNUM AS FL_NUM,
			SPECIALSTATUS AS DEP_NUM, 
			LATEST_DEPARTUREAIRPORT AS DEP_STAT, 
			TO_CHAR(OPSDATE, 'YYYY-MM-DD') AS PLAN_DATE, 
			AIRCRAFTREG AS AC_REG, 
			TO_CHAR(ACTUAL_BLOCKOFF, 'YYYY-MM-DD HH24:MI:SS') AS CHOX_OFF,
			TO_CHAR(ACTUAL_TAKEOFF, 'YYYY-MM-DD HH24:MI:SS') AS WHEELS_OFF,
			TO_CHAR(ACTUAL_TOUCHDOWN, 'YYYY-MM-DD HH24:MI:SS') AS WHEELS_ON,
			TO_CHAR(ACTUAL_BLOCKON, 'YYYY-MM-DD HH24:MI:SS') AS CHOX_ON,
			TO_CHAR(ESTIMATED_BLOCKOFF, 'YYYY-MM-DD HH24:MI:SS') AS E_CHOX_OFF,
			TO_CHAR(ESTIMATED_TAKEOFF, 'YYYY-MM-DD HH24:MI:SS') AS E_WHEELS_OFF, 
			TO_CHAR(ESTIMATED_TOUCHDOWN, 'YYYY-MM-DD HH24:MI:SS') AS E_WHEELS_ON, 
			TO_CHAR(ESTIMATED_BLOCKON, 'YYYY-MM-DD HH24:MI:SS') AS E_CHOX_ON, 
			LATEST_ARRIVALAIRPORT AS ARR_STAT, 
			SERVICETYPE AS FL_TYPE, 
			STATUS AS CANCELLATION, 
			TO_CHAR(MODIFIEDDATE, 'YYYY-MM-DD HH24:MI:SS') AS ENT_DATE, 
			FLIGHTLEGREF AS REF_NUM,
			SUFFIX
		FROM DBODSXML4OPS.XML4OPS 
		WHERE 
			TO_CHAR(OPSDATE, 'YYYY-MM-DD') BETWEEN '" . $date['start'] . "' AND '" . $date['end'] . "' AND 
			STATUS <> 'Cancelled'
			AND CARRIER = 'GA'
			"/* AND
			TO_CHAR(MODIFIEDDATE, 'YYYY-MM-DD HH24:MI:SS') >= '" . date('Y-m-d H:i:s', strtotime('+390 minutes', strtotime(date('Y-m-d H:i:s')))) 
			. "'"*/;
	
	$sql = OCI_Parse($conn, $query);
	$r = OCI_Execute($sql);
	
	if (!$r) {
		$e = oci_error($sql);  // For oci_parse errors pass the connection handle
    	trigger_error(htmlentities($e['message']), E_USER_ERROR);
    	exit();
	}

	$results = array();
	while ( false !== ($row = oci_fetch_assoc($sql)) ) {
    	$results[] = $row;
	}

	return $results;
}

function get_data_mov($conn, $date) {
	$query = "SELECT
		A.COL_FLIGHT_NUMBER, 
		A.COL_DEPARTURE_NUMBER, 
		A.COL_DEPARTURE_STATION,
		A.COL_PLAN_DEPARTURE_DATE, 
		A.COL_AIRCRAFT_REGISTRATION, 
		A.COL_CHOX_OFF_DATE,
		A.COL_CHOX_OFF_TIME,
		A.COL_WHEELS_OFF_DATE,
		A.COL_WHEELS_OFF_TIME,
		A.COL_EST_DEP_DATE,
		A.COL_EST_DEP_TIME,
		A.COL_EST_WHEELS_OFF_DATE,
		A.COL_EST_WHEELS_OFF_TIME,
		A.COL_CHOX_ON_DATE,
		A.COL_CHOX_ON_TIME,
		A.COL_WHEELS_ON_DATE,
		A.COL_WHEELS_ON_TIME,
		A.COL_EST_ARR_DATE,
		A.COL_EST_ARR_TIME,
		A.COL_EST_WHEELS_ON_DATE,
		A.COL_EST_WHEELS_ON_TIME,
		A.COL_ARR_STATION, 
		A.COL_FLIGHT_TYPE
	FROM TBL_AC_MOVEMENT_PROD1 A
	JOIN 
		(SELECT COL_CARRIER_CODE, COL_FLIGHT_NUMBER, COL_DEPARTURE_NUMBER, COL_PLAN_DEPARTURE_DATE, COL_DEPARTURE_STATION, COL_ARR_STATION, MAX(COL_IDX) AS max_idx
		FROM TBL_AC_MOVEMENT_PROD1
		GROUP BY COL_CARRIER_CODE, COL_FLIGHT_NUMBER, COL_DEPARTURE_NUMBER, COL_PLAN_DEPARTURE_DATE, COL_DEPARTURE_STATION, COL_ARR_STATION) B
	ON A.COL_CARRIER_CODE = B.COL_CARRIER_CODE AND A.COL_FLIGHT_NUMBER = B.COL_FLIGHT_NUMBER AND 
		A.COL_DEPARTURE_NUMBER = B.COL_DEPARTURE_NUMBER AND
		A.COL_PLAN_DEPARTURE_DATE = B.COL_PLAN_DEPARTURE_DATE AND 
		A.COL_DEPARTURE_STATION = B.COL_DEPARTURE_STATION AND A.COL_ARR_STATION = B.COL_ARR_STATION AND A.COL_IDX = B.max_idx
	WHERE 
		A.COL_PLAN_DEPARTURE_DATE BETWEEN '" . $date['start'] . "' AND '" . $date['end'] . "' AND
		"/*A.COL_ENTRY_DATE >= '" . date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date('Y-m-d H:i:s')))) . "' AND*/
		. " A.COL_CARRIER_CODE = 'GA'"
	;

	//no cancelled filter

	$do = mssql_query($query, $conn);

	if (! $do) {
		mssql_get_last_message();
		exit();
	} 

	$results = array();
	
	/*while($rows = mssql_fetch_array($do)) {
		$results[] = $rows;
	}

	return $results;*/

	while($rows = mssql_fetch_array($do)) {
		$results[ 
			'GA' . 
			$rows['COL_FLIGHT_NUMBER'] .
			$rows['COL_DEPARTURE_NUMBER'] .
			$rows['COL_PLAN_DEPARTURE_DATE'] . 
			$rows['COL_DEPARTURE_STATION'] . 
			$rows['COL_ARR_STATION']
			/*$rows['COL_PLAN_DEPARTURE_DATE'] . '|' .  
			$rows['COL_AIRCRAFT_REGISTRATION'] . '|' .
			$rows['COL_CHOX_OFF_DATE'] . '|' .
			remove_zulu($rows['COL_CHOX_OFF_TIME']) . '|' .
			$rows['COL_WHEELS_OFF_DATE'] . '|' .
			remove_zulu($rows['COL_WHEELS_OFF_TIME']) . '|' .
			$rows['COL_EST_DEP_DATE'] . '|' .
			remove_zulu($rows['COL_EST_DEP_TIME']) . '|' .
			$rows['COL_EST_WHEELS_OFF_DATE'] . '|' .
			remove_zulu($rows['COL_EST_WHEELS_OFF_TIME']) . '|' .
			$rows['COL_CHOX_ON_DATE'] . '|' .
			remove_zulu($rows['COL_CHOX_ON_TIME']) . '|' .
			$rows['COL_WHEELS_ON_DATE'] . '|' .
			remove_zulu($rows['COL_WHEELS_ON_TIME']) . '|' .
			$rows['COL_EST_ARR_DATE'] . '|' .
			remove_zulu($rows['COL_EST_ARR_TIME']) . '|' .
			$rows['COL_EST_WHEELS_ON_DATE'] . '|' .
			remove_zulu($rows['COL_EST_WHEELS_ON_TIME']) . '|' .
			$rows['COL_ARR_STATION'] . '|' .
			$rows['COL_FLIGHT_TYPE']*/
		] 
			= array(
				'GA|' . 
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
			$rows['COL_FLIGHT_TYPE'],
			empty_date_time($rows['COL_CHOX_OFF_DATE']) . ' '. empty_date_time($rows['COL_CHOX_OFF_DATE'], remove_zulu($rows['COL_CHOX_OFF_TIME'])),
			empty_date_time($rows['COL_WHEELS_OFF_DATE']) . ' ' . empty_date_time($rows['COL_WHEELS_OFF_DATE'], remove_zulu($rows['COL_WHEELS_OFF_TIME'])),
			empty_date_time($rows['COL_WHEELS_ON_DATE']) . ' ' . empty_date_time($rows['COL_WHEELS_ON_DATE'], remove_zulu($rows['COL_WHEELS_ON_TIME'])),
			empty_date_time($rows['COL_CHOX_ON_DATE']) . ' ' . empty_date_time($rows['COL_CHOX_ON_DATE'], remove_zulu($rows['COL_CHOX_ON_TIME']))
			)
		;
	}
	return $results;
}

function get_idx($conn) {
	$query = mssql_query("SELECT (max(COL_IDX))+1 FROM TBL_AC_MOVEMENT_PROD1", $conn);
	$data = mssql_fetch_row($query);
	
	return $data[0];
}

function insert_data($conn, $idx, $val, $dupl, $key) {
	$chox_off = is_plus($val['CHOX_OFF']);
	$wheels_off =  is_plus($val['WHEELS_OFF']);
	$wheels_on = is_plus($val['WHEELS_ON']);
	$chox_on = is_plus($val['CHOX_ON']);

	$e_chox_off = is_plus_est($val['E_CHOX_OFF'], $val['CHOX_OFF']);
	$e_wheels_off =  is_plus_est($val['E_WHEELS_OFF'], $val['WHEELS_OFF']);
	$e_wheels_on = is_plus_est($val['E_WHEELS_ON'], $val['WHEELS_ON']);
	$e_chox_on = is_plus_est($val['E_CHOX_ON'], $val['CHOX_ON']);

	$query_insert = "INSERT INTO TBL_AC_MOVEMENT_PROD1
           		   ([COL_IDX]
		           ,[COL_KEY]
         		   ,[COL_CARRIER_CODE]
		           ,[COL_FLIGHT_NUMBER]
		           ,[COL_DEPARTURE_NUMBER]
		           ,[COL_DEPARTURE_STATION]
		           ,[COL_PLAN_DEPARTURE_DATE]
		           ,[COL_AIRCRAFT_REGISTRATION]
		           ,[COL_CHOX_OFF_DATE]
		           ,[COL_CHOX_OFF_TIME]
		           ,[COL_WHEELS_OFF_DATE]
		           ,[COL_WHEELS_OFF_TIME]
		           ,[COL_EST_DEP_DATE]
		           ,[COL_EST_DEP_TIME]
		           ,[COL_EST_WHEELS_OFF_DATE]
		           ,[COL_EST_WHEELS_OFF_TIME]
		           ,[COL_CHOX_ON_DATE]
		           ,[COL_CHOX_ON_TIME]
		           ,[COL_WHEELS_ON_DATE]
		           ,[COL_WHEELS_ON_TIME]
		           ,[COL_EST_ARR_DATE]
		           ,[COL_EST_ARR_TIME]
		           ,[COL_EST_WHEELS_ON_DATE]
		           ,[COL_EST_WHEELS_ON_TIME]
		           ,[COL_ARR_STATION]
		           ,[COL_FLIGHT_TYPE]
		           ,[COL_ARR_STAND_LOC]
		           ,[COL_DEP_STAND_LOC]
		           ,[COL_CANCEL_INDICATOR]
		           ,[COL_ARR_TERMINAL]
		           ,[COL_DEP_TERMINAL]
		           ,[COL_FLAG]
		           ,[COL_DUPL]
		           ,[REFERENCE_NUMBER]
			   	   ,[COL_ENTRY_DATE])
			   VALUES
        		   ('".$idx."'
				   ,'".$key."'
				   ,'GA'
		           ,'".$val['FL_NUM'] . $val['SUFFIX']."'
		           ,'".dep_number($val['DEP_NUM'])."'
		           ,'".$val['DEP_STAT']."'
		           ,'".$val['PLAN_DATE']."'
		           ,'".$val['AC_REG']."'
		           ,'".$chox_off['date']."'
		           ,'".$chox_off['time']."'
		           ,'".$wheels_off['date']."'
		           ,'".$wheels_off['time']."'
		           ,'".$e_chox_off['date']."'
		           ,'".$e_chox_off['time']."'
		           ,'".$e_wheels_off['date']."'
		           ,'".$e_wheels_off['time']."'
		           ,'".$chox_on['date']."'
		           ,'".$chox_on['time']."'
		           ,'".$wheels_on['date']."'
		           ,'".$wheels_on['time']."'
		           ,'".$e_chox_on['date']."'
		           ,'".$e_chox_on['time']."'
		           ,'".$e_wheels_on['date']."'
		           ,'".$e_wheels_on['time']."'
				   ,'".$val['ARR_STAT']."'
				   ,'".$val['FL_TYPE']."'
				   ,''
				   ,''
				   ,''
				   ,''
				   ,''
				   ,'0'
				   ,'".$dupl."'
				   ,'".$val['REF_NUM']."'
				   ,'".$val['ENT_DATE']."')";

	$insert = mssql_query($query_insert, $conn);
	//print_r($insert); exit();
	if ($insert) {
		return true;
		//echo 'insert to database success'; 
	}
	else {
		sleep(10);
		insert_data($conn, get_idx($conn), $val, $dupl, $key);
	}
}

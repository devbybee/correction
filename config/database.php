<?php

class Database {
	private $_host = '172.16.40.100';
	private $_user = 'usr-swf';
	private $_pass = 'p@ssw0rd';
	private $_db = 'db_MROSystem';

	public function connect_ods() {
		$db = "(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.25.192.212)(PORT = 1521)))(CONNECT_DATA=(SID=gasabre1)))" ;	
		$conn = OCILogon("jkttobps", "jkttobps17x", $db);

		if (!$conn) {
			//die('Cannot connect to database server');
			$e = oci_error();
			trigger_error(htmlentities($e['message']), E_USER_ERROR);
		}

		return $conn;
	}

	public function connect_mov() {
		$conn_db = mssql_connect($this->_host, $this->_user, $this->_pass);
		$select_db = mssql_select_db($this->_db, $conn_db);

		if(!$conn_db) {
			die('Failed to connect to database server' . mssql_get_last_message());
		}
		if(!$select_db) {
			die('Failed to connect to database' . mssql_get_last_message());
		}
		return $conn_db;
	}

	/*public function connect_mov() {
		$conn_db = mssql_connect('192.168.240.107', 'dev_dboard', 'devdboard');
		$select_db = mssql_select_db('db_dboard', $conn_db);

		if(!$conn_db) {
			die('Failed to connect to database server' . mssql_get_last_message());
		}
		if(!$select_db) {
			die('Failed to connect to database' . mssql_get_last_message());
		}
		return $conn_db;
	}*/
}
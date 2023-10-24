<?php
class mysqli_rc {
	protected $connection;
	protected $hostname;
	protected $database;
	protected $username;
	protected $password;
	protected $port;
	protected $socket;
	protected $debug;

	function __construct($params = array()) {
		if(array_key_exists('host', $params))
			$this->hostname	= $params['host'];
		if(array_key_exists('database', $params))
			$this->database = $params['database'];
		if(array_key_exists('user', $params))
			$this->username = $params['user'];
		if(array_key_exists('password', $params))
			$this->password = $params['password'];
                if(array_key_exists('port', $params))
                        $this->port = $params['port'];
                if(array_key_exists('socket', $params))
                        $this->socket = $params['socket'];
		if(array_key_exists('debug', $params))
			$this->debug 	= $params['debug'];
	}
	function open() {
		$this->connection = mysqli_connect($this->hostname, $this->username, $this->password, $this->database, $this->port, $this->socket);
		if($this->connection === false) {
			if($this->debug) {
				echo "Error: Unable to connect to MySQL." . PHP_EOL;
				echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
				echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
				die("Database Error 1");
				exit;
			}
			else {
				die("Could not connect to server. Try again after some time.");
			}
		}
	}
	function close() {
		mysqli_close($this->connection);
		$this->connection = null;
	}
	function affected_rows() {
		return mysqli_affected_rows($this->connection);
	}
	function insert_id() {
		return mysqli_insert_id($this->connection);
	}
	function num_rows($result) {
		return mysqli_num_rows($result);
	}
	function fetch($result) {
		return mysqli_fetch_array($result,MYSQLI_ASSOC);
	}
	function fetch_row($result) {
		return mysqli_fetch_row($result);
	}
	function rows($result) {
		return mysqli_num_rows($result);
	}
	function fetch_array($result) {
		$i=0;
		$temp=array();
		while($data = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
			$temp[$i]=$data;
			$i++;
		}
		return $temp;
	}
	function fetch_single_array($result,$field) {
		$i=0;
		$temp=array();
		while($data = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
			$temp[$i]=$data[$field];
			$i++;
		}
		return $temp;
	}
	function full_filter($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars(htmlspecialchars($data));
		$data = mysqli_real_escape_string($this->connection,$data);
		return $data;
	}
	function basic_filter($data) {
		$data = mysqli_real_escape_string($this->connection,$data);
		return $data;
	}
	function clean_output($string) {
		$string = stripslashes($string);
		$string = str_replace('&amp;','&',$string);
		return $string;
	}
	function query($sql) {
		$result=mysqli_query($this->connection,$sql);
		if($result === false && $this->debug)
		{
			echo mysqli_error($this->connection);
		}
		return $result;
	}
	function select($sql) {
		$results = $this->fetch_array($this->query($sql));
		return $results;
	}
	function insert($sql) {
		$this->query($sql);
		$primary_key = $this->insert_id();
		return $primary_key;
	}
	function execute($sql) {
		$this->query($sql);
		$results = $this->affected_rows();
		return $results;
	}
	function secure_insert($params = array()) {
		$table='';
		$values = array();
		if(array_key_exists('table', $params))
			$table=$params['table'];
		if(array_key_exists('values', $params))
			$values=$params['values'];
		$primary_key=0;
		$sql = '';
		$sql_column='';
		$sql_value='';
		$column = array_keys($values);
		for($i=0;$i<count($column);$i++) {
			if(!empty($sql_column))
				$sql_column.=', ';
			$sql_column.='`'.$column[$i].'`';
			$value = $values[$column[$i]];
			$value_validate = 'FF';
			if(is_array($value)) {
				if(count($value)>1)
					$value_validate = $value[1];
				$value = $value[0];
			}
			if(!empty($sql_value))
				$sql_value.=', ';
			if($value_validate=='BF') {
				$sql_value.="'".$this->basic_filter($value)."'";
			}
			else if($value_validate=='FF') {
				$sql_value.="'".$this->full_filter($value)."'";
			}
			else {
				$sql_value.="'".$value."'";
			}
		}
		if(!empty($sql_column) && !empty($sql_value)) {
			$sql = "INSERT INTO `$table`($sql_column) VALUES ($sql_value)";
			$this->query($sql);
			$primary_key = $this->insert_id();
		}
		return $primary_key;
	}
	function secure_update($params = array()) {
		$table='';
		$values = array();
		$condition = '';
		if(array_key_exists('table', $params))
			$table=$params['table'];
		if(array_key_exists('values', $params))
			$values=$params['values'];
		if(array_key_exists('condition', $params))
			$condition=$params['condition'];
		$results = 0;
		$sql_value ='';
		foreach ($values as $column => $value) {
			$value_validate = 'FF';
			if(is_array($value)) {
				if(count($value)>1)
					$value_validate = $value[1];
				$value = $value[0];
			}
			if(!empty($sql_value))
				$sql_value.=', ';
			if($value_validate=='BF')
				$sql_value.=" $column = '".$this->basic_filter($value)."'";
			else if($value_validate=='FF')
				$sql_value.=" $column = '".$this->full_filter($value)."'";
			else
				$sql_value.=" $column = '".$value."'";
		}
		if(!empty($sql_value)) {
			$this->query("UPDATE $table SET $sql_value $condition");
			$results = $this->affected_rows();
		}
		return $results;
	}
	function secure_select($params = array()) {
		$sql='';
		$values = array();
		if(array_key_exists('sql', $params))
			$sql=$params['sql'];
		if(array_key_exists('values', $params))
			$values=$params['values'];

		if(!empty($sql) && count($values)>0) {
			foreach ($values as $key => $value) {
    			$sql=str_replace($key,$this->full_filter($value),$sql);
			}
		}
		return $this->query($sql);
	}
	function create_slug($string = '', $tbl = array('tblname' => 'employer', 'slug' => 'employer_slug')) {
			//Unwanted:  {UPPERCASE} ; / ? : @ & = + $ , . ! ~ * ' ( )
			$string = strtolower($string);
			//Convert whitespaces and underscore to dash
			$string = preg_replace("/[\s_]/", "-", $string);
			//Strip any unwanted characters
			$string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
			//Clean multiple dashes or whitespaces
			$string = preg_replace("/[\s-]+/", " ", $string);
			//Convert whitespaces and underscore to dash
			$string = preg_replace("/[\s_]/", "-", $string);

			$string = rtrim($string, "-");

			$string_temp = $string;
			$flag = true;
			$i = 1;

			$tblname = $tbl['tblname'];
			$slug    = $tbl['slug'];

			while ($flag) {
					$res = $this->select("SELECT COUNT(*) AS cnt FROM `$tblname` WHERE `$slug` ='" . $this->full_filter($string_temp) . "'");
					if ($res[0]['cnt'] == 0)
							$flag = false;
					if ($flag) {
							$string_temp = $string . '-' . $i;
							$i++;
					}
			}
			return $string_temp;
	}
	function create_common_slug($string = '')
	{
		//Unwanted:  {UPPERCASE} ; / ? : @ & = + $ , . ! ~ * ' ( )
		$string = strtolower($string);
		//Convert whitespaces and underscore to dash
		$string = preg_replace("/[\s_]/", "_", $string);
		//Strip any unwanted characters
		$string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
		//Clean multiple dashes or whitespaces
		$string = preg_replace("/[\s-]+/", " ", $string);
		//Convert whitespaces and underscore to dash
		$string = preg_replace("/[\s_]/", "_", $string);
		$string_temp = $string;

		return $string_temp;
	}
	
	function crypt_password($password = '') {
			$hashed_password = crypt($password);
			return $hashed_password;
	}

	function verify_password($password = '', $hashed_password = '') {
			$result = false;
			if (password_verify($password, $hashed_password)) {
					$result = true;
			}
			return $result;
	}
}
$db = new mysqli_rc(array('host' => $config['db_host'], 'database' => $config['db_name'], 'user' => $config['db_user'], 'password' => $config['db_password'], 'port' => $config['port'], 'socket' => $config['socket'], 'debug' => $config['debug']));

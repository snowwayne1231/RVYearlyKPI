<?php
namespace Model;

if(is_null(BASE_PATH)){
	define('BASE_PATH', "../".dirname(__FILE__));
}

use \PDO;
use \Exception;

class DatabaseCenter {

	protected $DB;

	protected $CONF;

	private $limit_record = 1000;

	private $log_time;
	protected $_sql;
	public function __construct(){

		$this->CONF = include(BASE_PATH."/Config/db_config.php");
		$this->limit_record = $this->CONF['DB_CONFIG']['limit_record'];

	}

	private function buildPDOConnection(){

		date_default_timezone_set("Asia/Taipei");

		$PdoOptions = array(
				PDO::ATTR_PERSISTENT => false,
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_EMULATE_PREPARES => false,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
				PDO::ATTR_STRINGIFY_FETCHES => false,
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
		);try {

			$db_server = $this->CONF['DB_CONFIG']['server'];
			$db_content = $this->CONF['DB_CONFIG']['content'];
			$db_user = $this->CONF['DB_CONFIG']['user'];
			$db_pwd = $this->CONF['DB_CONFIG']['pwd'];


			$pdo = new PDO("mysql:host=$db_server;dbname=$db_content" , $db_user , $db_pwd , $PdoOptions);
			$pdo ->exec('SET CHARACTER SET utf8');


		} catch (PDOException $e) {
				throw new PDOException($e->getMessage());
		}
		return $pdo;
	}

	public function doSQL($sql, $bindData = []){
		if(empty($this->DB)){
			$this->DB = $this->buildPDOConnection();
		}
		$this->setSql($sql, $bindData);

		return $this->doPDO($sql, $bindData);
	}

	public function getPDO(){
		if(empty($this->DB)){
			$this->DB = $this->buildPDOConnection();
		}
		return $this->DB;
	}

	public function read($table, $match=null, $where=null, $order=null){
		if(!isset($table)){throw new Exception("Not Defined Table Name.");}
		if(isset($match)){
			if(is_array($match)&&count($match)>0){
				$cols = " ";
				foreach($match as $val){
					$cols.= $val.",";
				}
				$cols = preg_replace("/\,$/"," ",$cols);

			}else{
				$cols = " * ";
			}
		}else{
			$cols = " * ";
		}

		$case = $this->stringParseWhere($where);

		$orderBY = $this->stringParseOrderBy($order);

		$sql = "select $cols from $table $case $orderBY limit ".$this->limit_record;
		return $this->doSQL($sql);
	}

	public function add($table,$set = array()){
		if(!isset($table)){throw new Exception("Not Defined Table Name.");}
		if(is_array($set) && count($set) > 0){
			$cols = array();
			$vals = array();
			foreach($set as $col => $val){
				$val = $this->valueParseForSQL($val);
				array_push($cols,$col);
				array_push($vals,$val);
			}
			$allcol = join(",",$cols);
			$allval = join(",",$vals);
			$sql = "insert into ".$table." (".$allcol.") value (".$allval.");";
			// var_dump($sql);
			$this->doSQL($sql);

		}else{
			return false;
		}

		return $this->DB->lastInsertId();

	}

	public function addBatch($table,$sets){
		if(is_array($sets) && count($sets) > 0){
			// LG($sets);
			$cols = array();
			foreach($sets[0] as $col => $val){
				array_push($cols,$col);
			}
			// var_dump($cols);
			$values = array();
			foreach($sets as $col => &$val){
				foreach($val as &$vval){
					$vval = $this->valueParseForSQL($vval);
				}
				$values[] = "(".(join(',',$val)).")";
			}
			// dd($values);
			$allvalues = join(',',$values);

			$allcol = join(",",$cols);
			$sql = "insert into ".$table." (".$allcol.") value ".$allvalues.";";
			$this->doSQL($sql);
		}else{
			return false;
		}
		return $this->DB->lastInsertId();
	}

	public function update($table, $set=array(), $where=null){
		if(!isset($table)){throw new Exception("Not Defined Table Name.");}
		if(is_array($set) && count($set) > 0 && isset($where)){

			$case = $this->stringParseWhere($where);

			$updArray = array();
			foreach($set as $key => $val){
				$val = $this->valueParseForSQL($val);
				array_push($updArray, "$key = $val");
			}

			$updset = join(",",$updArray);
			$sql = "update $table set $updset $case";

			return $this->doSQL($sql);
		}else{
			return false;
		}

	}

	public function delete($table, $where=null){
		if(!isset($table)){throw new Exception("Not Defined Table Name.");}
		if(isset($where)){

			$case = $this->stringParseWhere($where);

			$sql = "delete from $table $case";

			$this->doSQL($sql);

		}else{
			return false;
		}
		return true;

	}


	public function flat($key = 'id') {
		$ary = [];
		foreach ($this->data as $loc) {
			$ary[] = $loc[$key];
		}
		return $ary;
	}


	protected function doPDO($sql, $bindData= [], $mode='array'){

		$this->log_time = microtime(true);
		try{

			$sth = $this->DB->prepare($sql);
			if ($bindData) {
				foreach ($bindData as $key => $value) {
					if (isset($value['length'])) {
						$sth->bindParam($key, $value['value'], $value['type'], $value['length']);
					} else {
						$sth->bindParam($key, $value['value'], $value['type']);
					}
				}
			}
			$sth->execute();
			if($mode=='object'){
				$result = $sth->fetchAll(PDO::FETCH_OBJ);
			}else{
				if( preg_match( '/select/i', $sth->queryString) ){
					$result = $sth->fetchAll(PDO::FETCH_ASSOC);
				}else if( preg_match( '/update/i', $sth->queryString) ){
					$result = $sth->rowCount();
				}else{
					$result = null;
				}
			}
			$sth->closeCursor();
			$this->writeDBLog($this->getSql());
		}catch (Exception $e){
			$msg = $e->getMessage();
			$this->writeDBLog($this->getSql(),$msg);
			if(IS_DEBUG_MODE){
				var_dump($this->getSql());
				var_dump($msg);
				exit;
			}else{
				$result = new \SplFixedArray(0);
				$result->sqlError = $msg;
			}
		}
		return $result;
	}

	protected function arrayParseForSQL($ary=array()){
		$loc = array();
		foreach($ary as $key => $val){
			$loc_val = $this->valueParseForSQL($val);
			$loc[$key] = $loc_val;
		}
		return $loc;
	}

	protected function valueParseForSQL($val){
		if(is_numeric($val)){ $val = (float)$val; }
		$type = gettype($val);
		$newValue = $val;
		switch($type){
			case "integer": case "double": break;
			case "array":
			case "object":
				$newValue = json_encode($val);
				$newValue = preg_replace("/([\'\\\\])/",'\\\$1',$newValue);          //符號 引號
				$newValue = "'".$newValue."'";
			break;
			case "boolean": $newValue = $newValue?1:0;break;
			case "string":
			default:
				$val = trim($val);
				if(preg_match('/^\(/',$val)){ return $val; }              //是故意夸
				$val_2 = preg_replace('/^[\\\'\"]+|[\'\"]+$/','',$val); //去頭尾引號
				// $val_3 = preg_replace("/[\\\']/","\'",$val_2);          //內容引號
				$val_4 = preg_replace("/([\'\\\\])/",'\\\$1',$val_2);          //符號 引號
				if($val_4=='NULL'){return $val_4;}
				// dd($val_4);
				$newValue = "'".( $val_4 )."'";
		}
		return $newValue;
	}

	protected function stringParseWhere($ary){
		if(!empty($ary)){
			if(is_array($ary)){
				$caseArray = array();
				foreach($ary as $key => $val){
					$val = $this->parseWhereValue($val);
					array_push($caseArray, "$key $val" );
				}
				$case = " where ".join(" and ",$caseArray);
			}else{  //string
				$case = $ary;
			}
		}else{
			$case = " ";
		}
		return $case;
	}

	private function parseWhereValue($v){
		if(is_array($v)){
			$symbol='in';
			$val = '('.join(',',$v).')';
		}else{  //string
			$val = trim($v);
			$match = preg_match("/^[\>\<\=i\s\!]/",$val); //have symbol
			if($match){
				$symbol = preg_replace("/([\>\<\=\!]+|in).*/","$1",$val);
				$pos = strpos($val,$symbol) + strlen($symbol);
				$val = substr($val,$pos);
			}else{
				$symbol = '=';
			}
		}
		$val = $this->valueParseForSQL($val);

		return $symbol.' '.$val;
	}

	protected function stringParseOrderBy($ord){
		if(isset($ord) && count($ord)>0){
			if(is_array($ord)){
				$caseArray = array();
				foreach($ord as $key => $val){
					$val = ($val=="DESC")?$val:"ASC";
					$val = $key." ".$val;
					array_push($caseArray,$val);
				}
				$order = " ORDER BY ".join(",",$caseArray);
			}else{
				$order = $ord;
			}
		}
		else{
			$order = " ";
		}
		return $order;
	}

	protected function writeDBLog($str,$error=''){
		$time_end = microtime(true);
		$spend_time = $time_end - $this->log_time;
		if(empty($error) && $spend_time <= $this->CONF['DB_CONFIG']['long_time'] ){return;}
		if(!empty($error)){$error=" - $error \n";$file = '/db_error';}else{$file = '/db_longtime';}

		$log = (empty($log))? new \Logging() : $log;
		$log->lfile( $file );
		$log->lwrite("\n----------------------------------- Command_START -----------------------------------\n ".$str."\n\r$error - Spend Time : ( ".$spend_time." )\n"."-----------------------------------  Command_END  -----------------------------------\n");

		$log->lclose();
	}

	protected function setSql($sql, $bindData)
	{
		$this->_sql = $this->parms($sql, $bindData);
		return $this;
	}

	public function getSql()
	{
		return $this->_sql;
	}

	public function set_limit_record($val){
		$this->limit_record = $val;
	}

	private  function parms($sql_string, $data) {
		$indexed= $data ==array_values($data);

		if ($data) {
			foreach($data as $k=>$v) {
				if(is_string($v)) {
					$v="'$v'";
				} else {
					if (isset($v['value'])) {

						if($indexed) {

							$sql_string=preg_replace('/\?/', $this->getSqlValue($v), $sql_string,1);
						} else {
							$sql_string=str_replace("$k", $this->getSqlValue($v),$sql_string);
						}
					}
				}

			}
		}
		return $sql_string;
	}
	/**
	 * 取得 sql bind 的value
	 * @method     getSqlValue
	 * @author Alex Lin <alex.lin@rv88.tw>
	 * @version    [version]
	 * @modifyDate 2017-08-09T16:12:30+0800
	 * @param      [type]                   $valueAry [description]
	 * @return     [type]                             [description]
	 */
	private function getSqlValue($valueAry)
	{
		$type = isset($valueAry['type']) ? $valueAry['type'] : \PDO::PARAM_STR;
		$return_value = $valueAry['value'];
		switch ($type) {
			case \PDO::PARAM_INT:
				$return_value = intval($return_value);
				# code...
				break;

			default:
				$return_value = '\''.$return_value.'\'';
				break;
		}
		return $return_value;
	}
}

?>

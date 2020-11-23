<?php
namespace Model\Business;

include_once __DIR__.'/../../PropertyObject.php';
include_once __DIR__.'/../../DatabaseCenter.php';

use \DateTime;
use \Model\PropertyObject;
use \Model\DatabaseCenter;
use \Exception;
use \Model\JsonGeneralFomat;

class DBPropertyObject extends PropertyObject
{

	public $table_name = "";

	public $tables_column = Array();

	protected $DB;

	protected $tmpWhere;

	protected $data;

	protected $map_key_cache;

	protected $maps;

	private $order_position = "_ORDER_POSITION";

	private $col_name_enable = "enable";

	private $order_timestamp;

	private $insertStorage=array();

	protected $_sql = '';

	//觀察者陣列
	protected $observeAry;
	//修改前快照物件
	protected $snapShotAry = [];
	//當前選擇的where條件
	protected $current_where = '';

	public function __construct($db=null){
		if(isset($db)){
			$this->DB = $db;
		}else{
			$this->DB = new DatabaseCenter();
		}
	}

	public function create($set=null){
		$this->callObserveAction('adding');
		$result = $this->DB->add($this->table_name, $set);
		if ($result) {
			$this->current_where = ['id' => $result];
			$this->callObserveAction('added');
		}
		return $result;
	}

	public function update($set=null, $where=null){
		if(is_numeric($where)) $where=array('id'=>$where);
		if ($where == null) {

		} else {
			$this->current_where = $where;
		}
		$this->setSnapShot($this->current_where);
		$this->callObserveAction('saving');
		$result = $this->DB->update($this->table_name, $set, $this->current_where);
		if ($result) {
			$this->callObserveAction('saved');
		}
		return $result;
	}

	public function select($match=null, $where=0, $order=null){
		if($where === 0){
			$where = $match;
			$match = null;
		}
		if(is_numeric($where)) $where = array('id'=>$where);
		$this->current_where = $where;

		$this->data = $this->DB->read($this->table_name, $match, $where, $order);
		$this->map_key_cache = "";
		return $this->data;
	}

	public function delete($a=null){
		if(is_numeric($a)){$a=array('id'=>$a);}
		$this->current_where = $a;
		$this->setSnapShot($a);
		$this->callObserveAction('deleting');
		$result = $this->DB->delete($this->table_name, $a);
		if ($result) {
			$this->callObserveAction('deleted');
		}
		return $result;
	}

	public function softDelete($a=null){
		$bl = false;
		foreach($this->tables_column as $v){
			if($v==$this->col_name_enable){ $bl=true;break; }
		}
		if($bl){
			$key_name = $this->col_name_enable;
			$res = $this->update( ["$key_name"=>0], $a);
		}else{
			$res = 0;
		}
		return $res;
	}

	public function add($a=null){
		$this->DB->add($this->table_name, $a);
		return $this;
	}



	public function addStorage($a=null){
		foreach($a as $key => &$v){
			if(gettype($v)=='string') {
				if (!preg_match("/\'+/",$v)) {
					$v="'$v'";
				}
			}
			if (is_null($v)) {
				$v= 'NULL';
			}
		}
		$this->insertStorage[] = $a;
		return $this;
	}

	public function addRelease(){
		$this->DB->addBatch($this->table_name, $this->insertStorage);
		return count($this->insertStorage);
	}

	public function read($a=null,$b=0,$c=null){

		if($b===0){$b=$a;$a=null;}
		if(is_numeric($b)){
			$where=array('id'=>$b);
		} else {
			$where = $b;
		}
		$this->current_where = $where;
		$this->data = $this->DB->read($this->table_name, $a, $where, $c);
		$this->map_key_cache = "";
		return $this;
	}

	public function sql($s, $bindData= []){
		$s =str_replace('{table}',$this->table_name,$s);

		$this->data = $this->DB->doSQL($s, $bindData);
		return $this;
	}

	public function map($key="id",$only=false,$array=false,$pointer=false,$hasStamp=true){
		if($this->map_key_cache == $key){return $this->maps;}
		$this->map_key_cache = $key;
		$this->maps = array();

		$akey = explode(',',$key);
		if(!(isset($this->data) && is_array($this->data))){
			$this->read();
		}

		$date = new DateTime();
		$this->order_timestamp = $date->getTimestamp();

		// $origin = $this->order_position.$this->order_timestamp;
		$origin = $this->order_position;

		foreach($this->data as $i => $val){
			$innerKeyAry=array();
			foreach($akey as &$v){
				$innerKeyAry[]=$val[$v];
			}

			$innerKey=join('-',$innerKeyAry);

			if(isset($innerKey)){
				// if($pointer){ &$val; }
				if($hasStamp){$val[$origin] = $i;}
				if(isset($this->maps[$innerKey]) && !$only){
					if( isset($this->maps[$innerKey][0]) ){
						if($pointer){
							$this->maps[$innerKey][] = &$val ;
						}else{
							array_push( $this->maps[$innerKey], $val );
						}
					}else{
						$tmp = $this->maps[$innerKey];
						$this->maps[$innerKey] = array( $tmp , $val );
					}
				}else{
					if($array){
						if($pointer){
							$this->maps[$innerKey] = array( &$val );
						}else{
							$this->maps[$innerKey] = array( $val );
						}
					}else{
						$this->maps[$innerKey] = $val;
					}
				}
			};
		}
		return $this->maps;
	}

	public function cmap($a='id',$b=true, $c=false){
		$map = $this->map($a,$b,$c,false,false);
		foreach($map as &$m){
			if( $c ){
				foreach($m as &$v){ unset($v[$a]); }
			}else{
				unset($m[$a]);
			}
		}
		return $map;
	}
	public function amap($a='id'){
		$map = $this->map($a,false,true,false,false);
		foreach($map as &$m){ unset($m[$a]); }
		return $map;
	}

	public function join($condition = array(), $data, $matter=true){
		if(isset($data) && is_array($data) && is_array($condition) && count($condition) > 0){
			$ccl = count($condition);

			$a = 0;
			while($a < count($this->data) ){

				$b = $this->data[$a];$isMattch = false;

				foreach($data as $c => $d){
					$ci = 0;
					foreach($condition as $key => $key_2){
						if($b[$key]==$d[$key_2]){ $ci++; }
					}
					if($ci>=$ccl){
						// $this->data[$a] = array_merge($b, $d);
						$this->data[$a] = array_merge($d,$b);
						$isMattch = true;break;
					}
				}

				if($matter && !$isMattch){
					array_splice($this->data, $a, 1);
				}else{
					$a++;
				}

			}

		}
		return $this->data;
	}

	private $join_array=[];
	public function leftJoin($table, $condition){
		$join_array[]=['table'=>$talbe,'condition'=>$condition];
	}

	public function search($ary){
		if( !(isset($ary) && is_array($ary)) ){throw new Exception("Search Function Can't Receive Wrong Array.");}
		if(count($ary)>0){

			$tmp = array();
			$searchArray = array();
			foreach($ary as $whereKey => &$whereVal){
				if(preg_match("/[\<\>\=]+/",$whereVal)){
					$symbol = preg_replace('/.*?([\<\>\=]+).*/','$1',$whereVal);
					$val = preg_replace('/[\<\>\=]+/','',$whereVal);
				}else{
					$symbol = '==';
					$val = $whereVal;
				}
				if(gettype($val)=='string'){$val="'$val'";}
				$searchArray[$whereKey] = $symbol.$val;
			}

			foreach($this->data as $key => &$val){
				$loc = true;
				foreach($searchArray as $whereKey => &$where){
					if(gettype($val[$whereKey])=='string'){
						$render = "'".$val[$whereKey]."'".$where;
					}else{
						$render = $val[$whereKey].$where;
					}
					if(!($this->doOperatorsWithString($render))){
						$loc=false;break;
					}
				}

				if($loc){
					array_push($tmp,$val);
				}

			}
			return $tmp;
		}else{
			return $this->data;
		}
	}

	public function addData($record){

		array_push($this->data, $record);

		$this->clearCache();
	}

	public function clearCache(){
		$this->map_key_cache = "";
		return $this;
	}

	public function invertColumn($ary){
		$new = $this->tables_column;
		if(is_array($ary)){
			foreach($ary as &$v){
				$key = array_search($v,$new);
				unset($new[$key]);
			}
		}else{
			$key = array_search($ary,$new);
			unset($new[$key]);
		}
		return $new;
	}

	public function trueColumn(&$ary){
		foreach($ary as $k => &$v){
			if(!in_array($k,$this->tables_column)){
				unset($ary[$k]);
			}
		}
		return $ary;
	}

	public function getTiny(){
		$res = array();
		foreach($this->data as $row){
			foreach($row as $firstKey => $firstVal){
				array_push($res,$firstVal);
				break;
			}
		}
		return $res;
	}

	public function find($match, $where) {
		$data = $this->select($match, $where);
		if (count($data) == 1) {
			$data = $data[0];

			if (count($match) == 1) {
				return $data[$match[0]];
			}
		}
		return $data;
	}

	protected function get_data(){
		return isset($this->data) ? $this->data : $this->select() ;
	}
	protected function get_maps(){
		return $this->maps;
	}
	protected function get_table(){
		return $this->table_name;
	}
	protected function get_col(){
		return $this->tables_column;
	}
	protected function get_column(){
		if(!isset($this->data)){ $this->read(); }
		$col = array();
		foreach($this->data[0] as $key => $val){
			array_push($col,$key);
		}
		return $col;
	}
	protected function get_origin(){
		// return $this->order_position.$this->order_timestamp;
		return $this->order_position;
	}
	protected function get_DB(){
		return $this->DB;
	}

	protected function set_limit($val){
		$this->DB->set_limit_record($val);
		return $this;
	}

	protected function parseForTableColumn($ary,$col=null){
		$result = null;
		if(empty($col)){
			$col = $this->tables_column;
		}
		if(isset($ary) && isset($col)){
			$keys = Array();
			$values = Array();
			foreach($ary as $key => $val){
				if(in_array($key,$col)){
					array_push($keys,$key);
					array_push($values,$this->parseForSQL($val));
				}else{
					$result = null;break;
				}
			}
			$result = Array(
				"keys" => $keys,
				"values" => $values
			);
		}
		return $result;
	}

	protected function parseToArray($loc){
		return json_decode(json_encode($loc),true);
	}

	protected function doOperatorsWithString($str){
		return eval("return ($str);");
	}

	public function getSql()
	{
		return $this->DB->getSql();
	}

	public function observe($class)
	{
		$className = is_string($class) ? $class : get_class($class);
		$cls = new $className;
		$this->observeAry[] = $cls;
	}

	/**
	 * 呼叫觀察者 行為
	 * @method     callObserveAction
	 * @author Alex Lin <alex.lin@rv88.tw>
	 * @version    [version]
	 * @modifyDate 2017-08-07T14:55:19+0800
	 * @param      [type]                   $action 行為分為:
	 *                                                        saved: 編輯後
	 *                                                        deleted: 刪除後
	 * @return     [type]                           [description]
	 */
	protected function callObserveAction($action)
	{
		if ($this->observeAry) {
			foreach ($this->observeAry as $key => $observe) {
				if (method_exists($observe, $action)) {
					$observe->$action($this);
				}
			}
		}
	}

	protected function setSnapShot($where)
	{
		if ($this->observeAry) {
			$data = $this->DB->read($this->table_name, [], $where);
			$data = array_pop($data);
			$this->snapShotAry = $data;
		}

	}

	public function getSnapShot()
	{
		return $this->snapShotAry;
	}

	public function getCurrentWhere()
	{
		return $this->current_where;
	}

	public function getCurrentWhereData()
	{
		$data = $this->DB->read($this->table_name, [], $this->current_where);
		$data = array_pop($data);
		return $data;
	}


	/**
	 *  直接印出 錯誤訊息 json
	 */
	public function error($res = 'Unknow'){
		if(class_exists('ApiCore')){
			$for = new JsonGeneralFomat();
			$for->setStatus( JsonGeneralFomat::$INPUT_REJECT );
			$for->setMsg($res);
			print $for->getResult();exit;
		}else{
			throw new Exception($res);
		}
	}

	/**
	 *  執行檢查資料數 如果沒有 則印錯誤訊息
	 */
	public function check($count = 0, $msg=false){
		if( !is_numeric($count) && is_string($count) ){ $msg=$count; $count=0; }
		if($count<=0){
			$count = abs($count);
			$bl = count($this->data) <= $count;
		}else{
			$bl = count($this->data) >= $count;
		}
		if( $bl ){
			$str = $msg ? $msg : "Model : ( ".get_class($this)." ) Data Error. Count :( ".$count." )";
			$this->error($str);
		}
		return $this;
	}

	public function get_Count(){
		if($this->data){
			return count($this->data);
		}else{
			return 0;
		}
	}

	public function copyTmpByWhere($where = null) {
		$data = [];
		if (empty($where)) {
			$this->sql('DROP TABLE IF EXISTS {table}_tmp');
			$this->sql('CREATE TABLE {table}_tmp SELECT * FROM {table}');
			$data = $this->select();
		} else {
			$this->sql('CREATE TABLE IF NOT EXISTS {table}_tmp LIKE {table}');
			$this->sql('TRUNCATE {table}_tmp');
			$data = $this->select([], $where);
			
			if (count($data) > 0) {
				$fist_data = $data[0];
				$arrUPDATE = [];
				$fieldKeys = array_keys($fist_data);
				$fieldName = "(" . implode(", ", $fieldKeys) . ")";

				foreach ($data as $d) {
					foreach ($d as $key => $val) {
						if (gettype($val) == 'array') {
							$d[$key] = json_encode($val);
						}
					}
					$arrUPDATE[] = "('" . implode("', '", $d) . "')";
				}
			}

			$strUPDATE = implode(',', $arrUPDATE);

			$this->sql("INSERT INTO {table}_tmp $fieldName VALUES $strUPDATE");
		}

		return $data;
	}

}

?>
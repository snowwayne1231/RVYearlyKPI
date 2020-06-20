<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class Department extends DBPropertyObject{

	//實體表 :: 單表
	public $table_name = "rv_department";

	public static $SubTeam = "subTeam";

	protected static $CeoStaffId;

	const DEPART_LEVEL_OPS = 1;
	const DEPART_LEVEL_DEVELOPMENT = 2;
	const DEPART_LEVEL_OFFICE = 3;
	const DEPART_LEVEL_GROUP = 4;

	//欄位
	public $tables_column = Array(
		"id",
		"lv",//部門的等級 1.運維 2.部門 3.處 4.組
		"unit_id",//部門代號 不可重覆
		"name",//部門名稱
		"supervisor_staff_id",//隸屬上層
		"manager_staff_id",//部門主管
		'duty_shift',
		'upper_id',//部門結構上層
		'enable',//是否開啟
		'update_date'
	);

	//override
	public function read($a=null,$b=0,$c=null){
		$order = isset($c)?$c:' order by unit_id asc';
		return parent::read($a,$b,$order);
	}

	//override
	public function select($a=null,$b=0,$c=null){
		$order = isset($c)?$c:' order by unit_id asc';
		return parent::select($a,$b,$order);
	}

	//取得下層單位
	public function getLower($id){
		$lower = array();
		$data = $this->data;
		foreach($data as $v){
			if($v['upper_id']==$id){ $lower[$v['id']] = $v; }
		}
		return $lower;
	}

	//取得所有下層單位的ID
	public function getLowerIdArray($id, $includeMe = false){
		$lower = array();
		if($includeMe) array_push($lower, $id);
		if (count($this->data) == 0) {
			$this->read();
		}

		$inner_id = $id;
		$pos = 0;
		do{
			foreach($this->getLower($inner_id) as $key => $val){
				array_push($lower, $key);
			}
			$inner_id = (isset($lower[$pos])) ? $lower[$pos] : 0;
			$pos++;
		}while($pos <= count($lower));

		$lower = array_unique($lower);//去除重複值

		return $lower;
	}

	//取得所有下層單位的資料
	public function getLowerArray($id, $includeMe = false){
		$arrLowerID = $this->getLowerIdArray($id);
		if($includeMe) array_push($arrLowerID, $id);
		//取得資料
		$lowerData = array();
		foreach($arrLowerID as $lower_id){
			$lowerData[$lower_id] = $this->select(array('id' => $lower_id))[0];
		}
		return $lowerData;
	}

	/**
	 * 判斷 sub_id 是不是 id 的下層單位
	 * @param  integer $id     部門ID
	 * @param  integer $sub_id 子部門ID
	 * @return boolean 判斷結果
	 */
	public function isLower($id,$sub_id){
		$ary = $this->getLowerIdArray($id);
		return in_array($sub_id,$ary);
	}

	/**
	 * 依 主管ID 取得上層單位負責人
	 * @param  integer $manager_id 主管ID
	 * @return integer 上層單位負責人ID
	 */
	public function getSuperWithManager($manager_id){
		if(!$manager_id){
			return $this->select(array('id'),array('upper_id'=>0))[0]['id'];
		}else{
			$maps = $this->map('manager_staff_id');
			return $maps[$manager_id]['supervisor_staff_id'];
		}
	}

	//用主管 取得上層單位佬大
	public function getSuperArrayWithManager($manager_id, $end_id=0, $filter_self=false){
		$a = $manager_id;
		if($end_id==0){
			$end_id = $this->getCeoStaffId();
		}
		$map = $this->map('manager_staff_id');
		$res = array();
		do{
			$b = $a;
			array_push($res, $b);
			if($a == $end_id) break;
			$a = isset($map[$b]['supervisor_staff_id']) ? $map[$b]['supervisor_staff_id'] : $end_id;
		}while(!($a == $b));

		if($filter_self){
			foreach($res as $i => $v){
				if($v == $manager_id){
					array_splice($res,$i,1);
					break;
				}
			}
		}
		return $res;
	}

	/**
	 * 用id 取得上層單位ID Array
	 * @param  integer $id                部門ID
	 * @param  boolean $filter_no_manager 是否要過濾掉主管(預設為不過濾)
	 * @param  boolean $filter_no_self    是否要過濾掉自己部門(預設為不過濾)
	 * @return array   上層單位ID
	 */
	public function getUpperIdArray($id,$filter_no_manager=false,$filter_no_self=false){
		$a = $id;
		$map = $this->map();
		$res = array();
		do{
			$b = $a;
			if( isset($map[$b]) ){
				if($filter_no_manager){
					$map[$b]['manager_staff_id']>0 && array_push($res, $b);
				}else{
					array_push($res, $b);
				}
			}else{
				break;
			}
			$a = $map[$b]['upper_id'];
		}while(!($a == $b));
		if($filter_no_self==true && isset($res[0]) && $res[0]==$id){ array_splice($res, 0, 1); }
		return $res;
	}

	/**
	 * 用id 取得上層單位 ID, lv, 名稱
	 * @param  integer $id                部門ID
	 * @param  boolean $filter_no_manager 是否要過濾掉主管(預設為不過濾)
	 * @param  boolean $filter_no_self    是否要過濾掉自己部門(預設為不過濾)
	 * @return array   上層單位ID
	 */
	public function getUpperArray($id,$filter_no_manager=false,$filter_no_self=false){
		$tmp = $this->getUpperIdArray($id,$filter_no_manager,$filter_no_self);
		$return = [];
		$map = $this->map();
		foreach ($tmp as $key => $value) {
			$return[$map[$value]['lv']] = [
				'id' => $map[$value]['id'],
				'lv' => $map[$value]['lv'],
				'name' => $map[$value]['name'],
			];
		}
		return $return;
	}

	//建立樹狀結構
	public function getTree($in=null){
		$data = ($in)?$in:$this->data;
		// $map = $this->map();
		$sub = self::$SubTeam;
		$tree = array( 0=>array($sub=>array() ) );
		$lv = 1;
		$maxLv = 5;

		$layer = array( &$tree );

		while($lv<$maxLv){

			$layer_next = array();

			foreach($data as $order=>$v){
				if(!($v['lv']==$lv)) continue;
				$up_id = $v['upper_id'];

				foreach($layer as $id=>$loc){
					$singleLayer = &$layer[$id];
					if( isset($singleLayer[ $up_id ]) ){
						$singleLayer[ $up_id ][$sub][$v['id']] = array( 'self'=>$v );
						$layer_next[] = &$singleLayer[$up_id][$sub];
					}
				}
			}
			$layer = $layer_next;
			$lv++;
		}

		return $tree[0][$sub];
	}

	/**
	 * 刷新 上層主管ID (supervisor_staff_id )
	 * @return object
	 */
	public function refreshRelation(){
		$sql = 'UPDATE {table} as a
		left join (select id, upper_id, manager_staff_id as mid from {table} ) as b on a.upper_id = b.id
		left join (select id, upper_id, manager_staff_id as mid from {table} ) as c on if(b.upper_id>0,b.upper_id,0) = c.id
		left join (select id, upper_id, manager_staff_id as mid from {table} ) as d on if(c.upper_id>0,c.upper_id,0) = d.id
		set a.supervisor_staff_id = if(b.mid>0, b.mid, if(c.mid>0,c.mid, if(d.mid>0, d.mid, a.manager_staff_id) ) ) ';
		$this->sql($sql);
		return $this;
	}

	/**
	 * 取得 CEO 的 職員ID
	 * @return integer 職員ID
	 */
	public function getCeoStaffId(){
		if( isset($this->CeoStaffId) ){
			$id = $this->CeoStaffId;
		}else{
			$upMap = $this->map('upper_id',true);
			$id = $upMap[0]['manager_staff_id'];
			$this->CeoStaffId = $id;
		}
		return $id;
	}

	/**
	 * 取得指定職員，擔任哪些部門的主管
	 * @param  integer $staff_id 職員ID
	 * @return array 部門ID
	 */
	public function getListWithManager($staff_id){
		$list = array();
		$data = $this->select(['manager_staff_id', 'id', 'upper_id', 'supervisor_staff_id'], []);
		foreach($data as $v){
			if($v['manager_staff_id']== $staff_id) $list[$v['id']] = $v;
		}
		return $list;
	}

}
?>

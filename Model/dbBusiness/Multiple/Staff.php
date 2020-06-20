<?php
namespace Model\Business\Multiple;

include_once __DIR__.'/_MultipleSets.php';
include_once __DIR__.'/../Staff.php';
include_once __DIR__.'/../StaffPost.php';
include_once __DIR__.'/../StaffStatus.php';
include_once __DIR__.'/../StaffTitleLv.php';
include_once __DIR__.'/StaffDepartment.php';
include_once __DIR__.'/../RecordStaff.php';
// include_once __DIR__.'/../Department.php';

use \Exception;

/*
Staff
*/
class Staff extends MultipleSets{

	protected $staff;
	protected $post;
	protected $title;
	protected $status;
	protected $department;
	protected $record;

	// protected $team;

	public function __construct(){
		$this->staff = new \Model\Business\Staff();
		$this->post = new \Model\Business\StaffPost();
		$this->title = new \Model\Business\StaffTitleLv();
		$this->status = new \Model\Business\StaffStatus();
		$this->department = new \Model\Business\Multiple\StaffDepartment();
		$this->record = new \Model\Business\RecordStaff();
	}

	public function admission($set, $admin=0){
		$set = $this->getData($set);

		$id = $this->staff->create($set);
		$new_one = $this->staff->select($id);
		$this->record->add( array(
			'operating_staff_id' => $admin,
			'staff_id' => $id,
			'doing' => 1,
			'changed_json' => json_encode(['id'=>$id])
		) );
		return $new_one;
	}

	public function insertStorage($set){
		$set = $this->getData($set);
		$this->staff->addStorage($set);
		return $this;
	}
	public function releaseInsertStorage(){
		return $this->staff->addRelease();
	}

	public function getData(&$set){
		$set = $this->staff->trueColumn($set);

		$post_map           = $this->post->map();
		$title_map          = $this->title->map();
		$status_map         = $this->status->map();
		$set['post']        = $post_map[$set['post_id']]['name'];
		$set['title']       = $title_map[$set['title_id']]['name'];
		$set['lv']          = $title_map[$set['title_id']]['lv'];
		$set['status']      = $status_map[$set['status_id']]['name'];
		$set['first_day']   = empty($set['first_day']) ? '0000-00-00':$set['first_day'];
		$set['last_day']    = empty($set['last_day']) ? '0000-00-00':$set['last_day'];
		$set['update_date'] = empty($set['update_date']) ? '0000-00-00':$set['update_date'];

		return $set;
	}

	public function updateByAdmin($set,$id,$admin){
		$post_map   = $this->post->map();
		$title_map  = $this->title->map();
		$status_map = $this->status->map();

		if( isset($set['post_id']) ){
			$set['post'] = $post_map[$set['post_id']]['name'];
		}
		if( isset($set['status_id']) ){
			$set['status'] = $status_map[$set['status_id']]['name'];

			if($set['status_id'] == 4){//離職
				if(empty( $set['last_day'])) $set['last_day'] = date('Y-m-d');
				//$set['is_leader'] = 0;
			}
		}

		if( isset($set['title_id']) ){
			$title = $title_map[$set['title_id']];
			$set['title'] = $title['name'];
			$set['lv'] = $title['lv'];

			/*
			if($title['lv'] > 0 && $title['lv'] <= 4){
				$set['is_leader'] = 1;
			}else{
				$set['is_leader'] = 0;
			}
			*/
		}

		if( empty($set['passwd']) ){
			unset($set['passwd']);
		}

		$res = $this->staff->update($set,$id);
		if($res){
			$this->record->add( array(
				'operating_staff_id' => $admin,
				'staff_id' => $id,
				'doing' => 2,
				'changed_json' => json_encode($set)
			) );
		}
		return $res;
	}

	public function select($a=null,$b=0,$c=null){
		$this->data = $this->staff->select($a,$b,$c);
		return $this->data;
	}

	public function read($a=null,$b=0,$c=null){
		$this->select($a,$b,$c);
		return $this;
	}

	public function getMd5PassWord($pwd){
		return $this->staff->getMd5PassWord($pwd);
	}

	protected function get_inner(){
		return $this->staff;
	}
	protected function get_post(){
		return $this->post;
	}
	protected function get_title(){
		return $this->title;
	}
	protected function get_status(){
		return $this->status;
	}

}
?>

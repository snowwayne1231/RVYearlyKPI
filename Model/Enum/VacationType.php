<?php
namespace Model\Enum;


class VacationType {
  
  public $one=true;
  
  private static $data_info = ['number','name','unit','common_key','is_work','is_hide'];  //子項目編號,假別名稱,請假單位,常用英文名,是否算在實際工時,是否不能被看見
  private static $data = [
    ['011', '事假', 1, 'leave', 0, 0],
    ['012', '家庭照顧假', 1, 'take_care', 0, 1],
    ['013', '曠職', 1, 'absent', 0, 0],
    ['02', '病假(不扣薪)', 4, 'paysick', 0, 0],
    ['02-', '全薪病假', 4, 'paysick', 0, 0],
    ['021', '生理假(不扣薪)', 4, 'physiology', 0, 1],
    ['023', '安胎休養請假(不扣薪)', 4, 'maternity', 0, 1],
    ['03', '病假(半薪)', 4, 'sick', 0, 0],
    ['031', '生理假(半薪)', 4, 'physiology', 0, 1],
    ['033', '安胎休養請假(半薪)', 4, 'maternity', 0, 1],
    ['041', '特別休假', 4, 'special_relax', 1, 0],
    ['051', '公假', 4, 'worked', 1, 0],
    ['061', '公傷病假', 4, 'sick', 0, 0],
    ['071', '婚假', 8, 'marry', 0, 0],
    ['091', '陪產假', 8, 'maternity', 0, 1],
    ['101', '喪假(8日)', 4, 'death', 0, 0],
    ['102', '喪假(6日)', 4, 'death', 0, 0],
    ['103', '喪假(3日)', 4, 'death', 0, 0],
    ['111', '天然災害假', 1, 'disaster', 1, 0],
    ['112', '防疫隔離', 1, 'isolation', 0, 0],
    ['121', '出差假', 4, 'worked', 1, 0],
    ['131', '加班補休', 1, 'relax', 1, 0],
    ['141', '無薪假', 8, 'nopay', 0, 0],
    ['151', '選舉假', 1, 'vote', 0, 0],
    ['161', '公出', 1, 'worked', 1, 0],
    ['17', 'Team Buiding', 1, 'buiding', 1, 0],
    ['171', '員工旅遊', 1, 'buiding', 1, 0],
    ['18', '產檢假', 4, 'maternity', 0, 1],
    ['19', '病假(工讀轉正)', 4, 'sick', 0, 0],
    ['811', '產假(滿年資半年)', 8, 'maternity', 0, 1],
    ['812', '產假1(滿年資半年)', 8, 'maternity', 0, 1],
    ['813', '產假2(滿年資半年)', 8, 'maternity', 0, 1],
    ['814', '產假3(滿年資半年)', 8, 'maternity', 0, 1],
    ['821', '產假(未滿年資半年)', 8, 'maternity', 0, 1],
    ['822', '產假1(未滿年資半年)', 8, 'maternity', 0, 1],
    ['823', '產假2(未滿年資半年)', 8, 'maternity', 0, 1],
    ['824', '產假3(未滿年資半年)', 8, 'maternity', 0, 1]
  ];
  
  //代號, 上班時間, 下班時間, 備註, 休息時間起, 休息時間尾
  private static $work_class = [
    ['A01',	0900,1800,'彈性上下班',	1300,1400	],
    ['A02',	1000,1900,'彈性上下班',1400,1500	],
    ['A03',	1000,1900,'彈性上下班',1400,1500	],
    ['B01',	0800,1600,'',	0,0],
    ['B02',	0900,1800,'',1300,1400],	
    ['B03',	0930,1830,'',1330,1430],
    ['B04',	1500,2300,'',	0,0],
    ['B05',	2300,0800,'',0300,0400],	
    ['B06',	2300,0700,'',	0,0],
    ['B07',	1330,2130,'',	0,0],
    ['B08',	1400,2300,'',1800,1900],	
    ['D02',	0700,1500,'',	0,0],
    ['D03',	1500,2300,'',	0,0],
    ['D04',	2300,0700,'',0,0]
  ];
  private $data_count;
  private $class_data;
  
  private $pointer=[];
  private $cache_value;
  
  public function __construct($class_code=null){
    $this->data_count = count(self::$data_info);
    if(isset($class_code)){
      foreach(self::$work_class as $wc){
        if($wc[0]==$class_code){$this->class_data=$wc;break;}
      }
    }
    if(empty($this->class_data)){$this->class_data=self::$work_class[0];}
  }
  
  public function itName($name){
    $rms = explode('、',$name);
    $rms = array_filter($rms);
    $this->cache_value=$name;
    $this->one = count($rms)==1;
    $this->pointer=[];
    foreach($rms as $r){
      $this->pointer[] = $this->pointIt($r);
    }
    return $this;
  }
  
  public function pointIt($value,$index=1){
    foreach(self::$data as $v){
      if(strpos($value,$v[$index])!==false){$v[$this->data_count]=$value;return $v;}
    }return null;
  }
  
  public function isHide(){
    foreach($this->pointer as $p){ if($p[5]==1){return true;} }return false;
  }
  
  public function isWork($data=null){
    return (isset($data)?$data[4]:$this->pointer[0][4])==1;
  }
  
  public function getKey($data=null){
    return isset($data)?$data[3]:$this->pointer[0][3];
  }
  
  
  public function getTime($max_time=8){
    $res = []; $total=0;
    $cv = $this->cache_value;
    $launch_time = round($this->class_data[4]/100);
    foreach($this->pointer as $tp){
      if(empty($tp)){continue;}
      $min_unit = $tp[2];
      $cv = $tp[$this->data_count];
      
      $vvh = preg_replace('/[^\(]*?\(([\d][^\)]+).*/','$1',$cv);
      $vvh = preg_replace('/^[\D]+/','',$vvh);
      $vvhas = explode('-',$vvh);
      if( count($vvhas)!=2){return null;}
      $sta = explode(':',$vvhas[0]);
      $end = explode(':',$vvhas[1]);
      $sta_hour = (int)$sta[0];
      $sta_mint = (int)$sta[1];
      $end_hour = (int)$end[0];
      $end_mint = (int)$end[1];
      
      $vh = (float)($end_hour - $sta_hour);
      if($vh<0){ $vh+=24; }
      
      $mint = $end_mint - $sta_mint;
      
      $vh += $mint>=30 ? 0.5 : ( $mint<=(-30) ? -0.5 : 0);
      
      $surplus = ($sta_hour<=$launch_time && $end_hour>$launch_time)?1:0; //有跨午休
      if(($vh-$surplus)%$min_unit!=0){   //請假不准時
        $surplus = -($vh%$min_unit);
      }
      
      
      $total+=$vh;
      $res[]=['key'=>$this->getKey($tp),'work'=>$this->isWork($tp),'time'=>$vh,'surplus'=>$surplus];
    }
    
    if($total!=$max_time){   //總合不是正常請假時數
      if(count($res)==1){
        $res[0]['time']=$max_time;
      }else{
        $tmp_total_surplus = $total-$max_time;
        // dd($res);
        foreach($res as &$r){
          $r['time']-= $r['surplus'];
          $total-= $r['surplus'];
          if($total==$max_time){break;}
        }
        
        if($total!=$max_time){   //還是批配不上
          $tmp_i = 0;
          foreach($res as $r_i => $rr){   //找到最大的數字
            if($rr['time']>$res[$tmp_i]['time']){$tmp_i=$r_i;}
          }
          $res[$tmp_i]['time']-=$tmp_total_surplus; //從最大的減掉差距
        }
        
      }
    }
    
    return $res;
  }
  
  
}
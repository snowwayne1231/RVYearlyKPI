<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

/**
* 
*/
class YearPerformanceReportDistributionRate extends DBPropertyObject
{
	//實體表 :: 單表
  	public $table_name = "rv_year_performance_report_distribution_rate";
	
  	//欄位
  	public $table_column = Array(
  		'id',
  		'lv',
  		'name',
  		'score_limit',
  		'rate_least',
  		'rate_limit',
  		'enable'  // 0: 關閉, 1: 啟用
  	);

	public function __construct()
	{
		parent::__construct();
	}
}
?>
<?php
namespace Home\Model;
use Think\Model;
class GetDBModel extends Model
{
	protected $trueTableName  = 'TABLES';
	protected $dbName = 'information_schema';
	public function __construct() 
	{
        parent::__construct();
	}
	public function dbName($where)
	{
		return $this->field('TABLE_SCHEMA,TABLE_NAME')->where($where)->select();
	}

}
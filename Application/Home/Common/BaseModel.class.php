<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/4/21
 * Time: 14:42
 */

namespace Home\Common;


use Think\Model;

class BaseModel extends Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function formatSql($sql, $where)
	{

	}
}
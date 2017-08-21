<?php
namespace Home\Model;
use Think\Model;
class DriverModel extends Model
{
	protected $dbName;
	protected $tablePrefix;
	public function __construct($dbName,$tablePrefix) 
	{
		$this->dbName = $dbName;
		$this->tablePrefix = $tablePrefix;
        parent::__construct();
	}
	/**
	*获取司机单个信息
	* $where 条件 array
	* $field 指定字段
	*/
	public function getInfo($where,$field = "*")
	{
		return $this->field($field)->where($where)->find();
	}
	/**
	*获取司机信息
	* $where 条件 array
	* $field 指定字段
	*/
	public function getData($where,$field = "*")
	{
		return $this->field($field)->where($where)->select();
	}
	/**
	*添加司机信息
	* $array 添加数据 array
	*/
	public function addDriver($array)
	{
		return $this->add($array);
	}
	/**
	*修改司机信息
	* $where 条件 array
	* $array 修改数据 array
	*/
	public function editDriver($where,$array)
	{
		return $this->where($where)->data($array)->save();
	}
	/**
	*删除司机信息
	* $id 司机主键
	*/
	public function delDriver($id)
	{
		return $this->delete($id);
	}


	/**
	*根据车辆id获取司机信息
	* $where 条件
	*/
	public function getDriverById($where)
	{
		return M("$this->dbName.driver")
		->alias('d')
		->join("$this->dbName.tp_car_driver_rel as cdr on d.driver_id = cdr.driver_id")
		->field('car_id as driver_id,name as driver_name')
		->where($where)->select();
	}

}
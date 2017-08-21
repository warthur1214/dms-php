<?php
namespace Home\Model;
use Think\Model;
class CarGroupModel extends Model
{
	protected $dbName;
	protected $tablePrefix;
    protected $trueTableName  = 'tp_group';
	public function __construct($dbName,$tablePrefix) 
	{
		$this->dbName = $dbName;
		$this->tablePrefix = $tablePrefix;
        parent::__construct();
	}
	/**
	*获取车辆分组单个信息
	* $where 条件 array
	* $field 指定字段
	*/
	public function getInfo($where,$field = "*")
	{
		return $this->field($field)->where($where)->find();
	}
	/**
	*获取车辆分组信息
	* $where 条件 array
	* $field 指定字段
	*/
	public function getData($where,$field = "*")
	{
		return $this->field($field)->where($where)->select();
	}
	/**
	*添加车辆分组信息
	* $array 添加数据 array
	*/
	public function addGroup($array)
	{
		return $this->add($array);
	}
	/**
	*修改车辆分组信息
	* $where 条件 array
	* $array 修改数据 array
	*/
	public function editGroup($where,$array)
	{
		return $this->where($where)->data($array)->save();
	}
	/**
	*删除车辆分组信息
	* $id 车辆分组主键
	*/
	public function delGroup($id)
	{
		return $this->delete($id);
	}

}
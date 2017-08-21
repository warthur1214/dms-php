<?php
namespace Home\Model;
use Think\Model;
class CompanyModel extends Model
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
	*获取企业单个信息
	* $where 条件 array
	* $field 指定字段
	*/
	public function getInfo($where,$field = "*")
	{
		return $this->field($field)->where($where)->find();
	}
	/**
	*获取企业信息
	* $where 条件 array
	* $field 指定字段
	*/
	public function getData($where,$field = "*")
	{
		return $this->field($field)->where($where)->select();
	}
	/**
	*添加企业信息
	* $array 添加数据 array
	*/
	public function addCompany($array)
	{
		return $this->add($array);
	}
	/**
	*修改企业信息
	* $where 条件 array
	* $array 修改数据 array
	*/
	public function editCompany($where,$array)
	{
		return $this->where($where)->data($array)->save();
	}
	/**
	*删除企业信息
	* $id 企业主键
	*/
	public function delCompany($id)
	{
		return $this->delete($id);
	}

}
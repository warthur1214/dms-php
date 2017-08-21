<?php
namespace Home\Model;
use Think\Model;
class AccountModel extends Model
{

    protected $trueTableName  = 'tp_account';
	/**
	*获取account单个信息
	* $where 条件 array
	* $field 指定字段
	*/
	public function getInfo($where,$field = "*")
	{
		return $this->field($field)->where($where)->find();
	}
	/**
	*获取account信息
	* $where 条件 array
	* $field 指定字段
	*/
	public function getData($where,$field = "*")
	{
		return $this->field($field)->where($where)->select();
	}
	/**
	*添加account信息
	* $array 添加数据 array
	*/
	public function addAccount($array)
	{
		return $this->add($array);
	}
	/**
	*修改account信息
	* $where 条件 array
	* $array 修改数据 array
	*/
	public function editAccount($where,$array)
	{
		return $this->where($where)->data($array)->save();
	}
	/**
	*删除account信息
	* $id account主键
	*/
	public function delAccount($id)
	{
		return $this->delete($id);
	}

}
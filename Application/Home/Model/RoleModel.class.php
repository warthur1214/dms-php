<?php
namespace Home\Model;
use Think\Model;
class RoleModel extends Model
{
	/**
	*获取角色单个信息
	* $where 条件 array
	* $field 指定字段
	*/
	public function getInfo($where,$field = "*")
	{
		return $this->field($field)->where($where)->find();
	}
	/**
	*获取角色信息
	* $where 条件 array
	* $field 指定字段
	*/
	public function getData($where,$field = "*")
	{
		return $this->field($field)->where($where)->select();
	}
	/**
	*添加角色信息
	* $array 添加数据 array
	*/
	public function addRole($array)
	{
		return $this->add($array);
	}
	/**
	*修改角色信息
	* $where 条件 array
	* $array 修改数据 array
	*/
	public function editRole($where,$array)
	{
		return $this->where($where)->data($array)->save();
	}
	/**
	*删除角色信息
	* $id 角色主键
	*/
	public function delRole($id)
	{
		return $this->delete($id);
	}

}
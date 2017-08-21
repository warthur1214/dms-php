<?php
namespace Home\Model;
use Think\Model;
class DeviceModel extends Model
{
	protected $dbName;
	protected $trueTableName;
	public function __construct($dbName,$trueTableName) 
	{
		$this->dbName = $dbName;
		$this->trueTableName = $trueTableName;
        parent::__construct();
	}
	/**
	*获取企业硬件单个信息
	* $where 条件 array
	* $field 指定字段
	*/
	public function getInfo($where,$field = "*")
	{
		return $this->field($field)->where($where)->find();
	}
	/**
	*获取企业硬件信息
	* $where 条件 array
	* $field 指定字段
	*/
	public function getData($where,$field = "*")
	{
		return $this->field($field)->where($where)->select();
	}
	/**
	*添加企业硬件信息
	* $data 添加数据 array
	*/
	public function addDevice($data)
	{
        //$sql = "insert into device (`device_type`,`device_model`,`organ_id`) values ".substr($data,0,-1);
		return $this->add($data);
	}
	/**
	*修改企业硬件信息
	* $where 条件 array
	* $array 修改数据 array
	*/
	public function editDevice($where,$array)
	{
		return $this->where($where)->data($array)->save();
	}
	/**
	*删除企业硬件信息
	* $where 删除条件
	*/
	public function delDevice($where)
	{
		return $this->where($where)->delete();
	}

	/**
	*获取企业硬件信息以厂商为分组
	* $where 条件 array
	*/
	public function getDataByGroup($where)
	{
		return $this->where($where)->group('organ_id')->select();
	}

}
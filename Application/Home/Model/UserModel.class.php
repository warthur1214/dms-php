<?php
namespace Home\Model;
use Think\Model;
class UserModel extends Model
{
	protected $dbName;
	protected $tablePrefix;
	protected $trueTableName = 'tp_user';
	public function __construct($dbName,$tablePrefix) 
	{
		$this->dbName = $dbName;
		$this->tablePrefix = $tablePrefix;
        parent::__construct();
	}
	/**
	*获取user单个信息
	* $where 条件 array
	* $field 指定字段
	*/
	public function getInfo($where,$field = "*")
	{
		return $this->field($field)->where($where)->find();
	}
	/**
	*获取user信息
	* $where 条件 array
	* $field 指定字段
	*/
	public function getData($where,$field = "*")
	{
		return $this->field($field)->where($where)->select();
	}
    /**
    * 获取所有用户信息
    * $where 查询条件 string
    * $firstRow 分页
    * $listRows 分页
    * $table 设备号库
    */
	public function userList($where,$firstRow,$listRows)
	{
		$sql = "SELECT `user`.user_id,`user`.tel,`user`.nickname,cp.plate_no as car_no,car.engine_no as motor_no,car.vin as car_vin,v.device_no,organ.organ_name,user.create_time
         from {$this->dbName}.tp_user as `user`
		 left join {$this->dbName}.tp_device as v on `user`.device_id = v.device_id
         left join auth.tp_organ as organ on v.belonged_organ_id = organ.organ_id
		 left join {$this->dbName}.tp_car as car on `user`.car_id = car.car_id
         left join {$this->dbName}.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
         left join {$this->dbName}.tp_plate as cp on cpr.plate_id = cp.plate_id 
		 where {$where} order by v.device_id desc limit {$firstRow},{$listRows}";
		$row = $this->query($sql);
		return $row;
	}
    /**
    * 获取所有用户数量
    * $where 查询条件 string
    * $table 设备号库
    */
	public function userCnt($where)
	{
		$sql = "SELECT count(1) as cnt
         from {$this->dbName}.tp_user as `user`
         left join {$this->dbName}.tp_device as v on `user`.device_id = v.device_id
         left join auth.tp_organ as organ on v.belonged_organ_id = organ.organ_id
         left join {$this->dbName}.tp_car as car on `user`.car_id = car.car_id
         left join {$this->dbName}.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
         left join {$this->dbName}.tp_plate as cp on cpr.plate_id = cp.plate_id 
		 where {$where}";
		$row = $this->query($sql);
		return $row;
	}

}
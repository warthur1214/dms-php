<?php
namespace Home\Model;
use Think\Model;
class FenceModel extends Model
{
    protected $dbName;
    protected $tablePrefix;
    protected $trueTableName  = 'tp_e_fence';
    public function __construct($dbName,$tablePrefix) 
    {
        $this->dbName = $dbName;
        $this->tablePrefix = $tablePrefix;
        parent::__construct();
    }
    /**
    *获取围栏信息
    * $where 条件 array
    * 
    */
    public function fenceList($where)
    {
        $sql = "SELECT f.e_fence_id as fence_id,name as fence_name,alarm_cond,open_date as open_time,close_date as end_time,alarm_day as work_day,alarm_start_time as work_stime,alarm_end_time as work_etime,sendee_phone,is_available as is_use
         from {$this->dbName}.tp_e_fence as f
         left join {$this->dbName}.tp_device_e_fence_rel as ef on f.e_fence_id = ef.e_fence_id
         left join {$this->dbName}.tp_device as v on ef.device_id = v.device_id
         where {$where}
         group by f.e_fence_id order by f.e_fence_id desc";
        $row = $this->query($sql);
        return $row;
    }
    /**
    *获取无分组车辆信息
    * $where 条件 array
    * 
    */
    public function getCarNoGroup($where)
    {
    	$sql = "SELECT car.car_id,car.car_no,car.car_group,v.organ_id from {$this->dbName}.tp_dvc_car as car
    	 left join {$this->dbName}.tp_dvc_vehicle as v on car.device_id=v.id
    	 where (car.car_group='0' or car.car_group is null) and car.device_id in ({$where})";
        $row = $this->query($sql);
        return $row;
    }
    /**
    *根据车辆id获取车辆信息
    * $where 条件 array
    * $车辆数据库 条件
    * 
    */
    public function getCarById($where)
    {
    	$sql = "SELECT car.car_id,cp.plate_no as car_no,cb.car_brand as car_band,cd.name as driver_name,cd.phone as driver_phone
         from {$this->dbName}.tp_device as v
         left join {$this->dbName}.tp_car as car on v.car_id = car.car_id
         left join {$this->dbName}.tp_car_plate_rel as cpr on v.car_id = cpr.car_id
         left join {$this->dbName}.tp_plate as cp on cpr.plate_id = cp.plate_id
         left join {$this->dbName}.tp_car_driver_rel as cdr on v.car_id = cdr.car_id
         left join {$this->dbName}.tp_driver as cd on cdr.driver_id = cd.driver_id
         left join biz.tp_car_type_view as cb on car.car_type_id = cb.car_type_id
    	 where v.device_id in ({$where})";
        $row = $this->query($sql);
        return $row;
    }
    /**
    *添加围栏车辆信息
    * $fenceId 围栏id
    * $carArr 车辆id  array
    */
    public function addCar($fenceId,$carArr)
    {
    	$sql = "insert into {$this->dbName}.tp_device_e_fence_rel (device_id,e_fence_id) values ";
    	foreach ($carArr as $key => $val) 
    	{
    		$sql .= "('".$val."','{$fenceId}'),";
    	}
    	$sql = substr($sql,0,-1);
    	$row = $this->execute($sql);
    	return $row;
    }

}
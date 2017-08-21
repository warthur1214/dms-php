<?php

namespace Home\Model;

use Think\Model;

class VehicleModel extends Model
{
    protected $dbName;
    protected $tablePrefix;
    protected $trueTableName = 'tp_device';

    public function __construct($dbName, $tablePrefix)
    {
        $this->dbName = $dbName;
        $this->tablePrefix = $tablePrefix;
        parent::__construct();
    }
    /**
     * 获取设备车辆数量
     * $where 查询条件 string
     */
    // public function getCon($where)
    // {
    // 	$sql = "select count(*) as con from {$this->dbName}.tp_dvc_vehicle as v
    // 	 left join {$this->dbName}.tp_dvc_car as car on v.id = car.device_id where {$where} limit 1";
    // 	 $row = $this->query($sql);
    // 	 return $row;
    // }
    /**
     * 获取设备车辆信息
     * $where 查询条件 string
     * $firstRow 分页 从哪开始
     * $listRows 分页 条数
     */
    // public function getList($where,$firstRow = '0',$listRows = '20')
    // {
    // 	$sql = "select car.car_no,car.car_brand,car.car_driver,car.car_status,car.car_group,v.id,v.device_no,v.active_status,v.is_car,v.is_use,v.device_type FROM {$this->dbName}.tp_dvc_vehicle as v
    // 	 LEFT JOIN {$this->dbName}.tp_dvc_car as car on v.id = car.device_id
    // 	 WHERE {$where} order by v.id desc limit {$firstRow},{$listRows}";
    // 	 $row = $this->query($sql);
    // 	 return $row;
    // }
    /**
     *获取设备单个信息
     * $where 条件 array
     * $field 指定字段
     */
    public function getInfo($where, $field = "*")
    {
        return $this->field($field)->where($where)->find();
    }

    /**
     *获取设备信息
     * $where 条件 array
     * $field 指定字段
     */
    public function getData($where, $field = "*")
    {
        return $this->field($field)->where($where)->order('device_id desc')->select();
    }

    /**
     *添加设备信息
     * $array 添加数据 array
     */
    public function addVehicle($array)
    {
        try {
            $row = @$this->add($array);
        } catch (Exception $e) {
            $row = null;
        }
        return $row;
    }
    /**
     *批量添加设备
     * $array 添加数据 array
     */
    // public function importVehicle($array)
    // {
    // 	if(is_array($array))
    // 	{
    // 		$sql = "insert into {$this->dbName}.tp_dvc_vehicle (device_no,device_com,device_type,device_model,organ_id,city_code) values ";
    // 		foreach ($array as $key => $val)
    // 		{
    // 			$sql .= "('".$val['device_id']."',".$val['device_com'].",".$val['device_type'].",".$val['device_model'].",".$val['organ_id'].",'".$val['city_code']."'),";
    // 		}
    // 		$sql = substr($sql,0,-1);
    // 		try {
    // 			$row = $this->execute($sql);
    // 		} catch (Exception $e) {
    // 			$row = null;
    // 		}
    // 		return $row;
    // 	}
    // }

    /**
     *修改设备信息
     * $where 条件 array
     * $array 修改数据 array
     */
    public function editVehicle($where, $array)
    {
        return $this->where($where)->data($array)->save();
    }

    /**
     *删除设备信息
     * $id 设备主键
     */
    public function delVehicle($id)
    {
        return $this->delete($id);
    }

    /**
     *设备列表
     * $where 搜索条件
     * $where 分页
     * $where 分页
     */
    public function vehicleList($where, $firstRow, $listRows)
    {
        $sql = "
            SELECT 
                v.device_id as id,v.device_no,vender.organ_name as device_com,type.device_type,
                model.device_series as device_model,v.belonged_organ_id,v.status as active_status,
                v.activated_time as active_time,v.is_binded_car as is_car,v.is_binded_user as is_use,
                v.create_time,sim.imsi,sim.iccid as sim_iccid,sim.msisdn,sim.total_flow,
                sim.plan_term as package_month,user.tel,user.nickname,user.create_time as user_create_time,
                car.car_id,cp.plate_no as car_no,car.vin as v_code,car.engine_no as e_code,organ.organ_name
            from {$this->dbName}.tp_device as v
            LEFT JOIN auth.tp_organ AS organ ON v.belonged_organ_id = organ.organ_id
            LEFT JOIN auth.tp_organ AS vender ON v.supplied_organ_id = vender.organ_id
            LEFT JOIN biz.tp_device_type AS type ON vender.organ_id = type.supplied_organ_id AND type.supplied_organ_id = v.supplied_organ_id
            LEFT JOIN biz.tp_device_series AS model ON model.device_series_id = v.device_series_id
            left join {$this->dbName}.tp_sim_card as sim on v.device_id = sim.device_id
            left join {$this->dbName}.tp_user as user on v.device_id = user.device_id
            left join {$this->dbName}.tp_car as car on user.car_id = car.car_id 
            left join {$this->dbName}.tp_car_plate_rel as cpr on car.car_id = cpr.car_id AND cpr.car_id = v.car_id 
            left join {$this->dbName}.tp_plate as cp on cpr.plate_id = cp.plate_id
            where {$where} 
            group by v.device_id
            order by v.device_id desc limit {$firstRow},{$listRows}";
        $row = $this->query($sql);
        return $row;
    }


    /**
     *设备列表数量
     * $where 搜索条件
     */
    public function vehicleListCnt($where)
    {
        $sql = "SELECT count(1) as cnt
            from {$this->dbName}.tp_device as v
            LEFT JOIN auth.tp_organ AS organ ON v.belonged_organ_id = organ.organ_id
            LEFT JOIN auth.tp_organ AS vender ON v.supplied_organ_id = vender.organ_id
            LEFT JOIN biz.tp_device_type AS type ON vender.organ_id = type.supplied_organ_id 
            AND type.supplied_organ_id = v.supplied_organ_id
            AND type.supplied_organ_id = organ.organ_id
            LEFT JOIN biz.tp_device_series AS model ON model.device_series_id = v.device_series_id
            left join {$this->dbName}.tp_sim_card as sim on v.device_id = sim.device_id 
            left join {$this->dbName}.tp_user as user on v.device_id = user.device_id 
            left join {$this->dbName}.tp_car as car on user.car_id = car.car_id 
            left join {$this->dbName}.tp_car_plate_rel as cpr on car.car_id = cpr.car_id AND cpr.car_id = v.car_id
            left join {$this->dbName}.tp_plate as cp on cpr.plate_id = cp.plate_id
            where {$where}
            limit 1";
        $row = $this->query($sql);
        return $row;
    }
}
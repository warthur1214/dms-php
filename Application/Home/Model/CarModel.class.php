<?php

namespace Home\Model;

use Think\Model;

class CarModel extends Model
{
    protected $dbName;
    protected $tablePrefix;

    public function __construct($dbName, $tablePrefix)
    {
        $this->dbName = $dbName;
        $this->tablePrefix = $tablePrefix;
        parent::__construct();
    }

    /**
     *获取车辆信息
     * $cardb 车辆信息数据库
     * $where 条件
     * $firstRow 分页 从哪开始
     * $listRows 分页 条数
     */
    public function carList($where, $firstRow = '0', $listRows = '20')
    {
        $sql = "SELECT v.device_id as id,cp.plate_no as car_no,cb.car_brand as car_band,cd.name as driver_name,car.status as car_status,v.device_no,v.status as active_status,v.is_binded_user as is_use,v.is_binded_car as is_car,type.device_type as device_type_name,g.group_name
		 from {$this->dbName}.tp_device as v
		 left join {$this->dbName}.tp_car as car on v.car_id = car.car_id
		 left join {$this->dbName}.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
		 left join {$this->dbName}.tp_plate as cp on cpr.plate_id = cp.plate_id
		 left join biz.tp_car_type_view as cb on car.car_type_id = cb.car_type_id
		 left join {$this->dbName}.tp_car_driver_rel as cdr on car.car_id = cdr.car_id
		 left join {$this->dbName}.tp_driver as cd on cdr.driver_id = cd.driver_id
		 left join {$this->dbName}.tp_group as g on car.group_id = g.group_id
         left join biz.tp_device_series as series on v.device_series_id = series.device_series_id
		 left join biz.tp_device_type as type on series.device_type_id = type.device_type_id
		 where {$where} order by v.device_id desc limit {$firstRow},{$listRows}";
        $row = $this->query($sql);
        return $row;
    }

    /**
     *获取车辆信息数量
     * $cardb 车辆信息数据库
     * $where 条件
     */
    public function carCnt($where)
    {
        $sql = "SELECT count(1) as cnt from {$this->dbName}.tp_device as v
		 left join {$this->dbName}.tp_car as car on v.car_id = car.car_id
		 left join {$this->dbName}.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
		 left join {$this->dbName}.tp_plate as cp on cpr.plate_id = cp.plate_id
		 left join biz.tp_car_type_view as cb on car.car_type_id = cb.car_type_id
		 left join {$this->dbName}.tp_car_driver_rel as cdr on car.car_id = cdr.car_id
		 left join {$this->dbName}.tp_driver as cd on cdr.driver_id = cd.driver_id
		 left join {$this->dbName}.tp_group as g on car.group_id = g.group_id
         left join biz.tp_device_series as series on v.device_series_id = series.device_series_id
		 left join biz.tp_device_type as type on series.device_type_id = type.device_type_id
		 where {$where} limit 1";
        $row = $this->query($sql);
        return $row;
    }

    public function getCarInfoByDvcId($id)
    {
        $sql = "SELECT cb.car_series as car_brand_id,cb.car_brand,cb.car_series,cb.car_type,car.function,cp.plate_no,cdr.driver_id,car.purchase_time as purchase,car.group_id,car.100_km_oil_wear,car.upkeep_type,car.upkeep_interval,car.4s_shop_phone,car.status,car.next_upkeep_time,car.engine_no,car.vin,v.device_id,v.device_no,v.activated_time,v.belonged_organ_id,type.device_type,series.device_series,car.car_series_id,sim.imsi,sim.total_flow,sim.plan_term from {$this->dbName}.tp_device as v
		 left join {$this->dbName}.tp_sim_card as sim on v.device_id = sim.device_id
		 left join {$this->dbName}.tp_car as car on v.car_id = car.car_id
		 left join biz.tp_car_type_view as cb on car.car_type_id = cb.car_type_id
		 left join {$this->dbName}.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
		 left join {$this->dbName}.tp_plate as cp on cpr.plate_id = cp.plate_id
		 left join {$this->dbName}.tp_car_driver_rel as cdr on car.car_id = cdr.car_id
         left join biz.tp_device_series as series on v.device_series_id = series.device_series_id
		 left join biz.tp_device_type as type on series.device_type_id = type.device_type_id
		 where v.device_id = {$id} limit 1";
        $row = $this->query($sql);
        return $row;
    }

    /**
     *获取车辆单个信息
     * $where 条件 array
     * $field 指定字段
     */
    public function getInfo($where, $field = "*")
    {
        return $this->field($field)->where($where)->find();
    }

    /**
     *获取车辆信息
     * $where 条件 array
     * $field 指定字段
     */
    public function getData($where, $field = "*")
    {
        return $this->field($field)->where($where)->select();
    }

    /**
     *添加车辆信息
     * $array 添加数据 array
     */
    public function addCar($array)
    {
        return $this->add($array);
    }

    /**
     *修改车辆信息
     * $where 条件 array
     * $array 修改数据 array
     */
    public function editCar($where, $array)
    {
        return $this->where($where)->data($array)->save();
    }

    /**
     *删除车辆信息
     * $id 车辆主键
     */
    public function delCar($id)
    {
        return $this->delete($id);
    }

    public function getCarIdByNo($where, $field)
    {
        return M("$this->dbName.plate")
            ->alias('plate')
            ->join("$this->dbName.tp_car_plate_rel as pr on plate.plate_id = pr.plate_id")
            ->field($field)
            ->where($where)->find();
    }

    /**
     *根据车辆id获取车牌号
     * $where 条件
     */
    public function getCarNoById($where)
    {
        return M("{$this->dbName}.plate")
            ->alias('plate')
            ->join("{$this->dbName}.tp_car_plate_rel as pr on plate.plate_id = pr.plate_id")
            ->join("{$this->dbName}.tp_car c on c.car_id = pr.car_id")
            ->field('pr.car_id, plate_no as car_no')
            ->where($where)->select();
    }

    public function getCarInfoList($where)
    {

        $sql = "SELECT car.car_id,cb.car_brand,cp.plate_no,cd.name as driver_name,cd.phone as driver_phone,car.group_id,g.group_name,v.device_id,v.device_no,v.belonged_organ_id as organ_id,organ.organ_name
		 from {$this->dbName}.tp_device as v
         left join auth.tp_organ as organ on v.belonged_organ_id = organ.organ_id
		 left join {$this->dbName}.tp_car as car on v.car_id = car.car_id
		 left join biz.tp_car_type_view as cb on car.car_type_id = cb.car_type_id
		 left join {$this->dbName}.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
		 left join {$this->dbName}.tp_plate as cp on cpr.plate_id = cp.plate_id
		 left join {$this->dbName}.tp_car_driver_rel as cdr on car.car_id = cdr.car_id
		 left join {$this->dbName}.tp_driver as cd on cdr.driver_id = cd.driver_id
		 left join {$this->dbName}.tp_group as g on car.group_id = g.group_id
		 where $where
		 ";
        $row = $this->query($sql);
        return $row;
    }

}
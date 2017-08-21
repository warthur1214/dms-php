<?php
namespace Home\Model;
use Think\Model;
class CostModel extends Model
{
    protected $dbName;
    protected $tablePrefix;
    protected $trueTableName  = 'tp_expense';
    public function __construct($dbName,$tablePrefix)
    {
        $this->dbName = $dbName;
        $this->tablePrefix = $tablePrefix;
        parent::__construct();
    }

    public function getCarNoByUser($where=null)
    {
        $device = M("{$this->dbName}.device d")->field('u.car_id')
            ->where($where)
            ->join("{$this->dbName}.tp_user u on u.device_id = d.device_id")
            ->select();
        return $device;
    }

    /**
    *获取费用信息
    * $where 条件 array
    * $cardb 车辆数据库
    *
    */
    public function costList($where)
    {
        $sql = "SELECT cost.expense_id as cost_id,cost.car_id,
	     cp.plate_no as car_no,v.device_no,cdr.driver_id,cd.name as driver_name,
	     CASE cost.expense_type WHEN 0 THEN '保险费用' WHEN 1 THEN '保养费用' WHEN 2 THEN '维修费用' 
	        WHEN 3 THEN '过路过桥费' ELSE '加油费' END as cost_type, 
	     cost.expense_amount as cost, cost.occur_time as cost_time
         from {$this->dbName}.tp_expense as cost
         left join {$this->dbName}.tp_car as car on cost.car_id = car.car_id
         left join {$this->dbName}.tp_car_plate_rel as cpr on cost.car_id = cpr.car_id
         left join {$this->dbName}.tp_plate as cp on cpr.plate_id = cp.plate_id
         LEFT JOIN {$this->dbName}.tp_user u ON u.car_id = car.car_id
         LEFT JOIN {$this->dbName}.tp_device AS v ON u.device_id = v.device_id
         left join {$this->dbName}.tp_car_driver_rel as cdr on cost.car_id = cdr.car_id
         left join {$this->dbName}.tp_driver as cd on cdr.driver_id = cd.driver_id
         where {$where}
         order by cost.expense_id desc";
        $row = $this->query($sql);
        return $row;
    }
    /**
    *获取费用统计
    * $where 条件 array
    *
    */
    public function costData($where)
    {
        $sql = "SELECT cost.expense_type as cost_type,ifnull(sum(cost.expense_amount),0) as cost_sum
         from {$this->dbName}.tp_expense as cost
         left join {$this->dbName}.tp_car as car on cost.car_id = car.car_id
         left join {$this->dbName}.tp_car_plate_rel as cpr on cost.car_id = cpr.car_id
         left join {$this->dbName}.tp_plate as cp on cpr.plate_id = cp.plate_id
         left join {$this->dbName}.tp_device as v on cost.car_id=v.car_id
         left join {$this->dbName}.tp_car_driver_rel as cdr on cost.car_id = cdr.car_id
         left join {$this->dbName}.tp_driver as cd on cdr.driver_id = cd.driver_id
         where {$where} group by cost_type";
        $row = $this->query($sql);
        return $row;
    }
    /**
    *获取费用合计
    * $where 条件 array
    *
    */
    public function costCon($where)
    {
        $sql = "SELECT ifnull(sum(cost.expense_amount),0) as cost_sum,date_format(cost.occur_time,'%Y-%m') as cost_time
         from {$this->dbName}.tp_expense as cost
         left join {$this->dbName}.tp_car as car on cost.car_id = car.car_id
         left join {$this->dbName}.tp_car_plate_rel as cpr on cost.car_id = cpr.car_id
         left join {$this->dbName}.tp_plate as cp on cpr.plate_id = cp.plate_id
         left join {$this->dbName}.tp_device as v on cost.car_id=v.car_id
         left join {$this->dbName}.tp_car_driver_rel as cdr on cost.car_id = cdr.car_id
         left join {$this->dbName}.tp_driver as cd on cdr.driver_id = cd.driver_id
         where {$where}";
        $row = $this->query($sql);
        return $row;
    }
    /**
    *获取费用折线统计
    * $where 条件 array
    *
    */
    public function costItemData($where)
    {
        $sql = "SELECT ifnull(sum(cost.expense_amount),0) as cost_sum,cost.expense_type as cost_type,cost.occur_time as cost_time
         from {$this->dbName}.tp_expense as cost
         left join {$this->dbName}.tp_car as car on cost.car_id = car.car_id
         left join {$this->dbName}.tp_car_plate_rel as cpr on cost.car_id = cpr.car_id
         left join {$this->dbName}.tp_plate as cp on cpr.plate_id = cp.plate_id
         left join {$this->dbName}.tp_device as v on cost.car_id=v.car_id
         left join {$this->dbName}.tp_car_driver_rel as cdr on cost.car_id = cdr.car_id
         left join {$this->dbName}.tp_driver as cd on cdr.driver_id = cd.driver_id
         where {$where} group by cost_type,cost_time order by cost_time";
        $row = $this->query($sql);
        return $row;
    }

}
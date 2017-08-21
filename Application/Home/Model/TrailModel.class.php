<?php

namespace Home\Model;

use Think\Model;

class TrailModel extends Model
{
    protected $trueTableName = 'tp_sgl_jny_analysis_0000';
    protected $dbName;
    protected $mydb;

    public function __construct($dbName, $db)
    {
        $this->dbName = $dbName;
        $this->mydb = $db;
        parent::__construct();
    }

    /**
     * 获取车辆轨迹
     * $bizDB 业务数据库
     * $database 数据库名 array
     * $where 查询条件 string
     * $firstRow 分页 从哪开始
     * $listRows 分页 条数
     */
    public function locusList($bizDB, $database, $where, $firstRow = '0', $listRows = '20')
    {
        $sql = "SELECT id,device_no,car_no,user_id,jny_id,device_id,start_time,end_time,distance_travelled,distance_units,duration,oil_wear,start_address,end_address FROM (";
        $sql .= "(SELECT jny.id,v.device_no,cp.plate_no as car_no,jny.user_id,jny.jny_id,jny.device_id,jny.start_time,jny.end_time,jny.distance_travelled,jny.distance_units,jny.duration,jny.oil_wear,jny.start_address,jny.end_address,jny.create_time
         from $this->dbName.tp_sgl_jny_analysis_0000 as jny
         inner join $bizDB.tp_device as v on jny.device_id = v.device_id
         left join $bizDB.tp_car as car on v.car_id = car.car_id
         left join $bizDB.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
         left join $bizDB.tp_plate as cp on cpr.plate_id = cp.plate_id
         left join $bizDB.tp_group as `group` on car.group_id = `group`.group_id
         where $where)";
        if (!empty($database)) {
            foreach ($database as $key => $val) {
                $sql .= " union (select jny.id,v.device_no,cp.plate_no as car_no,jny.user_id,jny.jny_id,jny.device_id,jny.start_time,jny.end_time,jny.distance_travelled,jny.distance_units,jny.duration,jny.oil_wear,jny.start_address,jny.end_address,jny.create_time
                 from {$val['table_schema']}.{$val['table_name']} as jny
                 inner join $bizDB.tp_device as v on jny.device_id = v.device_id
                 left join $bizDB.tp_car as car on v.car_id = car.car_id
                 left join $bizDB.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
                 left join $bizDB.tp_plate as cp on cpr.plate_id = cp.plate_id
                 left join $bizDB.tp_group as `group` on car.group_id = `group`.group_id
                 where $where)";
            }
        }
        $sql .= ") as a
         order by a.end_time desc limit $firstRow,$listRows";
        $row = $this->query($sql);
        return $row;
    }

    /**
     * 获取车辆轨迹数量
     * $bizDB 业务数据库
     * $database 数据库名 array
     * $where 查询条件 string
     */
    public function locusListCnt($bizDB, $database, $where)
    {
        $sql = "SELECT sum(cnt) AS cnt FROM (";
        $sql .= "(select count(1) as cnt
         from $this->dbName.tp_sgl_jny_analysis_0000 as jny
         inner join $bizDB.tp_device as v on jny.device_id = v.device_id
         left join $bizDB.tp_car as car on v.car_id = car.car_id
         left join $bizDB.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
         left join $bizDB.tp_plate as cp on cpr.plate_id = cp.plate_id
         left join $bizDB.tp_group as `group` on car.group_id = `group`.group_id
         where $where )";
        if (!empty($database)) {
            foreach ($database as $key => $val) {
                $sql .= " union (select count(1) as cnt
                 from {$val['table_schema']}.{$val['table_name']} as jny
                 inner join $bizDB.tp_device as v on jny.device_id = v.device_id
                 left join $bizDB.tp_car as car on v.car_id = car.car_id
                 left join $bizDB.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
                 left join $bizDB.tp_plate as cp on cpr.plate_id = cp.plate_id
                 left join $bizDB.tp_group as `group` on car.group_id = `group`.group_id
                 where $where )";
            }
        }
        $sql .= ") as a";
        $row = $this->query($sql);
        return $row;
    }

    /**
     * 获取详细信息
     * $table 数据库+表名
     * $field 字段
     * $where 查询条件 string
     */
    public function getInfo($table, $field = '*', $where)
    {
        $sql = "select {$field} from {$table} where 1=1 {$where}";
        $row = $this->query($sql);
        return $row;
    }

    public function getTrailInfo($table = [], $field = '*', $where)
    {
        $sql = "select {$field} from {$table[0]} t1 
                INNER join {$table[1]} t2 on t1.jny_id = t2.jny_id
                where 1=1 
                {$where}";
        return $this->query($sql);
    }

    /**
     * 获取行驶统计数据
     * $database 数据库名 array
     * $where 查询条件 string
     * $bizDB 业务数据库 string
     */
    public function getData($database, $where, $bizDB)
    {
        $sql = "SELECT sum(distance_travelled) AS distance_travelled,sum(substr(duration,1,2)*3600+substr(duration,4,2)*60+substr(duration,7,2)) AS duration,sum(accel_count) AS accel_count,sum(decel_count) AS decel_count,sum(oil_wear) AS oil_wear,max(max_speed) AS max_speed,sum(avg_speed) AS avg_speed,count(*) AS con FROM (";
        $sql .= "(select distance_travelled,duration,accel_count,decel_count,oil_wear,max_speed,avg_speed,jny.device_id,start_time,end_time
         from {$this->dbName}.tp_sgl_jny_analysis_0000 as jny
         left join $bizDB.tp_device as v on jny.device_id = v.device_id
         left join $bizDB.tp_car as car on v.car_id = car.car_id
         left join $bizDB.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
         left join $bizDB.tp_plate as cp on cpr.plate_id = cp.plate_id
         left join $bizDB.tp_car_driver_rel as cdr on car.car_id = cdr.car_id
         left join $bizDB.tp_driver as cd on cdr.driver_id = cd.driver_id
         left join $bizDB.tp_group as g on car.group_id = g.group_id
         where {$where})";
        if (!empty($database)) {
            foreach ($database as $key => $val) {
                $sql .= " union (select distance_travelled,duration,accel_count,decel_count,oil_wear,max_speed,avg_speed,jny.device_id,start_time,end_time
                 from {$val['table_schema']}.{$val['table_name']} as jny
                 left join $bizDB.tp_device as v on jny.device_id = v.device_id
                 left join $bizDB.tp_car as car on v.car_id = car.car_id
                 left join $bizDB.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
                 left join $bizDB.tp_plate as cp on cpr.plate_id = cp.plate_id
                 left join $bizDB.tp_car_driver_rel as cdr on car.car_id = cdr.car_id
                 left join $bizDB.tp_driver as cd on cdr.driver_id = cd.driver_id
                 left join $bizDB.tp_group as g on car.group_id = g.group_id
                 where {$where})";
            }
        }
        $sql .= ") as a";
        $row = $this->query($sql);
        return $row;
    }

    /**
     * 获取行驶折线统计数据
     * $database 数据库名 array
     * $timeval 分组条件 string
     * $where 查询条件 string
     * $bizDB 业务数据库 string
     */
    public function getItemData($database, $timeval, $where, $bizDB)
    {
        $sql = "select {$timeval} as timeval,sum(distance_travelled) AS distance_travelled,sum(substr(duration,1,2)*3600+substr(duration,4,2)*60+substr(duration,7,2)) AS duration,sum(accel_count) as accel_count,sum(decel_count) as decel_count,sum(oil_wear) as oil_wear,avg_speed,max(max_speed) as max_speed from (";
        $sql .= "(select jny.id,end_time,distance_travelled,duration,accel_count,decel_count,oil_wear,avg_speed,max_speed,jny.device_id
         from {$this->dbName}.tp_sgl_jny_analysis_0000 as jny
         left join $bizDB.tp_device as v on jny.device_id = v.device_id
         left join $bizDB.tp_car as car on v.car_id = car.car_id
         left join $bizDB.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
         left join $bizDB.tp_plate as cp on cpr.plate_id = cp.plate_id
         left join $bizDB.tp_car_driver_rel as cdr on car.car_id = cdr.car_id
         left join $bizDB.tp_driver as cd on cdr.driver_id = cd.driver_id
         left join $bizDB.tp_group as g on car.group_id = g.group_id
         where {$where})";
        if (!empty($database)) {
            foreach ($database as $key => $val) {
                $sql .= " union (select jny.id,end_time,distance_travelled,duration,accel_count,decel_count,oil_wear,avg_speed,max_speed,jny.device_id
                 from {$val['table_schema']}.{$val['table_name']} as jny
                 left join $bizDB.tp_device as v on jny.device_id = v.device_id
                 left join $bizDB.tp_car as car on v.car_id = car.car_id
                 left join $bizDB.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
                 left join $bizDB.tp_plate as cp on cpr.plate_id = cp.plate_id
                 left join $bizDB.tp_car_driver_rel as cdr on car.car_id = cdr.car_id
                 left join $bizDB.tp_driver as cd on cdr.driver_id = cd.driver_id
                 left join $bizDB.tp_group as g on car.group_id = g.group_id
                 where {$where})";
            }
        }
        $sql .= ") as a group by timeval order by timeval";
        $row = $this->query($sql);
        return $row;
    }

    /**
     * 获取行驶统计列表信息数量
     * $database 数据库名 array
     * $where 查询条件 string
     * $bizDB 业务数据库 string
     */
    public function getCnt($database, $where, $bizDB)
    {
        $sql = "SELECT count(1) AS cnt FROM (";
        $sql .= "(select jny.id,start_time,end_time,duration,max_speed,avg_speed,accel_count,decel_count,oil_wear,jny.device_id
         from {$this->dbName}.tp_sgl_jny_analysis_0000 as jny
         left join $bizDB.tp_device as v on jny.device_id = v.device_id
         left join $bizDB.tp_car as car on v.car_id = car.car_id
         left join $bizDB.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
         left join $bizDB.tp_plate as cp on cpr.plate_id = cp.plate_id
         left join $bizDB.tp_car_driver_rel as cdr on car.car_id = cdr.car_id
         left join $bizDB.tp_driver as cd on cdr.driver_id = cd.driver_id
         left join $bizDB.tp_group as g on car.group_id = g.group_id
         where {$where})";
        if (!empty($database)) {
            foreach ($database as $key => $val) {
                $sql .= " union (select jny.id,start_time,end_time,duration,max_speed,avg_speed,accel_count,decel_count,oil_wear,jny.device_id
                 from {$val['table_schema']}.{$val['table_name']} as jny
                 left join $bizDB.tp_device as v on jny.device_id = v.device_id
                 left join $bizDB.tp_car as car on v.car_id = car.car_id
                 left join $bizDB.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
                 left join $bizDB.tp_plate as cp on cpr.plate_id = cp.plate_id
                 left join $bizDB.tp_car_driver_rel as cdr on car.car_id = cdr.car_id
                 left join $bizDB.tp_driver as cd on cdr.driver_id = cd.driver_id
                 left join $bizDB.tp_group as g on car.group_id = g.group_id
                 where {$where})";
            }
        }
        $sql .= ") as a limit 1";
        $row = $this->query($sql);
        return $row;
    }

    /**
     * 获取行驶统计列表信息
     * $database 数据库名 array
     * $where 查询条件 string
     * $bizDB 业务数据库 string
     * $firstRow 分页
     * $listRows 分页
     */
    public function getList($database, $where, $bizDB, $firstRow, $listRows)
    {
        $sql = "SELECT a.id,a.start_time,a.end_time,plate_no AS car_no,device_no,name AS driver_name,a.duration,a.max_speed,a.avg_speed,a.accel_count,a.decel_count,a.oil_wear FROM (";
        $sql .= "(select jny.id,start_time,end_time,duration,max_speed,avg_speed,accel_count,decel_count,oil_wear,jny.device_id,v.device_no,cp.plate_no,cd.name
         from {$this->dbName}.tp_sgl_jny_analysis_0000 as jny
         left join $bizDB.tp_device as v on jny.device_id = v.device_id
         left join $bizDB.tp_car as car on v.car_id = car.car_id
         left join $bizDB.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
         left join $bizDB.tp_plate as cp on cpr.plate_id = cp.plate_id
         left join $bizDB.tp_car_driver_rel as cdr on car.car_id = cdr.car_id
         left join $bizDB.tp_driver as cd on cdr.driver_id = cd.driver_id
         left join $bizDB.tp_group as g on car.group_id = g.group_id
         where {$where})";
        if (!empty($database)) {
            foreach ($database as $key => $val) {
                $sql .= " union (select jny.id,start_time,end_time,duration,max_speed,avg_speed,accel_count,decel_count,oil_wear,jny.device_id,v.device_no,cp.plate_no,cd.name
                 from {$val['table_schema']}.{$val['table_name']} as jny
                 left join $bizDB.tp_device as v on jny.device_id = v.device_id
                 left join $bizDB.tp_car as car on v.car_id = car.car_id
                 left join $bizDB.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
                 left join $bizDB.tp_plate as cp on cpr.plate_id = cp.plate_id
                 left join $bizDB.tp_car_driver_rel as cdr on car.car_id = cdr.car_id
                 left join $bizDB.tp_driver as cd on cdr.driver_id = cd.driver_id
                 left join $bizDB.tp_group as g on car.group_id = g.group_id
                 where {$where})";
            }
        }
        $sql .= ") as a order by a.id desc limit {$firstRow},{$listRows}";
        $row = $this->query($sql);
        return $row;
    }

    /**
     * 获取得分统计列表信息数量
     * $database 数据库名 array
     * $where 查询条件 string
     * $bizDB 业务数据库 string
     */
    public function getScoreCnt($database, $where, $bizDB)
    {
        $sql = "SELECT sum(cnt) AS cnt FROM (";
        $sql .= "(select count(1) as cnt
         from {$this->dbName}.tp_sgl_jny_analysis_0000 as jny
         left join $bizDB.tp_device as v on jny.device_id = v.device_id
         left join $bizDB.tp_user as u on jny.device_id = u.device_id
         left join $bizDB.tp_car as car on v.car_id = car.car_id
         left join $bizDB.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
         left join $bizDB.tp_plate as cp on cpr.plate_id = cp.plate_id
         where {$where})";
        if (!empty($database)) {
            foreach ($database as $key => $val) {
                $sql .= " union (select count(1) as cnt
                 from {$val['table_schema']}.{$val['table_name']} as jny
                 left join $bizDB.tp_device as v on jny.device_id = v.device_id
                 left join $bizDB.tp_user as u on jny.device_id = u.device_id
                 left join $bizDB.tp_car as car on v.car_id = car.car_id
                 left join $bizDB.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
                 left join $bizDB.tp_plate as cp on cpr.plate_id = cp.plate_id
                 where {$where})";
            }
        }
        $sql .= ") as s limit 1";
        $row = $this->query($sql);
        return $row;
    }

    /**
     * 获取得分统计列表信息
     * $database 数据库名 array
     * $where 查询条件 string
     * $bizDB 业务数据库 string
     * $firstRow 分页
     * $listRows 分页
     */
    public function getScore($database, $where, $bizDB, $firstRow, $listRows)
    {
        $sql = "SELECT id,tel,plate_no AS car_no,device_no,risk_score,accel_score,decel_score,speed_score,night_score,area_score,duration_score,distance_score,from_unixtime(start_time,'%Y-%m-%d %H:%i:%s') AS start_time,from_unixtime(end_time,'%Y-%m-%d %H:%i:%s') AS end_time FROM (";
        $sql .= "(select jny.id,u.tel,cp.plate_no,v.device_no,jny.risk_score,jny.accel_score,jny.decel_score,jny.speed_score,jny.night_score,jny.area_score,jny.duration_score,jny.distance_score,jny.start_time,jny.end_time
         from {$this->dbName}.tp_sgl_jny_analysis_0000 as jny
         left join $bizDB.tp_device as v on jny.device_id = v.device_id
         left join $bizDB.tp_user as u on jny.device_id = u.device_id
         left join $bizDB.tp_car as car on v.car_id = car.car_id
         left join $bizDB.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
         left join $bizDB.tp_plate as cp on cpr.plate_id = cp.plate_id
         where {$where})";
        if (!empty($database)) {
            foreach ($database as $key => $val) {
                $sql .= " union (select jny.id,u.tel,cp.plate_no,v.device_no,jny.risk_score,jny.accel_score,jny.decel_score,jny.speed_score,jny.night_score,jny.area_score,jny.duration_score,jny.distance_score,jny.start_time,jny.end_time
                 from {$val['table_schema']}.{$val['table_name']} as jny
                 left join $bizDB.tp_device as v on jny.device_id = v.device_id
                 left join $bizDB.tp_user as u on jny.device_id = u.device_id
                 left join $bizDB.tp_car as car on v.car_id = car.car_id
                 left join $bizDB.tp_car_plate_rel as cpr on car.car_id = cpr.car_id
                 left join $bizDB.tp_plate as cp on cpr.plate_id = cp.plate_id
                 where {$where})";
            }
        }
        $sql .= ") as s order by s.end_time desc limit {$firstRow},{$listRows}";
        $row = $this->query($sql);
        return $row;
    }

    /**
     * 获取当前月每日得分统计列表信息
     * $database 数据库名 array
     * $where 查询条件 string
     * $usertb 用户表 string
     * $db 链表库 string
     */
    public function scoreCon($database, $where, $usertb, $db)
    {
        $sql = "SELECT count(s.id) AS con,sum(s.risk_score) AS score_con,date_format(s.create_time,'%Y-%m-%d') AS create_time FROM (";
        $sql .= "(select id,risk_score,create_time,device_id from {$this->dbName}.tp_sgl_jny_analysis_0000)";
        if (!empty($database)) {
            foreach ($database as $key => $val) {
                $sql .= " union (select id,risk_score,create_time,device_id from {$val['table_schema']}.{$val['table_name']})";
            }
        }
        $sql .= ") as s
         where {$where} and date_format(s.create_time,'%Y-%m') = '" . date('Y-m') . "' group by date_format(s.create_time, '%Y-%m-%d')";
        $row = $this->query($sql);
        return $row;
    }
}
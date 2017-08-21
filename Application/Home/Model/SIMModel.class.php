<?php
namespace Home\Model;
use Think\Model;
class SIMModel extends Model
{
    protected $dbName;
    protected $tablePrefix;
    protected $trueTableName  = 'tp_sim_card';
    public function __construct($dbName,$tablePrefix) 
    {
        $this->dbName = $dbName;
        $this->tablePrefix = $tablePrefix;
        parent::__construct();
    }
    /**
    *sim卡列表数据
    * $where 条件 string
    * $opt_db 条件 app信息数据库
    */
    public function simCnt($where)
    {
        $sql = "SELECT count(1) as cnt
         from {$this->dbName}.tp_sim_card as sim
         left join {$this->dbName}.tp_device as v on sim.device_id = v.device_id
         left join {$this->dbName}.tp_user as user on v.device_id = user.device_id
         left join auth.tp_organ as organ on sim.belonged_organ_id = organ.organ_id
         left join auth.tp_organ as vender on sim.supplied_organ_id = vender.organ_id 
         where {$where} 
         limit 1";
        $row = $this->query($sql);
        return $row;
    }
    /**
    *sim卡列表数据
    * $where 条件 string
    * $firstRow 分页
    * $listRows 分页
    */
    public function simList($where,$firstRow,$listRows)
    {
        $sql = "SELECT sim.sim_card_id as id,sim.iccid as sim_iccid,sim.imsi,sim.real_name_status as uni_real_name,sim.real_name_auth_error as uni_error_info,sim.regist_time as reg_time,sim.is_binded_device as bind_status,sim.total_flow,sim.plan_term as package_month,v.device_no,user.tel,organ.organ_name,sim.belonged_organ_id,vender.organ_name as vender_name
         from {$this->dbName}.tp_sim_card as sim
         left join {$this->dbName}.tp_device as v on sim.device_id = v.device_id
         left join {$this->dbName}.tp_user as user on v.device_id = user.device_id
         left join auth.tp_organ as organ on sim.belonged_organ_id = organ.organ_id
         left join auth.tp_organ as vender on sim.supplied_organ_id = vender.organ_id 
         where {$where} 
         group by sim.sim_card_id
         order by sim.sim_card_id desc 
         limit {$firstRow},{$listRows}";
        $row = $this->query($sql);
        return $row;
    }

}
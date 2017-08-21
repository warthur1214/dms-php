<?php
namespace Home\Controller;

use Home\Model\CostModel;

class CostCountController extends BaseController
{
    private $costDB;

    function __construct()
    {
        parent::__construct();
        $this->costDB = new CostModel($this->bizDB, 'tp_');
    }
    /**
    *费用统计
    */
    public function costCount()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('costCount',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('costCount');
    }
    /**
    *费用统计数据
    */
    public function costData()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('costCount',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $where = $this->selectInfo(I('param.'));
        $cost = $this->costDB->costData($where);
        $data = [];
        foreach ($cost as $key => $val) 
        {
            $data[$key][$val['cost_type']] = $val['cost_sum'];
        }
        echo json_encode($data);
        exit;
    }
    /**
    *费用统计数据
    */
    public function costItemData()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('costCount',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        
        $where = $this->selectInfo(I('param.'));
        $cost = $this->costDB->costItemData($where);
        $data = [];
        $sum = [];
        foreach ($cost as $key => $val) 
        {
            $sum[$key] = array(
                'cost_time' => $val['cost_time'],
                $val['cost_type'] => $val['cost_sum']
                );
        }
        foreach ($sum as $key => $val) 
        {
            if ($data[$val['cost_time']]) 
            {
                $data[$val['cost_time']] = array_merge($data[$val['cost_time']], $val);
            } 
            else 
            {
                $data[$val['cost_time']] = $val;
            }
        }
        $data = array_values($data);
        echo json_encode($data);
        exit;
    }
    /**
    *费用统计数据导出
    */
    public function costListOut()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('costCount',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        
        $where = $this->selectInfo(I('param.'));
        $data = $this->costDB->costList($where);
        A('Excel')->costOut($data);
        exit;
    }
    /**
     * 搜索条件
     * @param $data 搜索数据
     * @return array
     */
    public function selectInfo($data)
    {
        $where = '1=1';
        if($this->ownOrgan())
        {
            $where .= " and v.belonged_organ_id in (".$this->ownOrgan().")";
        }
        else
        {
            $where .= " and v.belonged_organ_id = -1";
        }
        //周统计
        if(I('param.timeStatus') == 'week')
        {
            $timeVal = explode('-',I('param.timeVal'));
            $where .= " and cost.occur_time between '".$timeVal[0]."' and '".$timeVal[1]."'";
        }
        //月统计
        else if(I('param.timeStatus') == 'month')
        {
            $where .= " and date_format(cost.occur_time,'%Y/%m') = '".I('param.timeVal')."'";
        }
        //年统计
        else if(I('param.timeStatus') == 'year')
        {
            $where .= " and date_format(cost.occur_time,'%Y') = '".I('param.timeVal')."'";
        }
        //查询车牌号
        if(I('param.car_no'))
        {
            $where .= " and cp.plate_no like '%".$data['car_no']."%'";
        }
        //查询设备号
        if(I('param.device_id'))
        {
            $where .= " and v.device_no like '%".$data['device_id']."%'";
        }
        unset($carId);
        //查询车辆分组
        if(I('param.car_group'))
        {
            $where .= " and car.group_id = ".$data['car_group'];
        }
        return $where;
    }
}
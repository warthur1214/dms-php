<?php
namespace Home\Controller;

use Home\Model\TrailModel;

class TravelCountController extends BaseController
{
    private $trailDB;

    function __construct()
    {
        parent::__construct();
        $this->trailDB = new TrailModel($this->getNowDB(), $this->bizDB);
    }
    /**
    *行驶统计列表
    */
    public function travelCount()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('travelCount',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('travelCount');
    }
    /**
    *行驶统计数据
    */
    public function travelData()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('travelCount',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $arr = $this->selectInfo(I('param.'));
        $data = $this->trailDB->getData($arr['dbName'], $arr['where'],$this->bizDB);
        
        foreach ($data as $key => &$val) 
        {
            $data[$key]['distance_travelled'] = $val['distance_travelled'];
            $data[$key]['duration'] = round($val['duration']/60);
            $data[$key]['avg_speed'] = round($val['avg_speed']/$val['con'],2);
            $data[$key]['max_speed'] = $val['max_speed'];
            $data[$key]['oil_wear'] = $val['oil_wear']/1000;
            $val = array_map(array($this, 'filterNull'), $val);
        }
        echo json_encode($data);
        exit;
    }
    /**
    *行驶各项统计数据
    */
    public function travelItemData()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('travelCount',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

    
        $arr = $this->selectInfo(I('param.'));
        $data = $this->trailDB->getItemData($arr['dbName'], $arr['timeKey'], $arr['where'],$this->bizDB);
        foreach ($data as $key => &$val) 
        {
            $data[$key]['distance_travelled'] = $val['distance_travelled'];
            $data[$key]['duration'] = round($val['duration']/60);
            $data[$key]['max_speed'] = $val['max_speed'];
            $data[$key]['oil_wear'] = $val['oil_wear']/1000;
            if(empty($val['timeval'])) unset($data[$key]);
            $val = array_map(array($this, 'filterNull'), $val);
        }

        echo json_encode($data);
        exit;
    }
    /**
    *行驶数据列表数据处理
    */
    public function travelListAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('travelCount',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        

        $arr = $this->selectInfo(I('param.'));
        // 查询满足要求的总记录数
        $count = $this->trailDB->getCnt($arr['dbName'], $arr['where'],$this->bizDB);
        // 实例化分页类
        $page = $this->getPage($count[0]['cnt'],$where);

        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $data = $this->trailDB->getList($arr['dbName'], $arr['where'],$this->bizDB,$page['firstRow'],$page['listRows']);
        if(I('param.fileOut') == 1)
        { 
            $data = $this->trailDB->getList($arr['dbName'], $arr['where'],$this->bizDB,0,$count[0]['cnt']);
        }
        foreach ($data as $key => &$val) 
        {
            $val = array_map(array($this, 'filterNull'), $val);
            $data[$key]['distance_travelled'] = $val['distance_travelled'];
            $data[$key]['duration'] = $val['duration'];
            $data[$key]['start_time'] = date('Y-m-d H:i:s',$val['start_time']);
            $data[$key]['end_time'] = date('Y-m-d H:i:s',$val['end_time']);
            $data[$key]['oil_wear'] = $val['oil_wear']/1000;
        }
        if(I('param.fileOut') == 1)
        { 
            A('Excel')->travelOut($data);
            exit;
        }
        echo json_encode(array('data' => $data,'page' => $page['show']));
        exit;
    }
    /**
     * 搜索条件
     * @param $data 搜索数据
     * @return array
     */
    public function selectInfo($data)
    {
        $where = "1=1";
        if($this->ownOrgan())
        {
            $where .= " and v.belonged_organ_id in (".$this->ownOrgan().")";
        }
        else
        {
            $where .= " and v.belonged_organ_id = -1";
        }

        $dbWhere['TABLE_SCHEMA'] = array('like','ubi_'.$this->sessionArr['organ_channel_id'].'_%');
        $dbWhere['TABLE_NAME'] = array('like','%tp_sgl_jny_analysis_%');
        //获取数据库名称
        $dbName = D('GetDB')->dbName($dbWhere);
        //周统计
        if($data['timeStatus'] == 'week')
        {
            $timeVal = explode('-',$data['timeVal']);
            $where .= " and end_time between '".strtotime($timeVal[0])."' and '".strtotime($timeVal[1])."'";
            $timeKey = "from_unixtime(end_time,'%Y/%m/%d')";
        }
        //月统计
        else if($data['timeStatus'] == 'month')
        {
            $where .= " and from_unixtime(end_time,'%Y/%m') = '".$data['timeVal']."'";
            $timeKey = "from_unixtime(end_time,'%Y/%m/%d')";
        }
        //年统计
        else if($data['timeStatus'] == 'year')
        {
            $where .= " and from_unixtime(end_time,'%Y') = '".$data['timeVal']."'";
            $timeKey = "from_unixtime(end_time,'%Y/%m')";
        }
        //查询车牌号
        if($data['car_no'])
        {
            $where .= " and cp.plate_no like '%".$data['car_no']."%'";
        }
        //查询设备号
        if($data['device_id'])
        {
            $where .= " and v.device_no like '%".$data['device_id']."%'";
        }
        //查询车辆分组
        if($data['car_group'])
        {
            $where .= " and car.group_id = ".$data['car_group'];
        }
        return ['where' => $where, 'timeKey' => $timeKey, 'dbName' => $dbName];
    }
    /**
    *信息数组赋值null为空
    */
    public function filterNull($v) 
    {
        if (is_null($v)) 
        {
            return '';
        }
        return $v;
    }
}
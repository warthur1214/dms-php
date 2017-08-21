<?php
namespace Home\Controller;

use Home\Model\CarGroupModel;

class PublicController extends BaseController
{
    private $groupDB;

    function __construct()
    {
        parent::__construct();
        $this->groupDB = new CarGroupModel($this->bizDB, 'tp_');
    }
    /**
    *获取企业机构车组递归数据
    */
    public function organTree()
    {
        $group = $this->groupDB->field('belonged_organ_id,group_id,group_name')->select();
        foreach ($group as $key => $val) 
        {
            $new_group[$val['belonged_organ_id']][] = $val;
        }
        $info = $this->getOrganGroup($this->sessionArr['parent_organ_id'],$new_group," and organ.organ_id = '".$this->sessionArr['organ_id']."'");
        if(empty($info))
        {
            $info = array();
        }
        echo json_encode($info);
        exit;
    }
    /**
    *无限极递归
    */
    public function getOrganGroup($pid = '0',$new_group,$otherWhere = '')
    {
        $where = "1=1 and organ.parent_organ_id = '{$pid}' and organ.is_available = '1'".$otherWhere;

        if($this->login_role_organ())
        {
            $organ_id = $this->belong_organ().",".$this->login_role_organ();
        }
        else
        {
            $organ_id = $this->belong_organ();
        }

        $where .= " and organ.organ_id in (".$organ_id.")";

        $list = $this->organDB->organList($where,'organ.organ_id,organ.organ_name');
        if($list)
        {
            foreach ($list as $key => $val) 
            {
                if ($val['organ_id']) 
                {
                    $val['group'] = $new_group[$val['organ_id']];
                    $val['son'] = $this->getOrganGroup($val['organ_id'],$new_group,$otherWhere = '');
                }
                $array[] = $val;
            }
        }
        return $array;
    }
    /**
    *车辆品牌数据
    */
    public function brand()
    {

        if(I('get.key'))
        {
            $where['car_brand'] = array('like','%'.I('get.key').'%');
            $where['car_series'] = array('like','%'.I('get.key').'%');
            $where['_logic'] = 'OR';
        }
        
        $brand = M('biz.car_type_view')->field('car_type_id,car_brand,car_series')->where($where)->group('car_series')->select();
        foreach ($brand as $key => $val) 
        {
            $data[$key]['car_brand_id'] = $val['car_series'];
            $data[$key]['car_brand'] = $val['car_brand'].' - '.$val['car_series'];
        }
        
        // if(I('get.id'))
        // {
        //     $where['car_brand'] = array('like',I('get.id').'%');
        // }
        // $data =  $this->brandDB->field('car_brand_id,car_brand')->where($where)->select();
        echo json_encode($data);
        exit;
    }
    /**
    *车系数据
    */
    public function getCarName()
    {
        $where['car_series'] = I('get.id');
        $where['car_type'] = array('like',I('get.key').'%');

        $arr = M('biz.car_type_view')->field('car_type_id,car_type')->where($where)->select();
        foreach ($arr as $key => $val) 
        {
            $data[$key]['car_series_id'] = $val['car_type_id'];
            $data[$key]['car_series'] = $val['car_type'];
        }

        // $where['car_brand_id'] = I('get.id');
        // $where['car_series'] = array('like',I('get.id').'%');
        // $where['_logic'] = 'OR';
        // $data = M('biz.car_series')->field('car_series_id,car_series')->where($where)->select();

        echo json_encode($data);
        exit;
    }
    // public function deviceRedis()
    // {
    //     $data = $this->vehicleDB->field('id,device_no,organ_id')->select();
    //     foreach ($data as $key => $val) 
    //     {
    //         $new_data[$val['device_no']] = $val['id'];
    //         $new_datas[$val['device_no']] = $val['organ_id'];
    //     }
    //     $this->in_redis->select(0);
    //     $this->in_redis->hmset($this->sessionArr['organ_channel_id'].':device_pk',$new_data);
    //     $this->in_redis->hmset($this->sessionArr['organ_channel_id'].':hardwareInfo',$new_datas);
    // }
    public function addTest()
    {

        //dump($this->in_redis->info());
        // try
        // {
        // $array = array(
        //     'imsi' => '111',
        //     'sim_iccid' => '4234',
        //     'msisdn' => '111',
        //     'total_flow' => '111',
        //     'package_month' => '111',
        //     'organ_id' => '1',
        //     'sim_vender' => '9'
        //     );
        //     $insertId = $this->simDB->data($array)->add();
        //     $con[] = $insertId;
        // }
        // catch (\Exception $e)
        // {
        //     $msg = $e->xdebug_message;
        //     $code = trim(explode(':',$msg)[4]);
        //     dump($code);
        //     switch ($code) 
        //     {
        //         case '1364'://必填项缺失
        //             # code...
        //             break;
        //         case '1062'://重复数据
        //             # code...
        //             break;
                
        //         default:
        //             # code...
        //             break;
        //     }
        // }
        // exit;
    }
}
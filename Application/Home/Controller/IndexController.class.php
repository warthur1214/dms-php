<?php
namespace Home\Controller;

use Home\Common\RedisModel;

class IndexController extends BaseController
{
    private $moduleDB;
    private $redisDB;

    function __construct()
    {
        parent::__construct();
        $this->moduleDB = D('Module');
        $this->redisDB = new RedisModel();
    }
    /**
    *首页 frame
    */
    public function index()
    {
        $this->display('Index/index');
    }
    /**
    *frame头部 
    */
    public function top()
    {
        $this->display('Index/top');
    }
    /**
    *机构归属
    */
    public function sonParent()
    {
        if($this->belong_organ())
        {
            $id = $this->belong_organ_parent();
        }
        else
        {
            $id = '0';
        }
        $pid = I('get.pid') ? I('get.pid') : $id;
        $data = $this->getList($pid,'organ.organ_id,organ.organ_name,organ.channel_id','organ.parent_organ_id',' and organ.parent_organ_id = 0');
        echo json_encode(array('data' => $data,'organ_id' => $this->sessionArr['organ_id']));
        exit;
    }
    /**
    *获取选择的企业机构标识 
    */
    public function saveChannel()
    {
        $array['organ_id'] = I('post.organ_id');

        $organ = $this->organDB->field('channel_id,parent_organ_id')->where(array('organ_id' => I('post.organ_id')))->find();

        $array['organ_channel_id'] = $organ['channel_id'];
        $array['parent_organ_id'] = $organ['parent_organ_id'];
        
        $this->redisDB->getRedis()->hmset(cookie('redis_key'),$array);
        echo json_encode(array('status' => 1));
        exit;
    }
    /**
    *frame选项栏
    */
    public function menu()
    {
        //获取当前帐号的角色的模块信息
        $module = $this->login_role_module();
        $pWhere = array('parent_module_id' => 0,'is_visible' => 1,'platform_id' => C('PLATFORM_ID'));
        $menu = $this->moduleDB->field('module_id,module_name,module_url')->where($pWhere)->order('sort_no desc,module_id asc')->select();
        foreach ($menu as $key => $val) 
        {
            $where['module_id'] = array('in',$module);
            $where['parent_module_id'] = array('eq',$val['module_id']);
            $where['is_visible'] = array('eq',1);
            $menu[$key]['menu_two'] = $this->moduleDB->field('module_id,module_name,module_url')->where($where)->order('sort_no desc,module_id asc')->select();
        }
        $this->assign('menu',$menu);
        $this->display('Index/menu');
    }
    /**
    *首页
    */
    public function main()
    {
        $organArr = $this->new_login_role_organ();
        $organId = $organArr[$this->sessionArr['organ_channel_id']];
        $carNo = $travelNum = $cost = $off = $on = $stop = $wrong = 0;
        $data = array();
        foreach ($organId as $key => $val) 
        {
            $info = $this->redisDB->getRedis()->hgetAll($this->sessionArr['organ_channel_id'].':dms_organ_data:'.$val);
            $data['carNo'] += $info['carNo'];
            $travelNum += $info['travelNum'];
            $data['travelNum'] += round($travelNum / 10000,1);
            $data['cost'] += $info['cost'];
            $data['off'] += $info['off'];
            $data['on'] += $info['on'];
            $data['stop'] += $info['stop'];
            $data['wrong'] = $data['carNo'] - $data['off'] - $data['on'] - $data['stop'];
        }
        $this->assign('info',$data);
        $this->display('Index/main');
    }
    /**
    *获取评分信息
    */
    public function score()
    {
        $organArr = $this->new_login_role_organ();
        $organId = $organArr[$this->sessionArr['organ_channel_id']];
        foreach ($organId as $key => $val) 
        {
            $map = json_decode($this->redisDB->getRedis()->hget($this->sessionArr['organ_channel_id'].':dms_organ_data:'.$val,'scoreMap'),true);
            if(is_array($map))
            {
                $i = 0;
                foreach ($map as $k => $v) 
                {
                    $scoreMap[$v['create_time']][$i]['cnt'] = $v['cnt'];
                    $scoreMap[$v['create_time']][$i]['score_cnt'] = $v['score_cnt'];
                    $i++;
                }
            }
        }
        ksort($scoreMap);
        foreach ($scoreMap as $key => $val) 
        {
            $cnt = $score_cnt = 0;
            foreach ($val as $k => $v) 
            {
                $cnt += $v['cnt'];
                $score_cnt += $v['score_cnt'];
                $info[$key]['score_cnt'] = round($score_cnt/$cnt);
                $info[$key]['create_time'] = $key;
            }
        }
        echo json_encode(array_values($info));
        exit;
    }
    /**
    *获取费用信息
    */
    public function cost()
    {
        $organArr = $this->new_login_role_organ();
        $organId = $organArr[$this->sessionArr['organ_channel_id']];
        foreach ($organId as $key => $val) 
        {
            $map = json_decode($this->redisDB->getRedis()->hget($this->sessionArr['organ_channel_id'].':dms_organ_data:'.$val,'costPic'),true);
            if(is_array($map))
            {
                $i = 0;
                foreach ($map as $k => $v) 
                {
                    $costMap[$v['cost_time']][$i]['cost_sum'] = $v['cost_sum'];
                    $i++;
                }
            }
        }
        ksort($costMap);
        foreach ($costMap as $key => $val) 
        {
            foreach ($val as $k => $v) 
            {
                $info[$key]['cost_sum'] += $v['cost_sum'];
                $info[$key]['cost_time'] = $key;
            }
        }
        echo json_encode(array_values($info));
        exit;
    }
    /**
    *获取行驶信息
    */
    public function travel()
    {
        $organArr = $this->new_login_role_organ();
        $organId = $organArr[$this->sessionArr['organ_channel_id']];
        foreach ($organId as $key => $val) 
        {
            $map = json_decode($this->redisDB->getRedis()->hget($this->sessionArr['organ_channel_id'].':dms_organ_data:'.$val,'driveData'),true);
            if(is_array($map))
            {
                $i = 0;
                foreach ($map as $k => $v) 
                {
                    $travelMap[$v['timeVal']][$i]['distance_travelled'] = $v['distance_travelled'];
                    $i++;
                }
            }
        }
        ksort($travelMap);
        foreach ($travelMap as $key => $val) 
        {
            foreach ($val as $k => $v) 
            {
                $info[$key]['distance_travelled'] += $v['distance_travelled'];
                $info[$key]['timeVal'] = $key;
            }
        }
        echo json_encode(array_values($info));
        exit;
    }
    /**
    *获取费用详情
    */
    public function getCostInfo()
    {
        $info = A('CostCount')->costData('cost_info',I('param.cost_time'));
        echo json_encode($info);
        exit;
    }
    /**
    *获取行驶详情
    */
    // public function getTravelInfo()
    // {
    //     $info = A('TravelCount')->travelItemData('travel_info',I('param.timeval'));
    //     echo json_encode($info);
    //     exit;
    // }
    /**
    *信息数组赋值null为空
    */
    public function filterNull($v) 
    {
        if (is_null($v)) 
        {
            return '0';
        }
        return $v;
    }
}
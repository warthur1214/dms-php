<?php
namespace Home\Controller;

use Home\Common\RedisModel;
use Home\Model\CarGroupModel;
use Home\Model\CarModel;
use Home\Model\FenceCarModel;
use Home\Model\FenceModel;
use Home\Model\VehicleModel;

class FenceController extends BaseController
{
    public $fenceCarDB; //围栏车辆信息model
    private $fenceDB;
    private $carDB;
    private $groupDB;
    private $vehicleDB;
    private $redisDB;

    function __construct()
    {
        parent::__construct();

        $this->fenceCarDB = new FenceCarModel($this->bizDB);
        $this->fenceDB = new FenceModel($this->bizDB, 'tp_');
        $this->carDB = new CarModel($this->bizDB, 'tp_');
        $this->groupDB = new CarGroupModel($this->bizDB, 'tp_');
        $this->vehicleDB = new VehicleModel($this->bizDB, 'tp_');
        $this->redisDB = new RedisModel();
    }
    /**
    *电子围栏列表页
    */
    public function fenceList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('fenceList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('fenceList');
    }
    /**
    *电子围栏列表数据
    */
    public function fenceListAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('fenceList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $where = '1=1';

        if($this->ownOrgan())
        {
            $where .= " and (v.belonged_organ_id in (".$this->ownOrgan().") or v.belonged_organ_id is null)";
        }
        //查询围栏名称
        if(I('post.fence_name'))
        {
            $where .= " and f.name like '%".I('post.fence_name')."%'";
        }
        //查询报警条件
        if(I('post.work_term') != '')
        {
            $where .= " and f.alarm_cond = ".I('post.work_term');
        }
        //查询围栏状态
        if(I('post.is_use') != '')
        {
            $where .= " and f.is_available = ".I('post.is_use');
        }
        //查询车牌号
        if(I('post.car_no'))
        {
            //通过车牌号获取车辆id
            $carArr['plate_no'] = array('like',"%".I('post.car_no')."%");
            $hasNo = $this->carDB->getCarNoById($carArr);
            if($hasNo)
            {
                foreach ($hasNo as $key => $val) 
                {
                    $carStr[] = $val['car_id'];
                }
                $carWhere['car_id'] = array('in',$carStr);
            }
            else
            {
                $carWhere['car_id'] = -1;
            }
            $dvc = $this->vehicleDB->field('device_id')->where($carWhere)->select();
            if($dvc)
            {
                foreach ($dvc as $key => $val) 
                {
                    $dvcStr[] = $val['device_id'];
                }
                $dvcWhere['device_id'] = array('in',$dvcStr);
            }
            else{
                $dvcWhere['device_id'] = -1;
            }
            //通过设备id获取围栏id
            $fenceCar = $this->fenceCarDB->field('e_fence_id')->where($dvcWhere)->select();
            if($fenceCar)
            {
                foreach ($fenceCar as $key => $val) 
                {
                    $fenceId[$key] = $val['e_fence_id'];
                }
                $where .= " and f.e_fence_id in (".implode(',',array_unique($fenceId)).")";
            }
            else
            {
                $where .= " and f.e_fence_id = -1";
            }
        }
        $data = $this->fenceDB->fenceList($where);

        //获取车辆和围栏关联id
        $carId = $this->fenceCarDB->field('device_id,e_fence_id')->select();
        //以围栏id为key重组车辆id数组
        foreach ($carId as $key => $val) 
        {
            $newCarId[$val['e_fence_id']][] = $val['device_id'];
        }
        foreach ($data as $key => $val) 
        {
            switch ($val['alarm_cond']) {
                case '1':
                    $data[$key]['work_term'] = '驶出';
                    break;
                case '2':
                    $data[$key]['work_term'] = '驶入驶出';
                    break;
                
                default:
                    $data[$key]['work_term'] = '驶入';
                    break;
            }
            $data[$key]['car_con'] = count($newCarId[$val['fence_id']]);
            $data[$key]['sendee_con'] = count(array_filter(explode(';',$val['sendee_phone'])));
        }
        echo json_encode(array('data' => $data));
        exit;
    }
    /**
    *添加电子围栏
    */
    public function addFence()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addFence',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('addFence');
    }
    /**
    *添加电子围栏数据处理
    */
    public function addFenceAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addFence',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        if(I('post.carStr'))
        {
            $carArr = explode(',',I('post.carStr'));
        }
        $sendee = explode(';',I('post.sendee_phone'));
        if(count($sendee) > 20)
        {
            echo json_encode(array('msg' => '接警人超过限制','status' => '0'));
            exit;
        }
        switch ( I('post.area_type')) {
            case 'rectangle':
            $region_type = 2;
                break;
            case 'circle':
            $region_type = 0;
                break;
            case 'polygon':
            $region_type = 5;
                break;
            default:
            $region_type = 0;
                break;
        }
        $array = array(
            'name' => I('post.fence_name'),
            'open_date' => I('post.open_time'),
            'close_date' => I('post.end_time'),
            'alarm_day' => I('post.work_day'),
            'alarm_start_time' => I('post.work_stime'),
            'alarm_end_time' => I('post.work_etime'),
            'alarm_interval' => I('post.work_rate'),
            'sendee_phone' => I('post.sendee_phone'),
            'alarm_cond' => I('post.work_term'),
            'region_name' => I('post.fence_area'),
            'region_type' => $region_type,
            // 'admin_area' => I('post.admin_area'),
            'region_gps' => htmlspecialchars_decode(I('post.area_val'))
            );
        //事务开启
        M()->startTrans();
        $id = $this->fenceDB->data($array)->add();
        
        $row = $this->addCar($id,$carArr);
        if($id > 0 && $row == count($carArr))
        {
            //事务提交
            M()->commit();
            $msg = '添加成功';
            $status = 1;
        }
        else
        {
            //事务回滚
            M()->rollback();
            $msg = '添加失败';
            $status = 0;
        }
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *修改电子围栏
    */
    public function editFence()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editFence',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('editFence');
    }
    /**
    *修改电子围栏数据处理
    */
    public function editFenceAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editFence',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        if(I('post.carStr'))
        {
            $carArr = explode(',',I('post.carStr'));
        }
        $sendee = explode(';',I('post.sendee_phone'));
        if(count($sendee) > 20)
        {
            echo json_encode(array('msg' => '接警人超过限制','status' => '0'));
            exit;
        }
        switch ( I('post.area_type')) {
            case 'rectangle':
            $region_type = 2;
                break;
            case 'circle':
            $region_type = 0;
                break;
            case 'polygon':
            $region_type = 5;
                break;
            default:
            $region_type = 0;
                break;
        }
        $array = array(
            'name' => I('post.fence_name'),
            'open_date' => I('post.open_time'),
            'close_date' => I('post.end_time'),
            'alarm_day' => I('post.work_day'),
            'alarm_start_time' => I('post.work_stime'),
            'alarm_end_time' => I('post.work_etime'),
            'alarm_interval' => I('post.work_rate'),
            'sendee_phone' => I('post.sendee_phone'),
            'alarm_cond' => I('post.work_term'),
            'region_name' => I('post.fence_area'),
            'region_type' => $region_type,
            // 'admin_area' => I('post.admin_area'),
            'region_gps' => htmlspecialchars_decode(I('post.area_val'))
            );
        //事务开启
        M()->startTrans();
        $id = $this->fenceDB->data($array)->where(array('e_fence_id' => I('post.fence_id')))->save();
        $row = $this->addCar(I('post.fence_id'),$carArr);
        if($id >= 0 && $row == count($carArr))
        {
            //事务提交
            M()->commit();
            $msg = '修改成功';
            $status = 1;
        }
        else
        {
            //事务回滚
            M()->rollback();
            $msg = '修改失败或未修改';
            $status = 0;
        }
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *添加围栏车辆
    */
    public function addCar($fenceId='0',$carArr='0')
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('fenceList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $fenceId = ($fenceId == '0') ? I('post.fence_id') : $fenceId;
        $carArr = ($carArr == '0') ? explode(',',I('post.carStr')) : $carArr;
        if(I('post.listAdd') && I('post.listAdd') == '1')
        {
            $row = '1';
        }
        else
        {
            $row = '0';
        }
        //根据围栏id获取围栏信息
        $info = $this->fenceDB->where(array('e_fence_id' => $fenceId))->find();
        if($info['is_available'] == '0')
        {
            //列表页添加标识
            if(I('post.listAdd') && I('post.listAdd') == '1')
            {
                echo json_encode(array('msg' => '操作失败,电子围栏已失效','status' => '0'));
                exit;
            }
            return $row = '-1';
            exit;
        }
        //根据围栏id获取已有围栏车辆id
        $fence_car = $this->fenceCarDB->field('device_id')->where(array('e_fence_id' => $fenceId))->select();
        foreach ($fence_car as $key => $val) 
        {
            $carId[] = $val['device_id'];
        }
        //定义围栏添加方式固定为0
        $info['flag'] = '0';
        //区域类型为圆形时
        if($info['region_type'] == '0')
        {
            $area_val = json_decode($info['region_gps'],true);
            $wgps = A('Coord')->bd_decrypt($area_val[0]['lat'],$area_val[0]['lng']);
            $info['longitude'] = $area_val[0]['lng'];
            $info['latitude'] = $area_val[0]['lat'];
            $info['gpsLongitude'] = $wgps[0];
            $info['gpsLatitude'] = $wgps[1];
            $info['range'] = $area_val[1];
        }
        //事务开启
        M()->startTrans();
        $this->redisDB->getRedis()->select(1);
        $this->redisDB->getCodis()->select(0);
        //先删除所有已有车辆的redis和信息
        if($carId)
        {
            $deviceWhere['device_id'] = array('in',implode(',',$carId));
            
            $device = $this->vehicleDB->field('device_no')->where($deviceWhere)->select();

            foreach ($device as $key => $val) 
            {
                $hasObd = $this->sessionArr['organ_channel_id'].':dvc_fence_'.$val['device_no']; 
                $this->redisDB->getRedis()->del($hasObd);
                $this->redisDB->getRedis()->hdel($this->sessionArr['organ_channel_id'].':obd_fence_data','dvc_'.$val['device_no']);
            }
            $del = $this->fenceCarDB->where(array('e_fence_id' => $fenceId))->delete();
        }
        //新增车辆相关redis和信息
        if(I('post.carStr'))
        {
            //根据车辆id获取设备号
            $where['device_id'] = array('in',implode(',',$carArr));
            $car_obd = $this->vehicleDB->field('device_id,car_id,device_no')->where($where)->select();

            foreach ($car_obd as $key => $val) 
            {
                $device_id[] = $val['car_id'];
            }
            $carWhere['car_id'] = array('in',implode(',',$device_id));
            $device = $this->carDB->getCarNoById($carWhere);
            foreach ($device as $key => $val) 
            {
                $new_device[$val['car_id']] = $val['car_no'];
            }
            foreach ($car_obd as $key => $val) 
            {
                $info['car_no'] = $new_device[$val['car_id']];
                //根据设备号获取车辆最后定位信息
                $obd = $this->redisDB->getRedis()->hget($this->sessionArr['organ_channel_id'].':car_gps_last',$val['device_no']);
                if($obd) 
                {
                    $info['device_id'] = $val['device_id'];
                    //当报警条件为驶离时gps信息为车辆gps
                    if($info['alarm_cond'] == '1')
                    {
                        $obdgps = json_decode($obd,true);
                        $obdwgps = A('Coord')->bd_decrypt($obdgps['gpsLatitude'],$obdgps['gpsLongitude']);
                        $info['longitude'] = $obdgps['gpsLongitude'];
                        $info['latitude'] = $obdgps['gpsLatitude'];
                        $info['gpsLongitude'] = $obdwgps[0];
                        $info['gpsLatitude'] = $obdwgps[1];
                    }
                }
                $this->redisDB->getRedis()->hmset($this->sessionArr['organ_channel_id'].':dvc_fence_'.$val['device_no'],$info);
                $this->redisDB->getRedis()->hset($this->sessionArr['organ_channel_id'].':obd_fence_data','dvc_'.$val['device_no'],$val['device_no']);
            }
            $row = $this->fenceDB->addCar($fenceId,$carArr);
        }
        if($del >= 0 && $row == count($carArr))
        {
            //事务提交
            M()->commit();
            $row = $row;
        }
        else
        {
            //事务回滚
            M()->rollback();
            $row = '-1';
        }
        //列表页添加标识
        if(I('post.listAdd') && I('post.listAdd') == '1')
        {
            $msg = ($row >= 0) ? '操作成功' : '操作失败';
            $status = ($row >= 0) ? 1 : 0;
            echo json_encode(array('msg' => $msg,'status' => $status));
            exit;
        }
        return $row;
    }
    
    /**
    *获取选择车辆信息
    */
    public function getCar()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('fenceList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        // if($this->ownOrgan())
        // {
        //     $where['organ_id'] = array('in',$this->ownOrgan());
        // }
        // else
        // {
        //     $where = array();
        // }
        //编辑页面获取围栏id
        if(I('get.id'))
        {
            //根据围栏id获取选中车辆信息
            $carId = $this->fenceCarDB->field('device_id')->where(array('e_fence_id' => I('get.id')))->select();
            foreach ($carId as $key => $val) 
            {
                $carArr[] = $val['device_id'];
            }
        }

        //获取车辆信息
        $carAll = json_decode($this->getCarJson($carArr),true);
        foreach ($carAll as $key => $val) 
        {
            $new_carAll[$val['id']] = $val;
            $device_id[] = $val['device_id'];
        }
        //获取设备号和企业机构信息
        if($device_id)
        {
            $deviceWhere['device_no'] = array('in',implode(',',$device_id));
        }
        $vehicle = $this->vehicleDB->getdata($deviceWhere,'belonged_organ_id as organ_id,device_id as id,device_no');
        //重组设备号信息
        foreach ($vehicle as $key => $val)
        {
            //以主键为key企业id为值
            $new_organ_id[$val['device_no']] = $val['organ_id'];
        }
        //重组车辆信息
        foreach ($carAll as $key => $val)
        {
            //以企业id为key重组没有车组的设备主键
            if(!$val['group_id'])
            {
                $ng_device[$new_organ_id[$val['device_id']]][] = $new_carAll[$val['id']];
            }
            else
            {
                $new_car[$val['group_id']][] = $new_carAll[$val['id']];
            }
        }

        //获取车组和企业机构信息
        $group = $this->groupDB->getdata(array(),'belonged_organ_id as organ_id,group_id,group_name');
        //重组车组信息
        foreach ($group as $key => $val)
        {
            //以企业id为key车组设备号信息为值
            $val['car'] = array_filter($new_car[$val['group_id']]);
            //以企业id为key车组为值
            $new_group[$val['organ_id']][] = $val;
        }
        $data = $this->getOrganList($this->sessionArr['parent_organ_id'],'',$ng_device,$new_group);
        if(empty($data))
        {
            $data = array();
        }
        echo json_encode(array('data' => $data));
        exit;
        
    }
    /**
    *车辆位置展示的企业list无限极递归函数
    */
    public function getOrganList($pid = '0',$otherWhere = '',$ng_device,$new_group)
    {
        $where = "1=1 and organ.parent_organ_id = '{$pid}' and organ.is_available = '1'".$otherWhere;
        if($this->ownOrgan())
        {
            $where .= " and organ.organ_id in (".$this->ownOrgan().")";
        }
        else
        {
            $where .= " and organ.channel_id = '".$this->sessionArr['organ_channel_id']."'";
        }
        $list = $this->organDB->organList($where,'organ_id,organ_name');
        if($list)
        {
            foreach ($list as $key => $val) 
            {
                if ($val['organ_id'])
                {
                    $val['car'] = $ng_device[$val['organ_id']];
                    $val['group'] = $new_group[$val['organ_id']];
                    $val['son'] = $this->getOrganList($val['organ_id'],$otherWhere,$ng_device,$new_group);
                }
                $array[] = $val;
            }
        }
        return $array;
    }
    /**
    *获取GPS信息
    */
    public function getCarJson($fenceCar = array())
    {
        $where = '1=1';
        //获取企业机构信息
        if($this->ownOrgan())
        {
            $where .= " and v.belonged_organ_id in (".$this->ownOrgan().")";
        }
        else
        {
            $where .= " and v.belonged_organ_id = -1";
        }

        //获取车辆信息
        $where .= " and v.status = 1";
        //获取车辆信息
        $data = $this->carDB->getCarInfoList($where);

        foreach ($data as $key => $val) 
        {
            $dataArr[$key]['id'] = $val['device_id'];
            $dataArr[$key]['device_id'] = $val['device_no'];
            $dataArr[$key]['organ_id'] = $val['organ_id'];
            $dataArr[$key]['organ_name'] = $val['organ_name'];
            $dataArr[$key]['car_id'] = $val['car_id'];
            $dataArr[$key]['car_no'] = $val['plate_no'];
            $dataArr[$key]['group_id'] = $val['group_id'];
            $dataArr[$key]['group_name'] = $val['group_name'];
        }
        $i = 0;
        $carAll = array();

        foreach ($dataArr as $key => $val) 
        {
            //设备号
            $arr = array();
            $arr['id'] = $val['id'];
            $arr['device_id'] = $val['device_id'];
            //车辆信息
            $arr['organ_id'] = $val['organ_id'];
            $arr['organ_name'] = $val['organ_name'];
            $arr['group_id'] = $val['group_id'];
            $arr['group_name'] = $val['group_name'];
            if(in_array($val['id'],$fenceCar))
            {
                $val['is_click'] = '1';
            }
            else
            {
                $val['is_click'] = '0';
            }
            $arr['car_id'] = $val['car_id'];
            $arr['is_click'] = $val['is_click'];
            $arr['car_no'] = $val['car_no'];

            $carAll[$i] = $arr;
            $i++;
        }
        return json_encode($carAll);
        exit;
    }

    /**
    *根据围栏id获取围栏信息
    */
    public function getFenceById()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editFence',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $info = $this->fenceDB->field('e_fence_id as fence_id,name as fence_name,alarm_cond as work_term,open_date as open_time,close_date as end_time,alarm_day as work_day,alarm_start_time as work_stime,alarm_end_time as work_etime,sendee_phone,is_available as is_use,region_type,region_gps as area_val,region_name as fence_area')->where(array('e_fence_id' => I('get.id')))->find();
        switch ($info['region_type']) {
            case '2':
            $info['area_type'] = 'rectangle';
                break;
            case '0':
            $info['area_type'] = 'circle';
                break;
            case '5':
            $info['area_type'] = 'polygon';
                break;
            default:
                break;
        }
        // $carId = $this->fenceCarDB->field('device_id')->where(array('e_fence_id' => I('get.id')))->select();
        // foreach ($carId as $key => $val) 
        // {
        //     $carArr[$key] = $this->carDB->getInfo(array('car_id' => $val['car_id']),'car_id,car_no,car_group');
        // }
        // $info['carArr'] = $carArr;
        echo json_encode($info);
        exit;
    }
    /**
    *获取选中车辆信息
    */
    public function getCarById()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('fenceList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        if(I('get.id'))
        {
            $carId = $this->fenceCarDB->field('device_id')->where(array('e_fence_id' => I('get.id')))->select();
            foreach ($carId as $key => $val) 
            {
                $carArr[$key] = $val['device_id'];
            }
            $carStr = $carArr ? implode(',',$carArr) : '-1';
        }
        else
        {
            $carStr = I('post.carStr');
        }
        $data = $this->fenceDB->getCarById($carStr);
        echo json_encode(array('data' => $data));
        exit;
    }
    /**
    *修改围栏状态
    */
    public function editStatus()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('fenceList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $id = $this->fenceDB->data(array('is_available' => I('post.is_use')))->where(array('e_fence_id' => I('get.id')))->save();
        //电子围栏改为无效则删除redis
        if(I('post.is_use') == '0')
        {
            //根据围栏id获取已有围栏车辆id
            $fence_car = $this->fenceCarDB->field('device_id')->where(array('e_fence_id' => I('get.id')))->select();
            foreach ($fence_car as $key => $val) 
            {
                $carId[] = $val['device_id'];
            }
            $this->redisDB->getRedis()->select(1);
            if($carId)
            {
                $obdWhere['device_id'] = array('in',implode(',',$carId));
                
                $device = $this->vehicleDB->field('device_no')->where($obdWhere)->select();

                foreach ($device as $key => $val) 
                {
                    $hasObd = $this->sessionArr['organ_channel_id'].':dvc_fence_'.$val['device_no']; 
                    $this->redisDB->getRedis()->del($hasObd);
                    $this->redisDB->getRedis()->hdel($this->sessionArr['organ_channel_id'].':obd_fence_data','dvc_'.$val['device_no']);
                }
            }
        }
        $status = ($id > 0) ? '1': '0' ;
        $msg = ($id > 0) ? '围栏状态修改成功': '围栏状态修改失败' ;
        echo json_encode(array('status' => $status,'msg' => $msg));
        exit;
    }

}
<?php
namespace Home\Controller;

use Home\Common\RedisModel;
use Home\Model\CarGroupModel;
use Home\Model\CarModel;
use Home\Model\DriverModel;
use Home\Model\VehicleModel;
use Home\Model\VenderModel;

class CarController extends BaseController
{
    private $driverDB;
    private $carDB;
    private $groupDB;
    private $vehicleDB;
    private $redisDB;

    function __construct()
    {
        parent::__construct();
        $this->driverDB = new DriverModel($this->bizDB, 'tp_');
        $this->carDB = new CarModel($this->bizDB, 'tp_');
        $this->groupDB = new CarGroupModel($this->bizDB, 'tp_');
        $this->vehicleDB = new VehicleModel($this->bizDB, 'tp_');
        $this->redisDB = new RedisModel();
    }
    /**
    *车辆列表页
    */
    public function carList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('carList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $this->display('carList');
    }

    /**
    *获取车辆信息
    */
    public function getInfo()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('carList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $where = "1=1";
        if($this->ownOrgan())
        {
            $where .= " and v.belonged_organ_id in (".$this->ownOrgan().")";
        }
        //车牌号
        if(I('param.car_no'))
        {
            $where .= " and cp.plate_no like '%".I('param.car_no')."%'";
        }
        //车辆状态
        if(I('param.car_status') != '')
        {
            $where .= " and car.status = ".I('param.car_status');
        }

        //所属车组
        if(I('param.car_group') != '')
        {
            $where .= " and car.group_id = '".I('param.car_group')."'";
        }
        //绑定状态
        switch (I('param.is_use')) 
        {
            case '-2':
                $where .= " and v.is_binded_user = 0";
                $where .= " and v.is_binded_car = 0";
                break;
            case '1':
                $where .= " and v.is_binded_user = 1";
                $where .= " and v.is_binded_car = 0";
                break;
            case '2':
                $where .= " and v.is_binded_user = 0";
                $where .= " and v.is_binded_car = 1";
                break;
            case '3':
                $where .= " and v.is_binded_user = 1";
                $where .= " and v.is_binded_car = 1";
                break;
            default:

                break;
        }

        //设备状态
        if(I('param.active_status') != '')
        {
            if(I('param.active_status') == 2)
            {
                $new_dvc = $codis_dvc = array();
                $wrongArr['belonged_organ_id'] = array('in',$this->ownOrgan());
                $wrongArr['status'] = 1;
                $wrongDvc = $this->vehicleDB->field('device_no')->where($wrongArr)->select();
                foreach ($wrongDvc as $key => $val) 
                {
                    $new_dvc[] = $val['device_no'];
                }
                $codisData = $this->redisDB->getCodis()->hgetAll($this->sessionArr['organ_channel_id'].':car_gps_last');
                //获取codis里的设备号
                $codis_dvc = array_keys($codisData);
                $dvc_diff = array_values(array_diff($new_dvc,$codis_dvc));
                if($dvc_diff)
                {
                    $where .= " and v.device_no in (".implode(',',$dvc_diff).")";
                }
                else
                {
                    $where .= " and v.device_id = 0";
                }
            }
            else
            {
                $where .= " and v.status = ".I('param.active_status');  
            }
        }
        //设备号搜索
        if(I('param.device_id'))
        {
            $where .= " and v.device_no like '%".I('param.device_id')."%'";
        }
        // 查询满足要求的总记录数
        $count = $this->carDB->carCnt($where);

        // 实例化分页类
        $page = $this->getPage($count[0]['cnt'],$where);

        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $data = $this->carDB->carList($where,$page['firstRow'],$page['listRows']);

        foreach ($data as $key => &$val) 
        {
            switch ($val['car_status']) 
            {
                case '1':
                    $car_status = "维修";
                    break;
                case '2':
                    $car_status = "保养";
                    break;
                default:
                    $car_status = "正常";
                    break;
            }
            $data[$key]['car_status'] = $car_status;

            switch ($val['active_status']) 
            {
                case '1':
                    $active_status = "已激活";
                    break;
                case '2':
                    $active_status = "已损坏";
                    break;
                default:
                    $active_status = "未激活";
                    break;
            }
            $data[$key]['active_status'] = $active_status;

            switch (true) 
            {
                case ($val['is_car'] == 0 && $val['is_use'] == 0):
                    $bind_status = "未绑定";
                    break;
                case ($val['is_car'] == 0 && $val['is_use'] == 1):
                    $bind_status = "已绑定手机";
                    break;
                case ($val['is_car'] == 1 && $val['is_use'] == 0):
                    $bind_status = "已绑定车辆";
                    break;
                case ($val['is_car'] == 1 && $val['is_use'] == 1):
                    $bind_status = "已绑定车辆&手机";
                    break;
            }
            $data[$key]['bind_status'] = $bind_status;
            $val = array_map(array($this, 'filterNull'), $val);
        }
        
        echo json_encode(array('data' => $data,'page' => $page['show']));
        exit;
    }
    /**
    *修改车辆页
    */
    public function editCar()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editCar',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        //设备信息
        $info = $this->carDB->getCarInfoByDvcId(I('get.id'));
        $info = $info[0];
        //司机信息
        $driver = $this->driverDB->getData([],'driver_id,name as driver_name');
        //车组信息
        $group = $this->groupDB->getData(array('belonged_organ_id' => $info['belonged_organ_id']),'group_id,group_name');
        $info['car_brand'] = $info['car_brand'].' - '.$info['car_series'];
        $info['car_series'] = $info['car_type'];

        $this->assign('info',$info);
        $this->assign('driver',$driver);
        $this->assign('group',$group);
        $this->display('editCar');
    }
    /**
    *修改车辆数据处理
    */
    public function editCarAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editCar',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $carId = $this->vehicleDB->field('car_id')->where(array('device_id' =>  I('param.device_id')))->find();
        $plateCarId = $this->carDB->getCarIdByNo(array('plate_no' => I('param.car_no')),'car_id');

        if($carId && $plateCarId && $plateCarId['car_id'] != $carId['car_id'])
        {
            echo json_encode(array('msg' => '车牌号已存在，请重新输入','status' => 0));
            exit;
        }

        $array = array(
            // 'car_brand_id' => I('param.car_band'),
            // 'car_series_id' => I('param.car_serious'),
            'car_type_id' => I('param.car_serious'),
            'function' => I('param.car_use'),
            'purchase_time' => I('param.car_buy_time'),
            'group_id' => I('param.car_group'),
            '100_km_oil_wear' => I('param.car_oil') ? I('param.car_oil') : 0,
            'upkeep_type' => I('param.car_care'),
            'upkeep_interval' => I('param.car_care_val'),
            '4s_shop_phone' => I('param.fours_number'),
            'status' => I('param.car_status'),
            'next_upkeep_time' => I('param.next_care_time') ? I('param.next_care_time') : date("Y-m-d"),
            'engine_no' => I('param.e_code'),
            'vin' => I('param.v_code')
        );
        

        //事务开启
        M()->startTrans();
        if($carId['car_id'])
        {
            $plateId = $this->carDB->getCarIdByNo(array('car_id' => $carId['car_id']),'plate.plate_id');
            M("$this->bizDB.plate")->where(array('plate_id' => $plateId['plate_id']))->save(array('plate_no' => I('param.car_no')));
            M("$this->bizDB.car_driver_rel")->where(array('car_id' => $carId['car_id']))->delete();
            M("$this->bizDB.car_driver_rel")->add(array('car_id' => $carId['car_id'],'driver_id' => I('param.driver_id')));
            $insertId = $this->carDB->editCar(array('car_id' => $carId['car_id']),$array);
        }
        else
        {
            $insertId = $this->carDB->addCar($array);
            $plateId = M("$this->bizDB.plate")->add(array('plate_no' => I('param.car_no')));
            M("$this->bizDB.car_plate_rel")->add(array('plate_id' => $plateId,'car_id' => $insertId));
            M("$this->bizDB.car_driver_rel")->add(array('driver_id' => I('param.driver_id'),'car_id' => $insertId));
            $this->vehicleDB->editVehicle(array('device_id' => I('param.device_id')),array('is_binded_car' => 1,'car_id' => $insertId));
        }

        if($insertId >= 0)
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
            $msg = '修改失败或无修改';
            $status = 0;
        }

        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *获取司机信息
    */
    public function getDriver()
    {
        $info = $this->driverDB->getInfo(array('driver_id' => I('param.driver_id')),'phone,license_start_time');

        echo json_encode($info);
        exit;
    }
    /**
    *获取车组信息
    */
    public function getCarGroup()
    {
        //根据设备号获取企业id
        $organ_id = $this->vehicleDB->getInfo(array('device_id' => I('param.id')),'belonged_organ_id');
        $data = $this->groupDB->getData(array('belonged_organ_id' => $organ_id['belonged_organ_id']),'group_id,group_name');
        echo json_encode($data);
        exit;
    }
    /**
    *车辆位置页
    */
    public function carPlace()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('carPlace',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('carPlace');
    }
    /**
    *车辆位置数据
    */
    public function getCarData()
    {
        //获取redis内的车辆信息
        $carAll = json_decode($this->getCarJson('return'),true);
        $device_id = [];
        foreach ($carAll as $key => $val) 
        {
            $new_carAll[$val['id']] = $val;
            $device_id[] = $val['id'];
            $new_organ_id[$val['id']] = $val['organ_id'];
        }
        if($device_id)
        {
            $carWhere['v.device_id'] = array('in',implode(',', $device_id));
        }
        else
        {
            $carWhere['v.device_id'] = -1;
        }

        $car = M("{$this->bizDB}.car")
            ->alias('car')
            ->join("{$this->bizDB}.tp_user u on u.car_id = car.car_id")
            ->join("$this->bizDB.tp_device as v on u.device_id = v.device_id")
            ->field('car.car_id,v.device_id, car.group_id')
            ->where($carWhere)
            ->select();
        $sql = M("{$this->bizDB}.car")->getLastSql();
        //重组车辆信息
        foreach ($car as $key => $val)
        {
            if($val['device_id'])
            {
                //以企业id为key重组没有车组的设备主键
                if($val['group_id'] == '0')
                {
                    $ng_device[$new_organ_id[$val['device_id']]][] = $new_carAll[$val['device_id']];
                }
                if($val['group_id']) 
                {
                    $new_car[$val['group_id']][] = $new_carAll[$val['device_id']];
                }
            }
        }
        //获取车组和企业机构信息
        $group = $this->groupDB->getdata(array(),'belonged_organ_id,group_id,group_name');
        //重组车组信息
        foreach ($group as $key => $val)
        {
            //以企业id为key车组设备号信息为值
            $val['car'] = array_filter($new_car[$val['group_id']]);
            //以企业id为key车组为值
            $new_group[$val['belonged_organ_id']][] = $val;
        }
        $data = $this->getOrganList($this->sessionArr['parent_organ_id'],'',$ng_device,$new_group);
        if(empty($data))
        {
            $data = array();
        }
        echo json_encode($data);
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
            $where .= " and organ.channel_id = '".$this->sessionArr['organ_channel_id']."' and organ.organ_id in (".$this->ownOrgan().")";
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
    *获取车牌号
    */
    public function getCarNo()
    {
        //根据车牌号获取设备号
        $car = $this->carDB->getInfo(array('car_no' => I('param.car_no')),'device_id,car_group,car_no');
        $group = $this->groupDB->getInfo(array('group_id' => $car['car_group']),'group_name,organ_id');
        $organ = $this->organDB->field('organ_name')->where(array('organ_id' => $group['organ_id']))->find();
        $car['organ_name'] = $organ['organ_name'];
        $car['group_name'] = $group['group_name'];

        echo json_encode($car);
        exit;
    }
    /**
    *获取GPS信息
    */
    public function getCarJson($result = '')
    {
        $where = '1=1';
        //从redis获取车辆最后定位信息
        $this->redisDB->getCodis()->select(0);
        $redis_car = $this->redisDB->getCodis()->hgetall($this->sessionArr['organ_channel_id'].':car_gps_last');
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
        $data = $this->carDB->getCarInfoList($where);

        $dataArr = $dvcNoArr = $odbidArr = array();
        //获取redis里的设备主键
        $odbidArr = array_keys($redis_car);

        foreach ($data as $key => $val) 
        {
            if(in_array($val['device_no'],array_values($odbidArr)))
            {
                $dataArr[$key]['id'] = $val['device_id'];
                $dataArr[$key]['device_id'] = $val['device_no'];
                $dataArr[$key]['organ_id'] = $val['organ_id'];
                $dataArr[$key]['organ_name'] = $val['organ_name'];
                $dataArr[$key]['redis'] = $redis_car[$val['device_no']];
                $dataArr[$key]['car_id'] = $val['car_id'];
                $dataArr[$key]['car_brand'] = $val['car_brand'];
                $dataArr[$key]['car_no'] = $val['plate_no'];
                $dataArr[$key]['group_id'] = $val['group_id'];
                $dataArr[$key]['group_name'] = $val['group_name'];
                $dataArr[$key]['driver_name'] = $val['driver_name'];
                $dataArr[$key]['driver_phone'] = $val['driver_phone'];
            }
        }
        $i = 0;
        $carAll = array();

        foreach ($dataArr as $key => $val) 
        {
            //设备号
            $carAll[$i]['id'] = $val['id'];
            $carAll[$i]['device_id'] = $val['device_id'];

            //转换redis获取的设备号的json数据
            $str = json_decode($val['redis'],true);
            //根据设备号存储坐标转换百度坐标
            $gpsStr = A('Coord')->gcj_encrypt($str['gpsLatitude'], $str['gpsLongitude']);
            //纬度
            $carAll[$i]['geolatitude'] = $gpsStr[0];
            //经度
            $carAll[$i]['geolongitude'] = $gpsStr[1];
            //时间
            $carAll[$i]['gpsTime'] = date("Y-m-d H:i:s",round($str['gpsTime']/1000));
            //车速
            $carAll[$i]['gpsSpeed'] = ($str['msgType'] != 4 && $str['gpsSpeed'] >= 1) ? round($str['gpsSpeed'] * 3.6,2) :'0';
            //汽车状态
            if(array_key_exists('msgType', $str) && $str['msgType'] == 4 || (time()*1000 - $str['gpsTime']) > 360*1000)
            {
                $status = "off";
            } 
            else 
            {
                if(array_key_exists('gpsSpeed', $str))
                {
                    if($str['gpsSpeed'] < 1)
                    {
                        $status = "stop";
                    }
                    else
                    {
                        $status = "on";
                    }
                }
            }

            $carAll[$i]['status'] = $status;
            //行驶方向
            if($str['gpsOrientation'] % 90 == 0)
            {
                switch($str['gpsOrientation'])
                {
                    case 0:
                        $pos = "正北";
                        break;
                    case 90:
                        $pos = "正东";
                        break;
                    case 180:
                        $pos = "正南";
                        break;
                    default:
                        $pos = "正西";
                        break; 
                }                      
            }
            else
            {
                if($str['gpsOrientation'] < 90)
                {
                    $pos = "东北";
                }
                else if($str['gpsOrientation'] < 180)
                {
                    $pos = "东南";
                }
                else if($str['gpsOrientation'] < 270)
                {
                    $pos = "西南";
                }
                else
                {
                    $pos = "西北";
                }
            }
            $carAll[$i]['pos'] = $pos;
            //车辆信息
            $carAll[$i]['organ_id'] = $val['organ_id'];
            $carAll[$i]['organ_name'] = $val['organ_name'];
            $carAll[$i]['group_id'] = $val['group_id'];
            $carAll[$i]['group_name'] = $val['group_name'];
            $carAll[$i]['car_id'] = $val['car_id'];
            $carAll[$i]['is_click'] = $val['is_click'];
            $carAll[$i]['car_brand'] = $val['car_brand'];
            $carAll[$i]['car_no'] = $val['car_no'];
            //司机信息
            $carAll[$i]['driver_name'] = $val['driver_name'];
            $carAll[$i]['driver_phone'] = $val['driver_phone'];
            $i++;
        }
        if($result == "return")
        {
            return json_encode($carAll);
        }
        else
        {
            echo json_encode($carAll);
        }
        exit;
    }
}
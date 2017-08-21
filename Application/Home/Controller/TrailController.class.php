<?php
namespace Home\Controller;

use Home\Model\CarGroupModel;
use Home\Model\CarModel;
use Home\Model\TrailModel;
use Home\Model\VehicleModel;

class TrailController extends BaseController
{
    private $carDB;
    private $groupDB;
    private $vehicleDB;
    private $trailDB;

    function __construct()
    {
        parent::__construct();
        $this->carDB = new CarModel($this->bizDB, 'tp_');
        $this->groupDB = new CarGroupModel($this->bizDB, 'tp_');
        $this->vehicleDB = new VehicleModel($this->bizDB, 'tp_');
        $this->trailDB = new TrailModel($this->getNowDB(), $this->bizDB);
    }
    /**
     *车辆轨迹列表页
     */
    public function trailList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('trailList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $group = $this->groupDB->getData([],'group_id,group_name');
        $date = array(
            'startDate' => date('Y-m-d',strtotime('-2 days')),
            'endDate' => date('Y-m-d'),
        );

        $this->assign('group',$group);
        $this->assign('date',$date);
        $this->display('trailList');
    }
    /**
     *获取车辆轨迹
     */
    public function getTrail()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('trailList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $where = "1=1";
        if($this->ownOrgan())
        {
            $where .= " and v.belonged_organ_id in (".$this->ownOrgan().")";
        }
        else
        {
            $where .= " and v.belonged_organ_id = -1";
        }
        //查询数据库条件
        $dbWhere['TABLE_SCHEMA'] = array('like','ubi_'.$this->sessionArr['organ_channel_id'].'_%');
        $dbWhere['TABLE_NAME'] = array('like','%tp_sgl_jny_analysis_%');
        //获取数据库名称
        $dbName = D('GetDB')->dbName($dbWhere);
        //查询车牌号
        if(I('param.car_no'))
        {
            $where .= " and cp.plate_no like '" . I('param.car_no') . "%'";
        }
        //查询设备号
        if(I('param.device_no'))
        {
            $where .= " and v.device_no like '".I('param.device_no')."%'";
        }
        //查询行程时间段
        if(I('param.trailtime'))
        {
            $time = explode(' - ',I('param.trailtime'));
            $endTime = strtotime($time[1]) + 3600*24;
            $where .= " and jny.end_time between ".strtotime($time[0])." and ".$endTime;
        }
        else
        {
            $endTime = strtotime(date('Y-m-d')) + 3600*24;
            $where .= " and jny.end_time between ".strtotime(date('Y-m-d',strtotime('-2 days')))." and ".$endTime;
        }
        //查询车辆分组
        if(I('param.car_group'))
        {
            $where .= " and car.group_id = " . I('param.car_group');
        }
        //数据数量
        $count = $this->trailDB->locusListCnt($this->bizDB,$dbName,$where);
        // 实例化分页类
        $page = $this->getPage($count[0]['cnt'],$where);

        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $data = $this->trailDB->locusList($this->bizDB,$dbName,$where,$page['firstRow'],$page['listRows']);

        foreach ($data as $key => &$val)
        {
            $val['start_time'] = date('Y-m-d H:i:s',$val['start_time']);
            $val['end_time'] = date('Y-m-d H:i:s',$val['end_time']);
            $val = array_map(array($this, 'filterNull'), $val);
            $data[$key] = $val;
        }
        if(I('param.fileStatus') == 1)
        {
            $checkArr = explode(',',I('param.checkArr'));
            foreach ($data as $key => $val)
            {
                if(in_array($val['id'],$checkArr))
                {
                    $arr[] = $val;
                }
            }
            A('Excel')->trailOut(array_merge($arr));
            exit;
        }
        echo json_encode(array('data' => $data,'page' => $page['show']));
        exit;
    }
    /**
     *字符串加引号
     */
    public function turnString($v)
    {
        if($v)
        {
            return "'".$v."'";
        }
    }
    /**
     *查看行程页
     */
    public function checkInfo()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('checkInfo',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $channelId = $this->sessionArr['organ_channel_id']."_2";
        //获取临时行程表
        $jnytb = A('DBUtil')->getJnyTableName($channelId, I('get.user_id'), strtotime(I('get.start_time')) ,$schemaPrefix = 'org_' ,$tablePrefix = 'tp_dvc_org_jny_');
        $is_virtual_user = $this->trailDB->getInfo($jnytb,'is_virtual_user'," and jny_id = '".I('get.jny_id')."'");
        $channelId = $this->sessionArr['organ_channel_id']."_".$is_virtual_user[0]['is_virtual_user'];
        //获取行程表 
        $locustb = A('DBUtil')->getJnyTableName($channelId, I('get.user_id'), strtotime(I('get.start_time')));
        //行程信息
        $locus = $this->trailDB->getInfo($locustb,"id,jny_id,device_id,start_time,end_time,distance_travelled,distance_units,duration,oil_wear,start_address,end_address,risk_score,accel_count,decel_count,turn_count,max_speed,create_time","and id = '".I('get.id')."'");
        $info = $locus[0];
        //车辆信息
        $device_id = $this->vehicleDB->getInfo(array('device_id' => $info['device_id']),'device_no,car_id');
        $info['device_id'] = $device_id['device_no'];
        $carNo = $this->carDB->getCarIdByNo(array('car_id' => $device_id['car_id']),'plate_no');
        $info['car_no'] = $carNo['plate_no'];
        //司机信息
        $driver = M("$this->bizDB.driver")
            ->alias('d')
            ->join("$this->bizDB.tp_car_driver_rel as cdr on d.driver_id = cdr.driver_id")
            ->field('name,phone')
            ->where(array('car_id' => $device_id['car_id']))->find();
        $info['driver_name'] = $driver['name'];
        $info['driver_phone'] = $driver['phone'];

        $this->assign('info',$info);
        $this->display('checkInfo');
    }
    /**
     *查看轨迹页
     */
    public function checkTrail()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('checkTrail',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $channelId = $this->sessionArr['organ_channel_id']."_2";
        $start_time = I("get.start_time");
        if (strpos($start_time, "%20")) {
            $start_time = str_replace("%20", " ", I("get.start_time"));
        }

        //获取临时行程表
        $jnytb = A('DBUtil')->getJnyTableName($channelId,
            I('get.user_id'),
            strtotime($start_time),
            $schemaPrefix = 'org_',
            $tablePrefix = 'tp_dvc_org_jny_'
        );
        $is_virtual_user = $this->trailDB->getInfo($jnytb,'is_virtual_user'," and jny_id = '".I('get.jny_id')."'");

        $channelId = $this->sessionArr['organ_channel_id']."_".$is_virtual_user[0]['is_virtual_user'];
        //获取行程表 
        $locustb = A('DBUtil')->getJnyTableName($channelId, I('get.user_id'), strtotime($start_time));
        //行程信息
        $locus = $this->trailDB->getInfo($locustb,"device_id","and id = '".I('get.id')."'");
        $info = $locus[0];
        //车辆信息
        $device_id = $this->vehicleDB->getInfo(array('device_id' => $info['device_id']),'device_no,car_id');
        $info['device_id'] = $device_id['device_no'];
        $carNo = $this->carDB->getCarIdByNo(array('car_id' => $device_id['car_id']),'plate_no');
        $car['car_no'] = $carNo['plate_no'];
        $brandId = $this->carDB->field('car_type_id')->where(array('car_id' => $device_id['car_id']))->find();
        $brand = M('biz.car_type_view')->field('car_brand')->where(array('car_type_id' => $brandId['car_type_id']))->find();
        $car['car_brand'] = $brand['car_brand'];
        //司机信息
        $driver = M("$this->bizDB.driver")
            ->alias('d')
            ->join("$this->bizDB.tp_car_driver_rel as cdr on d.driver_id = cdr.driver_id")
            ->field('name,phone')
            ->where(array('car_id' => $device_id['car_id']))->find();
        $car['driver_name'] = $driver['name'];
        $car['driver_phone'] = $driver['phone'];
        //获取轨迹表 
        $gpstb = A('DBUtil')->getJnyTableName($channelId, I('get.user_id'), strtotime($start_time), $schemaPrefix = 'ubi_', $tablePrefix = 'tp_sgl_jny_path_');
        $jnytb = A('DBUtil')->getJnyTableName($channelId, I('get.user_id'), strtotime($start_time), $schemaPrefix = 'ubi_', $tablePrefix = 'tp_sgl_jny_analysis_');
        $where = " and t1.jny_id = '".I('get.jny_id')."'";
//        $path = $this->trailDB->getInfo($gpstb,'path',$where);

        $pathList = $this->trailDB->getTrailInfo([$jnytb, $gpstb], 't1.data_ver, t2.path', $where);

        $path = json_decode($pathList[0]['path'],true);
        $dataVersion = $pathList[0]['data_ver'];


        foreach ($path as $key => $val)
        {
            //$gpsStr = A('Coord')->gcj_encrypt($val['latitude'], $val['longitude']);
            if ($dataVersion == 1) {
                //纬度
                $gps[$key]['geolatitude'] = $val['latitude'];
                //经度
                $gps[$key]['geolongitude'] = $val['longitude'];
                //速度
                $gps[$key]['gpsspeed'] = $val['eventSpeed'];
                $gps[$key]['gpstime'] = $val['eventTime'];
                $gps[$key]['gpsorientation'] = $val['bearing'];
            } else {
                //纬度
                $gps[$key]['geolatitude'] = $val['lat'];
                //经度
                $gps[$key]['geolongitude'] = $val['lng'];
                //速度
                $gps[$key]['gpsspeed'] = $val['spd'];
                $gps[$key]['gpstime'] = $val['tme'];
                $gps[$key]['gpsorientation'] = $val['ori'];
            }
        }

        $this->assign('car',$car);
        $this->assign('gps',$gps);
        $this->display('checkTrail');
    }
}
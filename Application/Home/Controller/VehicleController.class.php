<?php

namespace Home\Controller;


use Home\Common\RedisModel;
use Home\Model\CarModel;
use Home\Model\DeviceModel;
use Home\Model\DeviceModelModel;
use Home\Model\UserModel;
use Home\Model\VehicleModel;
use Home\Model\VenderModel;

ini_set('memory_limit', '-1');  //所需内存
class VehicleController extends BaseController
{
    private $userDB;
    private $venderDB;
    private $deviceDB;
    private $deviceModelDB;
    private $carDB;
    private $vehicleDB;
    private $redisDB;

    function __construct()
    {
        parent::__construct();
        $this->userDB = new UserModel($this->bizDB, 'tp_');
        $this->venderDB = new VenderModel('auth', 'tp_organ_type');
        $this->deviceDB = new DeviceModel('biz', 'tp_device_type');
        $this->deviceModelDB = new DeviceModelModel('biz', 'tp_device_series');
        $this->carDB = new CarModel($this->bizDB, 'tp_');
        $this->vehicleDB = new VehicleModel($this->bizDB, 'tp_');
        $this->redisDB = new RedisModel();
    }

    /**
     *添加设备页
     */
    public function index()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addVehicle', 0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        //硬件厂家
        $where['coop_type_id'] = array('like','%2%');
        $where['is_available'] = 1;
        $where['organ_type_id'] = C("ORGAN_TYPE_ID");
        $vender = $this->organDB->where($where)->field('organ_id as vender_id,organ_name as vender_name')->select();

        $this->assign('vender', $vender);
        $this->display('addVehicle');
    }

    /**
     *添加用户数据处理
     */
    public function addVehicleAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addVehicle', 1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $device_id = $this->vehicleDB->getInfo(array('device_no' => I('post.device_id')), 'device_id');

        if (!empty($device_id)) {
            echo json_encode(array('msg' => '设备号已存在，请重新输入', 'status' => 0));
            exit;
        }
        $organ_id = I('post.organ_id') ? I('post.organ_id') : $this->sessionArr['organ_id'];
        $array = array(
            "device_no" => strtoupper(trim(I('post.device_id'))),
            "supplied_organ_id" => I('post.vender_id'),
            "device_series_id" => I('post.device_model'),
            "belonged_organ_id" => $organ_id
        );

        //事务开启
        M()->startTrans();

        $this->redisDB->getRedis()->select(0);
        //redis事务
        $this->redisDB->getRedis()->multi();
        $this->redisDB->getRedis()->hset($this->sessionArr['organ_channel_id'] . ':hardwareInfo', I('post.device_id'), $organ_id);

        $insertId = $this->vehicleDB->add($array);
        $this->redisDB->getRedis()->hset($this->sessionArr['organ_channel_id'] . ':device_pk', I('post.device_id'), $insertId);
        if ($insertId > 0) {
            //事务提交
            M()->commit();
            $this->redisDB->getRedis()->exec();
            $msg = '添加成功！';
            $status = 1;
        } else {
            //事务回滚
            M()->rollback();
            $msg = '添加失败！';
            $status = 0;
        }

        echo json_encode(array('msg' => $msg, 'status' => $status));
        exit;
    }

    /**
     *设备列表页
     */
    public function vehicleList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('vehicleList', 0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        //硬件厂家
        $where['coop_type_id'] = array('like', '%2%');
        $where['is_available'] = 1;
        $where['organ_type_id'] = C("ORGAN_TYPE_ID");
        $vender = $this->organDB->where($where)->field('organ_id as vender_id,organ_name as vender_name')->select();

        $this->assign('vender', $vender);
        $this->display('vehicleList');
    }

    /**
     *获取设备信息
     */
    public function getInfo()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('vehicleList', 1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $where = "1=1";
        if ($this->ownOrgan()) {
            $where .= " and v.belonged_organ_id in (" . $this->ownOrgan() . ")";
        }
        //设备号搜索
        if (I('param.device_id')) {
            $where .= " and v.device_no like '%" . I('param.device_id') . "%'";
        }
        //vin搜索
        if (I('param.vin')) {
            $where .= " and car.vin like '". I('param.vin') ."'";
        }
        //激活时间搜索
        if (I('param.active_time')) {
            $active_time = explode(' - ', I('param.active_time'));
            $where .= " and v.activated_time >= '{$active_time[0]} 00:00:00' 
                        and v.activated_time <= '{$active_time[1]} 59:59:59'";
        }
        //设备状态搜索
        if (I('param.active_status') != "") {
            $where .= " and v.status = " . I('param.active_status');
        }
        //绑定状态搜索
        switch (I('param.is_use')) {
            case '-2':
                $where .= " and v.is_binded_user = '0'";
                $where .= " and v.is_binded_car = '0'";
                break;
            case '1':
                $where .= " and v.is_binded_user = '1'";
                $where .= " and v.is_binded_car = '0'";
                break;
            case '2':
                $where .= " and v.is_binded_user = '0'";
                $where .= " and v.is_binded_car = '1'";
                break;
            case '3':
                $where .= " and v.is_binded_user = '1'";
                $where .= " and v.is_binded_car = '1'";
                break;
            default:

                break;
        }
        //设备公司搜索
        if (I('param.vender_id')) {
            $where .= " and v.supplied_organ_id = '" . I('param.vender_id') . "'";
        }
        //设备类型搜索
        if (I('param.device_type')) {
            $where .= " and type.device_type_id = '" . I('param.device_type') . "'";
        }
        //设备型号搜索
        if (I('param.device_model')) {
            $where .= " and v.device_series_id = '" . I('param.device_model') . "'";
        }
        //设备归属搜索
        if (I('param.organ_id')) {
            $where .= " and v.belonged_organ_id = '" . I('param.organ_id') . "'";
        }
        // 查询满足要求的总记录数
        $count = $this->vehicleDB->vehicleListCnt($where);
        // 实例化分页类
        $page = $this->getPage($count[0]['cnt'], $where);

        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $data = $this->vehicleDB->vehicleList($where, $page['firstRow'], $page['listRows']);

        // var_dump($this->vehicleDB->getLastSql());

        if (I('param.fileOut') == 1) {
            $data = $this->vehicleDB->vehicleList($where, 0, $count[0]['cnt']);
            foreach ($data as $val) {
                $organ_id[] = $val['belonged_organ_id'];
            }
            $new_organ = array_unique($organ_id);
            foreach ($new_organ as $val) {
                $name[$val] = $this->getOwner($val);
            }
        }

        foreach ($data as $key => &$val) {
            switch ($val['active_status']) {
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

            switch (true) {
                case ($val['is_car'] == '0' && $val['is_use'] == '1'):
                    $bind_status = "已绑定手机";
                    break;
                case ($val['is_car'] == '1' && $val['is_use'] == '0'):
                    $bind_status = "已绑定车辆";
                    break;
                case ($val['is_car'] == '1' && $val['is_use'] == '1'):
                    $bind_status = "已绑定车辆&手机";
                    break;
                default:
                    $bind_status = "未绑定";
                    break;
            }
            $data[$key]['bind_status'] = $bind_status;
            $val = array_map(array($this, 'filterNull'), $val);
        }
        if (I('param.fileOut') == 1) {
            foreach ($data as $key => &$val) {
                //归属
                $data[$key]['first_name'] = $name[$val['belonged_organ_id']]['organ_name'];
                $data[$key]['company_name'] = $name[$val['belonged_organ_id']]['company_name'];
                $data[$key]['son_name'] = $name[$val['belonged_organ_id']]['son_name'];
            }
            A('Excel')->vehicleOut($data);
            exit;
        }
        echo json_encode(array('data' => $data, 'page' => $page['show']));
        exit;
    }

    /**
     *查看信息
     */
    public function editVehicle()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editVehicle', 0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('editVehicle');
    }

    /**
     *查看信息数据
     */
    public function getVehicle()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editVehicle', 0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        $where = 'v.device_id = ' . I('get.id');
        $info = $this->vehicleDB->vehicleList($where, 0, 1);
        $info = $info[0];
        switch ($info['active_status']) {
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
        $info['active_status'] = $active_status;

        switch (true) {
            case ($info['is_car'] == '0' && $info['is_use'] == '1'):
                $bind_status = "已绑定手机";
                break;
            case ($info['is_car'] == '1' && $info['is_use'] == '0'):
                $bind_status = "已绑定车辆";
                break;
            case ($info['is_car'] == '1' && $info['is_use'] == '1'):
                $bind_status = "已绑定车辆&手机";
                break;
            default:
                $bind_status = "未绑定";
                break;
        }
        $info['bind_status'] = $bind_status;
        //归属
        $name = $this->getOwner($info['belonged_organ_id']);
        //归属企业
        $info['first_name'] = $name['organ_name'];
        //归属公司
        $info['company_name'] = $name['company_name'];
        //归属机构
        $info['son_name'] = $name['son_name'];
        //车辆信息
        $car = $this->carDB->field('car_type_id')->where(array('car_id' => $info['car_id']))->find();

        $brand = M('biz.car_type_view')->field('car_brand,car_series')->where(array('car_brand_id' => $car['car_brand_id']))->find();
        $info['car_band'] = $brand['car_brand'];
        $info['car_serious'] = $brand['car_series'];

        $info = array_map(array($this, 'filterNull'), $info);
        echo json_encode($info);
        exit;
    }

    /**
     *机构完整名称处理
     */
    public function getOrganName($data)
    {
        $name = '';
        if ($data) {
            foreach ($data as $key => $val) {
                $son = $val['organ_name'];
                if ($val) {
                    $parent = $this->getOrganName($val);
                    $name = ($parent) ? $parent . "_" . $son : $son;
                }
            }
        }
        return htmlspecialchars($name);
    }

    /**
     *获取设备完整归属
     */
    public function getOwner($organ_id)
    {
        //归属
        $organ = $this->getList($organ_id, 'organ.parent_organ_id as organ_id,organ.organ_name', 'organ.organ_id');
        $name = array_values(array_filter(explode('_', $this->getOrganName($organ))));
        //归属企业
        $info['organ_name'] = $name[0];
        //归属公司
        $info['company_name'] = $name[1];
        //重组机构
        for ($i = 2; $i < count($name); $i++) {
            $son_name[] = $name[$i];
        }
        $info['son_name'] = implode('_', $son_name);
        return $info;
    }

    /**
     *删除设备
     */
    public function delVehicle()
    {
        //根据设备id获取车辆信息
        $info = $this->vehicleDB->getInfo(array('device_id' => I('get.id')), 'status,is_binded_user,is_binded_car');
        //车辆已激活或者已绑定手机的不可删除
        if ($info['is_binded_car'] == '1' || $info['is_binded_user'] == '1') {
            echo json_encode(array('msg' => '该设备不可删除', 'status' => '0'));
            exit;
        }
        $del = $this->vehicleDB->where(array('device_id' => I('get.id')))->delete();
        $msg = ($del > 0) ? '删除成功' : '删除失败,请联系管理员';
        $status = ($del > 0) ? 1 : 0;
        echo json_encode(array('msg' => $msg, 'status' => $status));
        exit;
    }

    /**
     *获取硬件详细信息
     */
    public function getDevice()
    {
        switch (I('get.act')) {
            case 'type':
                $info = $this->deviceDB->getData(array('supplied_organ_id' => I('get.id')),
                    'device_type_id,device_type as device_type_name');

                break;
            case 'model':
                $info = $this->deviceModelDB
                    ->field('device_series_id as device_model_id,device_series as device_model_name')
                    ->where(array('device_type_id' => I('get.id')))
                    ->select();
                break;
            default:
                break;
        }
        echo json_encode($info);
        exit;
    }

    /**
     *解绑手机
     */
    public function unbundingTel()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('unbundingTel', 1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        //根据设备号获取用户id
        $userId = $this->userDB->field('user_id')->where(array('device_id' => I('get.id')))->find();
        if (empty($userId)) {
            echo json_encode(array('msg' => '设备未绑定手机!', 'status' => 0));
            exit;
        }
        //事务开启
        M()->startTrans();
        //根据设备号修改绑定状态
        $device = $this->vehicleDB->where(array('device_id' => I('get.id')))->data(array('is_binded_user' => 0, 'is_binded_car' => 0, 'status' => 0, 'car_id' => 0, 'activated_time' => '0000-00-00 00:00:00', 'expire_time' => '0000-00-00 00:00:00'))->save();
        //根据用户id修改绑定状态
        $user = $this->userDB->where(array('user_id' => $userId['user_id']))->data(array('is_binded_device' => 1, 'device_id' => 0))->save();
        $this->redisDB->getRedis()->select(0);
        //redis事务
        $this->redisDB->getRedis()->multi();
        $this->redisDB->getRedis()->hset($this->sessionArr['organ_channel_id'] . ':' . $userId['user_id'], 'obdId', null);
        if ($device > 0 && $user > 0) {
            //事务提交
            M()->commit();
            $this->redisDB->getRedis()->exec();
            $msg = '解绑成功!';
            $status = 1;
        } else {
            //事务回滚
            M()->rollback();
            $msg = '解绑失败!';
            $status = 0;
        }

        echo json_encode(array('msg' => $msg, 'status' => $status));
        exit;
    }

    /**
     *信息数组赋值null为空
     */
    public function filterNull($v)
    {
        if (is_null($v)) {
            return '';
        }
        return $v;
    }
}
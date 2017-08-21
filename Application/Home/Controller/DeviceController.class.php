<?php
namespace Home\Controller;

use Home\Model\DeviceModel;
use Home\Model\DeviceModelModel;

class DeviceController extends BaseController
{
    private $deviceDB;
    private $deviceModelDB;

    function __construct()
    {
        parent::__construct();
        $this->deviceDB = new DeviceModel('biz', 'tp_device_type');
        $this->deviceModelDB = new DeviceModelModel('biz', 'tp_device_series');
    }
    /**
    *添加企业硬件页
    */
    public function index()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addDevice',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        //硬件厂家
        $where['coop_type_id'] = array('like', '%2%');
        $where['is_available'] = 1;
        $where['organ_type_id'] = C("ORGAN_TYPE_ID");
        $vender = $this->organDB->where($where)->field('organ_id as vender_id,organ_name as vender_name')->select();        $this->assign('vender',$vender);
        $this->display('addDevice');
    }
    /**
    *添加企业硬件数据处理
    */
    public function addDeviceAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addDevice',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $array = array(
            'device_type' => I('post.device_type_name'),
            'supplied_organ_id' => I('post.vender_id')
            );
        $type = $this->deviceDB->getInfo($array,'device_type_id');

        if(!empty($type))
        {
            echo json_encode(array('msg' => '该厂家下的设备类型已存在，请重新输入','status' => 0));
            exit;
        }
        //设备类型入库
        $insertId = $this->deviceDB->addDevice($array);
        //去空并去重
        $model = array_unique(array_filter(I('post.device_model_name')));
        //设备型号入库
        if(!empty($model))
        {
            foreach ($model as $key => $val) 
            {
                $modelData = array(
                    'device_series' => $val,
                    'device_type_id' => $insertId
                    );
                $this->deviceModelDB->add($modelData);
            }
        }
        $msg = ($insertId > 0) ? '添加成功' : '添加失败';
        $status = ($insertId > 0) ? 1 : 0;

        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *企业硬件列表页
    */
    public function deviceList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('deviceList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('deviceList');
    }
    /**
    *企业硬件列表数据处理
    */
    public function deviceListAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('deviceList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $draw = I('post.draw');//计数器
        $start = I('post.start');//分页偏移值
        $end = I('post.length');//分页每页显示数
        $search = I('post.search');//查询框提交参数 array 取消使用
        $sort = I('post.order');//排序字段 array
        $columns = I('post.columns');//数据列 array

        $data = $this->deviceDB->field('device_type_id,device_type as device_type_name,supplied_organ_id')->select();
        // dump($data);
        foreach ($data as $key => $val)
        {
            $organData = $this->organDB
                ->where(['organ_id'=>$val['supplied_organ_id']])
                ->field('organ_name')
                ->find();
            // dump($this->venderDB->getLastSql());
            $data[$key]['vender_name'] = $organData['organ_name'];
        }
        $dataCnt = count($data);

        $result = array(
            "draw"=>$draw,
            "recordsTotal"=>$dataCnt,
            "recordsFiltered"=>$dataCnt,
            "data"=>$data
        );

        echo json_encode($result);
        exit();
    }
    /**
    *修改企业硬件页
    */
    public function editDevice()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editDevice',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        //硬件厂家
        $where['coop_type_id'] = array('like','%2%');
        $where['is_available'] = 1;
        $where['organ_type_id'] = 4;
        $vender = $this->organDB->where($where)->field('organ_id as vender_id,organ_name as vender_name')->select();        $info = $this->deviceDB->getInfo(array('device_type_id' => I('get.id')),'device_type_id,device_type as device_type_name,supplied_organ_id as vender_id');

        $info['device_model_name'] = $this->deviceModelDB->field('device_series as device_model_name')->where(array('device_type_id' => I('get.id')))->select();

        $this->assign('vender',$vender);
        $this->assign('info',$info);
        $this->display('editDevice');
    }
    /**
    *修改企业硬件数据处理
    */
    public function editDeviceAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editDevice',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

//        $organInfo = $this->organDB
//            ->where("organ_type_id=".I('post.vender_id'))
//            ->field("organ_id")->find();
        // var_dump($this->organDB->getLastSql());
        // die;
        $array = array(
            'device_type' => I('post.device_type_name'),
            'supplied_organ_id' => I("post.vender_id")
        );
        $type = $this->deviceDB->getInfo($array,'device_type_id');

        if(!empty($type) && $type['device_type_id'] != I('post.device_type_id'))
        {
            $msg = '该厂家下的设备类型已存在，请重新输入';
            $status = ($insertId > 0) ? 1 : 0;

            echo json_encode(array('msg' => $msg,'status' => $status));
            exit;
        }

        //修改设备类型
        $insertId = $this->deviceDB->editDevice(array('device_type_id' => I('post.device_type_id')),$array);
        //删除设备型号
        $this->deviceModelDB->where(array('device_type_id' => I('post.device_type_id')))->delete();
        //去空并去重
        $model = array_unique(array_filter(I('post.device_model_name')));
        //设备型号入库
        if(!empty($model))
        {
            foreach ($model as $key => $val) 
            {
                $modelData = array(
                    'device_series' => $val,
                    'device_type_id' => I('post.device_type_id')
                    );
                $modelId = $this->deviceModelDB->add($modelData);
            }
        }

        $msg = '修改成功';
        $status = 1;
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *删除企业硬件
    */
    public function delDevice()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('delDevice',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        //事务开启
        M()->startTrans();
        //删除设备类型
        $id = $this->deviceDB->delDevice(array('device_type_id' => I('get.id')));
        //删除设备型号
        $delM = $this->deviceModelDB->where(array('device_type_id' => I('get.id')))->delete();
        if($id > 0 && $delM >= 0)
        {
            //事务提交
            M()->commit();
            $msg = '删除成功';
            $status = 1;
        }
        else
        {
            //事务回滚
            M()->rollback();
            $msg = '删除失败';
            $status = 0;
        }

        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *获取硬件详细信息
    */
    public function getDevice()
    {
        switch (I('get.act')) 
        {
            case 'type':
                $info = $this->deviceDB->getData(array('supplied_organ_id' => I('get.id')),'device_type_id,device_type as device_type_name');
                break;
            case 'model':
                $info = $this->deviceModelDB->field('device_series_id as device_model_id,device_series as device_model_name')->where(array('device_type_id' => I('get.id')))->select();
                break;
            default:
                break;
        }
        echo json_encode($info);
        exit;

    }
}
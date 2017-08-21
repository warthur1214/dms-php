<?php
namespace Home\Controller;

use Home\Model\DeviceModel;
use Home\Model\VenderModel;

class VenderController extends BaseController
{
    private $venderDB;
    private $deviceDB;

    function __construct()
    {
        parent::__construct();
        $this->venderDB = new VenderModel('auth', 'tp_organ_type');
        $this->deviceDB = new DeviceModel('biz', 'tp_device_type');
    }
    /**
    *添加硬件厂家页
    */
    public function addVender()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addVender',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('addVender');
    }
    /**
    *添加硬件厂家数据处理
    */
    public function addVenderAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addVender',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $array = array(
            'organ_type_name' => I('post.vender_name')
        );
        $info = $this->venderDB->field('organ_type_id')->where($array)->find();

        if(!empty($info))
        {
            echo json_encode(array('msg' => '该厂家已存在，请重新输入','status' => 0));
            exit;
        }
        //入库
        $insertId = $this->venderDB->data($array)->add();
        $msg = ($insertId > 0) ? '添加成功' : '添加失败';
        $status = ($insertId > 0) ? 1 : 0;

        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *硬件厂家列表页
    */
    public function venderList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('venderList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('venderList');
    }
    /**
    *硬件厂家列表数据处理
    */
    public function venderListAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('venderList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $draw = I('post.draw');//计数器
        $start = I('post.start');//分页偏移值
        $end = I('post.length');//分页每页显示数
        $search = I('post.search');//查询框提交参数 array 取消使用
        $sort = I('post.order');//排序字段 array
        $columns = I('post.columns');//数据列 array

        $fields = ['organ_type_id'=>'vender_id', 'organ_type_name'=>'vender_name', 'create_time', 'update_time'];
        $data = $this->venderDB->field($fields)->order('organ_type_id desc')->select();

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
    *修改硬件厂家页
    */
    public function editVender()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editVender',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        //设备信息
        $fields = ['organ_type_id'=>'vender_id', 'organ_type_name'=>'vender_name', 'create_time', 'update_time'];
        $info = $this->venderDB->where(array('organ_type_id' => I('get.id')))->field($fields)->find();

        $this->assign('info',$info);
        $this->display('editVender');
    }
    /**
    *修改硬件厂家数据处理
    */
    public function editVenderAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editVender',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $array = array(
            'organ_type_name' => I('post.vender_name')
        );
        $info = $this->venderDB->field('organ_type_id')->where($array)->find();

        if(!empty($info) && $info['organ_type_id'] != I('post.vender_id'))
        {
            echo json_encode(array('msg' => '该厂家已存在，请重新输入','status' => 0));
            exit;
        }
        //修改
        $saveId = $this->venderDB->data($array)->where(array('organ_type_id' => I('post.vender_id')))->save();

        $msg = ($saveId > 0) ? '修改成功' : '修改失败';
        $status = ($saveId > 0) ? 1 : 0;
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *删除硬件厂家
    */
    public function delVender()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('delVender',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $info = $this->deviceDB->field('device_type_id')->where(array('supplied_organ_id' => I('get.id')))->find();

        if(!empty($info))
        {
            echo json_encode(array('msg' => '该厂家存在硬件信息,请检查','status' => 0));
            exit;
        }
        //删除
        $id = $this->venderDB->where(array('organ_type_id' => I('get.id')))->delete();

        $msg = ($id > 0) ? '删除成功' : '删除失败';
        $status = ($id > 0) ? 1 : 0;

        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
}
<?php
namespace Home\Controller;

use Home\Model\CarGroupModel;
use Home\Model\CarModel;

class CarGroupController extends BaseController
{
    private $carDB;
    private $groupDB;

    function __construct()
    {
        parent::__construct();
        $this->carDB = new CarModel($this->bizDB, 'tp_');
        $this->groupDB = new CarGroupModel($this->bizDB, 'tp_');
    }
    /**
    *添加车辆分组页
    */
    public function index()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('addGroup',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $this->display('addGroup');
    }
    /**
    *添加车辆分组数据处理
    */
    public function addGroupAjax()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('addGroup',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $info = $this->groupDB->getInfo(array('group_name' => I('post.group_name'),'belonged_organ_id' => I('post.organ_id')),'group_id');

        if (strlen(I('post.group_name')) > 50) {
            echo json_encode(['status'=>0, 'msg'=>'车辆分组名称超过50个字符！']);
            die;
        }

        if (strlen(I('post.group_depict')) > 255) {
            echo json_encode(['status'=>0, 'msg'=>'车辆分组名称超过255个字符！']);
            die;
        }

        if(!empty($info))
        {
            $msg = '车辆分组名已存在，请重新输入';
            $status = 0;
        }
        else
        {
            $array = array(
                'group_name' => I('post.group_name'),
                'desc' => I('post.group_depict'),
                'belonged_organ_id' => I('post.organ_id')
                );
            $insertId = $this->groupDB->addGroup($array);

            $msg = ($insertId > 0) ? '添加成功' : '添加失败';
            $status = ($insertId > 0) ? 1 : 0;
        }
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *车辆分组列表页
    */
    public function groupList()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('groupList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $this->display('groupList');
    }
    /**
    *车辆分组列表数据处理
    */
    public function groupListAjax()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('groupList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $draw = I('post.draw');//计数器
        $start = I('post.start');//分页偏移值
        $end = I('post.length');//分页每页显示数
        $search = I('post.search');//查询框提交参数 array 取消使用
        $sort = I('post.order');//排序字段 array
        $columns = I('post.columns');//数据列 array

        if($this->ownOrgan())
        {
            $where['belonged_organ_id'] = array('in',$this->ownOrgan());
        }
        else
        {
            $where = array();
        }
        $data = $this->groupDB->getData($where,'group_id,group_name,`desc` as group_depict,belonged_organ_id');
        foreach ($data as $key => $val) 
        {
            $organ = $this->organDB->field('organ_name')->where(array('organ_id' => $val['belonged_organ_id']))->find();
            $data[$key]['organ_name'] = $organ['organ_name'];
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
    *修改车辆分组页
    */
    public function editGroup()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('editGroup',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $info = $this->groupDB->getInfo(array('group_id' => I('get.id')),'group_id,group_name,`desc` as group_depict,belonged_organ_id as organ_id');
        $organ = $this->organDB->field('organ_name')->where(array('organ_id' => $info['organ_id']))->find();
        $info['organ_name'] = $organ['organ_name'];
        
        $this->assign('info',$info);
        $this->display('editGroup');
    }
    /**
    *修改车辆分组数据处理
    */
    public function editGroupAjax()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('editGroup',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/
        
        $info = $this->groupDB->getInfo(array('group_name' => I('post.group_name'),'belonged_organ_id' => I('post.organ_id')),'group_id');
        
        if(!empty($info) && $info['group_id'] != I('post.group_id'))
        {
            $msg = '车辆分组名已存在，请重新输入';
            $status = 0;
        }
        else
        {
            $array = array(
                'group_name' => I('post.group_name'),
                'desc' => I('post.group_depict'),
                'belonged_organ_id' => I('post.organ_id')
                );
            $id = $this->groupDB->editGroup(array('group_id' => I('post.group_id')),$array);
            $msg = ($id > 0) ? '修改成功' : '修改失败或未修改';
            $status = ($id > 0) ? 1 : 0;
        }
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *删除车辆分组
    */
    public function delGroup()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('delGroup',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $car = $this->carDB->getData(array('group_id' => I('get.id')),'car_id');
        if(!empty($car))
        {

            $msg = '删除失败，有车辆属于该车辆分组，请检查';
            $status = 0;
        }
        else
        {
            $id = $this->groupDB->delGroup(I('get.id'));
            $msg = ($id > 0) ? '删除成功' : '删除失败';
            $status = ($id > 0) ? 1 : 0;
        }
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;

    } 
}
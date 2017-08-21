<?php
namespace Home\Controller;

use Home\Model\DeviceModel;

class CompanyController extends BaseController
{
    private $deviceDB;

    function __construct()
    {
        parent::__construct();
        $this->deviceDB = new DeviceModel('biz', 'tp_device_type');
    }
    /**
    *添加企业页
    */
    public function index()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addCompany',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('addCompany');
    }
    /**
    *添加企业数据处理
    */
    public function addCompanyAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addCompany',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $organ_name = $this->organDB->getInfo(array('organ_name' => I('post.organ_name')),'organ_id');
        $small_name = $this->organDB->getInfo(array('small_name' => I('post.small_name')),'organ_id');

        if(!empty($organ_name))
        {
            $msg = '企业名称已存在，请重新输入';
            $status = 0;
        }
        else if(!empty($small_name))
        {
            $msg = '企业简称已存在，请重新输入';
            $status = 0;
        }
        else
        {
            $toger_type = implode(',',I('post.toger_type'));
            $array = array(
                'organ_name' => I('post.organ_name'),
                'small_name' => I('post.small_name'),
                'organ_type' => I('post.organ_type'),
                'toger_type' => $toger_type,
                'sys_id' => I('post.sys_id'),
                'sys_key' => I('post.sys_key'),
                'organ_address' => I('post.organ_address'),
                'link_man' => I('post.link_man'),
                'link_number' => I('post.link_number'),
                'link_email' => I('post.link_email'),
                'remark' => I('post.remark')
                );

            $insertId = $this->organDB->addCompany($array);

            $msg = ($insertId > 0) ? '添加成功' : '添加失败';
            $status = ($insertId > 0) ? 1 : 0;
        }
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *企业列表页
    */
    public function organList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('organList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('organList');
    }
    /**
    *企业列表数据处理
    */
    public function organListAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('organList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $draw = I('post.draw');//计数器
        $start = I('post.start');//分页偏移值
        $end = I('post.length');//分页每页显示数
        $search = I('post.search');//查询框提交参数 array 取消使用
        $sort = I('post.order');//排序字段 array
        $columns = I('post.columns');//数据列 array

        //判断企业id
        switch ($this->organId) 
        {
            case false://管理员

                break;
            default:
                // 根据当前账户id获取其管理企业id 
                $where['organ_id'] = array('eq',$this->organId);
                break;
        }
        $data = $this->organDB->getData($where);
        foreach ($data as $key => $val) 
        {
            $data[$key]['toger_type'] = explode(',',$val['toger_type']);
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
    *修改企业页
    */
    public function editCompany()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editCompany',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $info = $this->organDB->getInfo(array('organ_id' => I('get.id')));

        $this->assign('info',$info);
        $this->display('editCompany');
    }
    /**
    *修改企业数据处理
    */
    public function editCompanyAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editCompany',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $organ_name = $this->organDB->getInfo(array('organ_name' => I('post.organ_name')),'organ_id');
        $small_name = $this->organDB->getInfo(array('small_name' => I('post.small_name')),'organ_id');

        if(!empty($organ_name) && $organ_name['organ_id'] != I('post.organ_id'))
        {
            $msg = '企业名称已存在，请重新输入';
            $status = 0;
        }
        else if(!empty($small_name) && $small_name['organ_id'] != I('post.organ_id'))
        {
            $msg = '企业简称已存在，请重新输入';
            $status = 0;
        }
        else
        {
            $toger_type = implode(',',I('post.toger_type'));
            $array = array(
                'organ_name' => I('post.organ_name'),
                'small_name' => I('post.small_name'),
                'organ_type' => I('post.organ_type'),
                'toger_type' => $toger_type,
                'sys_id' => I('post.sys_id'),
                'sys_key' => I('post.sys_key'),
                'organ_address' => I('post.organ_address'),
                'link_man' => I('post.link_man'),
                'link_number' => I('post.link_number'),
                'link_email' => I('post.link_email'),
                'remark' => I('post.remark')
                );
            $id = $this->organDB->editCompany(array('organ_id' => I('post.organ_id')),$array);
            $msg = ($id > 0) ? '修改成功' : '修改失败或未修改';
            $status = ($id > 0) ? 1 : 0;
        }
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *删除企业
    */
    public function delCompany()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('delCompany',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        //检查企业是否有硬件设备信息
        $device = $this->deviceDB->getData(array('organ_id' => I('get.id')),'device_id');
        if(!empty($device))
        {
            $msg = '删除失败，该企业有硬件设备信息，请检查';
            $status = 0;
            echo json_encode(array('msg' => $msg,'status' => $status));
            exit;
        }
        $id = $this->organDB->delCompany(I('get.id'));

        $msg = ($id > 0) ? '删除成功' : '删除失败';
        $status = ($id > 0) ? 1 : 0;
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;

    }
}
<?php
namespace Home\Controller;

use Home\Model\DriverModel;

class DriverController extends BaseController
{
    private $driverDB;

    function __construct()
    {
        parent::__construct();
        $this->driverDB = new DriverModel($this->bizDB, 'tp_');
    }
    /**
    *添加司机页
    */
    public function index()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('addDriver',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $this->display('addDriver');
    }
    /**
    *添加司机数据处理
    */
    public function addDriverAjax()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('addDriver',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        if (strlen(I("post.drive_type")) != 2) {
            echo json_encode(['status'=>0, 'msg'=>'准驾车型信息有误，请重新输入']);
            exit;
        }

        $info = $this->driverDB->getInfo(array('phone' => I('post.driver_phone')),'driver_id');
        if(!empty($info))
        {
            $msg = '司机手机号已存在，请重新输入';
            $status = 0;
        }
        else
        {
            $cardTime = explode(' - ',I('post.card_use_time'));
            $array = array(
                'name' => I('post.driver_name'),
                'gender' => I('post.driver_sex'),
                'phone' => I('post.driver_phone'),
                'id_card' => I('post.driver_id_card'),
                'age' => I('post.driver_age'),
                'native_place' => I('post.driver_address'),
                'regist_permanent_residence' => I('post.driver_location'),
                'emerge_contact_person' => I('post.need_link_man'),
                'emerge_contact_phone' => I('post.need_link_no'),
                'employ_date' => I('post.work_time'),
                'address' => I('post.now_address'),
                'license_no' => I('post.drive_card'),
                'driving_exper' => I('post.drive_year'),
                'license_start_time' => $cardTime[0],
                'license_end_time' => $cardTime[1],
                'license_class' => I('post.drive_type'),
                'license_issue_place' => I('post.drive_card_location'),
                );
            $insertId = $this->driverDB->addDriver($array);
            $msg = ($insertId > 0) ? '添加成功' : '添加失败';
            $status = ($insertId > 0) ? 1 : 0;
        }
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *修改司机页
    */
    public function editDriver()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('editDriver',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $info = $this->driverDB->getInfo(array('driver_id' => I('get.id')));
        
        $this->assign('info',$info);
        $this->display('editDriver');
    }
    /**
    *修改司机数据处理
    */
    public function editDriverAjax()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('editDriver',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $info = $this->driverDB->getInfo(array('phone' => I('post.driver_phone')),'driver_id');

        if (strlen(I("post.drive_type")) != 2) {
            echo json_encode(['status'=>0, 'msg'=>'准驾车型信息有误，请重新输入']);
            exit;
        }

        if(!empty($info) && $info['driver_id'] != I('post.driver_id'))
        {
            $msg = '司机手机号已存在，请重新输入';
            $status = 0;
        }
        else
        {
            $cardTime = explode(' - ',I('post.card_use_time'));
            $array = array(
                'name' => I('post.driver_name'),
                'gender' => I('post.driver_sex'),
                'phone' => I('post.driver_phone'),
                'id_card' => I('post.driver_id_card'),
                'age' => I('post.driver_age'),
                'native_place' => I('post.driver_address'),
                'regist_permanent_residence' => I('post.driver_location'),
                'emerge_contact_person' => I('post.need_link_man'),
                'emerge_contact_phone' => I('post.need_link_no'),
                'employ_date' => I('post.work_time'),
                'address' => I('post.now_address'),
                'license_no' => I('post.drive_card'),
                'driving_exper' => I('post.drive_year'),
                'license_start_time' => $cardTime[0],
                'license_end_time' => $cardTime[1],
                'license_class' => I('post.drive_type'),
                'license_issue_place' => I('post.drive_card_location'),
                );
            $editId = $this->driverDB->editDriver(array('driver_id' => I('post.driver_id')),$array);
            $msg = ($editId > 0) ? '修改成功' : '修改失败或未修改';
            $status = ($editId > 0) ? 1 : 0;
        }
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *删除司机
    */
    public function delDriver()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('delDriver',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $id = $this->driverDB->delDriver(I('get.id'));
        $msg = ($id > 0) ? '删除成功' : '删除失败';
        $status = ($id > 0) ? 1 : 0;

        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;

    } 
    /**
    *司机列表页
    */
    public function driverList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('driverList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $phone = $this->driverDB->getData($where,'phone as id,phone as text');

        $this->assign('phone',json_encode($phone));
        $this->display('driverList');
    }
    /**
    *获取司机
    */
    public function getDriver()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('driverList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        //查询司机姓名
        if(I('param.driver_name'))
        {
            $where['name'] = array('like',"%".I('param.driver_name')."%");
        }
        //查询手机号
        if(I('param.driver_phone'))
        {
           
            $where['phone'] = array('eq',I('param.driver_phone'));
        }
        // 查询满足要求的总记录数
        $count = $this->driverDB->where($where)->count();
        // 实例化分页类
        $page = $this->getPage($count,$where);

        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $data = $this->driverDB->where($where)->order('driver_id desc')->limit($page['firstRow'].','.$page['listRows'])->select();
        foreach ($data as $key => &$val) 
        {
            $val = array_map(array($this,'filterNull'),$val);
        }
        echo json_encode(array('data' => $data,'page' => $page['show']));
        exit;
    }
}
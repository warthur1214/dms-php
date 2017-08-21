<?php
namespace Home\Controller;

class RoleController extends BaseController
{
    private $moduleDB;
    private $roleDB;
    private $accountDB;

    function __construct()
    {
        parent::__construct();
        $this->moduleDB = D('Module');
        $this->roleDB = D('Role');
        $this->accountDB = D('Account');
    }
    /**
    *添加角色页
    */
    public function index()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('addRole',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

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
        $organ = $this->organDB->getData($where,'organ_id,organ_name');

        $this->assign('organ',$organ);
        $this->display('addRole');
    }
    /**
    *添加角色数据处理
    */
    public function addRoleAjax()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('addRole',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $info = $this->roleDB->getInfo(array('role_name' => I('post.role_name')),'role_id');
        //根据当前账户获取企业id
        $organ_id = $this->accountDB->getInfo(array('account_id' => $this->sessionArr['account_id']),'organ_id');
        //添加角色不选择企业则自动获取当前账户的企业id
        $organ_id = (I('post.organ_id'))? I('post.organ_id') : $organ_id['organ_id'];
        if(!empty($info))
        {
            $msg = '角色名已存在，请重新输入';
            $status = 0;
        }
        else
        {
            $array = array(
                'role_name' => I('post.role_name'),
                'organ_id' => $organ_id,
                'role_explain' => I('post.role_explain')
                );
            $insertId = $this->roleDB->addRole($array);
            $roleM = array(
                'm_id' => 10,
                'role_id' => $insertId
            );
            $this->roleMDB->addRoleM($roleM);
            $msg = ($insertId > 0) ? '添加成功' : '添加失败';
            $status = ($insertId > 0) ? 1 : 0;
        }
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *角色列表页
    */
    public function roleList()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('roleList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $this->display('roleList');
    }
    /**
    *角色列表数据处理
    */
    public function roleListAjax()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('roleList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

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
        //不可看自己的角色
        $where['role_id'] = array('neq',$this->roleId);
        $data = $this->roleDB->getData($where);
        foreach ($data as $key => $val) 
        {
            $organ = $this->organDB->getInfo(array('organ_id' => $val['organ_id']),'organ_name');
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
    *修改角色页
    */
    public function editRole()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('editRole',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $info = $this->roleDB->getInfo(array('role_id' => I('get.id')));

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
        $organ = $this->organDB->getData($where,'organ_id,organ_name');

        $this->assign('info',$info);
        $this->assign('organ',$organ);
        $this->display('editRole');
    }
    /**
    *修改角色数据处理
    */
    public function editRoleAjax()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('editRole',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/
        
        $info = $this->roleDB->getInfo(array('role_name' => I('post.role_name')),'role_id');

        //根据当前账户获取企业id
        $organ_id = $this->accountDB->getInfo(array('account_id' => $this->sessionArr['account_id']),'organ_id');
        //添加角色不选择企业则自动获取当前账户的企业id
        $organ_id = (I('post.organ_id'))? I('post.organ_id') : $organ_id['organ_id'];

        if(!empty($info) && $info['role_id'] != I('post.role_id'))
        {
            $msg = '角色名已存在，请重新输入';
            $status = 0;
        }
        else
        {
            $array = array(
                'role_name' => I('post.role_name'),
                'organ_id' => $organ_id,
                'role_explain' => I('post.role_explain')
                );
            $id = $this->roleDB->editRole(array('role_id' => I('post.role_id')),$array);
            $this->accountDB->editAccount(array('role_id' => I('post.role_id')),array('organ_id' => $organ_id));
            $msg = ($id > 0) ? '修改成功' : '修改失败或未修改';
            $status = ($id > 0) ? 1 : 0;
        }
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *删除角色
    */
    public function delRole()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('delRole',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $account = $this->accountDB->getData(array('role_id' => I('get.id')));
        if(!empty($account))
        {

            $msg = '删除失败，有账户属于该角色，请检查';
            $status = 0;
        }
        else
        {
            $id = $this->roleDB->delRole(I('get.id'));
            $this->roleMDB->delRoleM(array('role_id' => I('get.id')));
            $msg = ($id > 0) ? '删除成功' : '删除失败';
            $status = ($id > 0) ? 1 : 0;
        }
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;

    } 
    /**
    *设置角色权限页
    */
    public function addRoleM()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('addRoleM',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $roleId = I('get.id');
        $this->assign('roleId',$roleId);
        $this->display('addRoleM');
    }
    /**
    *设置角色权限数据处理
    */
    public function addRoleMAjax()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('addRoleM',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $array = array(
            'role_id' => I('post.roleId'),
            'm_id' => I('post.mId')
            );
        $roleM = $this->roleMDB->getRoleM($array);
        if(I('post.act') == 'add')
        {
            if(empty($roleM))
            {
                $this->roleMDB->addRoleM($array);
            }
        }
        else
        {
            if(!empty($roleM))
            {
                $this->roleMDB->delRoleM($array);
            }
        }
        echo json_encode(array('status' => 1));
        exit;
    }
    /**
    *获取模块信息
    */
    public function getM()
    {
        //角色id
        $roleId = I('get.id');
        //根据角色id获取管理模块信息
        $roleM = $this->roleMDB->getRoleM(array('role_id' => $roleId));
        foreach ($roleM as $key => $val) 
        {
            $module[$key] = $val['m_id'];
        }
        //根据模块id获取
        if(!empty($module))
        {
            $mId = implode(',',$module);
            $where['m_id'] = array('in',$mId);
            $where['m_lv'] = array('eq',1);
            $mList = $this->moduleDB->getData($where);
            foreach ($mList as $key => $val) 
            {
                $data['m_id'] = array('in',$mId);
                $data['m_parent'] = array('eq',$val['m_id']);
                $two = $this->moduleDB->getData($data);
                $mList[$key]['two'] = $two;
            }
        }

        //企业 根据账户分配的管理的企业
        switch ($this->accountId) 
        {
            case '1'://超级管理员
                $array = array();
                break;
            default:
                $array['m_id'] = array('not in','2');
                break;
        }
        $array['m_lv'] = array('eq','1');
        //获取等级为1的模块
        $info = $this->moduleDB->getModule($array);
        //获取子模块
        foreach ($info as $key => $val) 
        {
            $data = $this->moduleDB->getModule(array('m_parent' => $val['m_id']));
            $info[$key]['m_two'] = $data;
            $info[$key]['count'] = count($data);
        }
        //计算模块长度
        $count = array_sum(array_column($info,'count')) + count($info);
        echo json_encode(array('module' => $module,'mList' => $mList,'info' => $info,'count' => $count));
        exit;
    }
}
<?php
namespace Home\Controller;

class AccountController extends BaseController
{
    private $roleDB;
    private $accountDB;

    function __construct()
    {
        parent::__construct();
        $this->roleDB = D('Role');
        $this->accountDB = D('Account');
    }
    /**
    *添加账户页
    */
    public function index()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addAccount',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

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
        //角色
        $role = $this->roleDB->getData($where);

        $this->assign('role',$role);
        $this->display('addAccount');
    }
    /**
    *添加账户数据处理
    */
    public function addAccountAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addAccount',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $where = array(
            'account_name' => I('post.account_name')
            );
        $info = $this->accountDB->getInfo($where,'account_id');

        if(!empty($info))
        {
            $msg = '账户名已存在，请重新输入';
            $status = 0;
        }
        else
        {
            $array = array(
                'account_name' => I('post.account_name'),
                'real_name' => I('post.real_name'),
                'pwd' => I('post.pwd'),
                'add_time' => time(),
                'email' => I('post.email'),
                'role_id' => I('post.role_id'),
                'is_use' => 0
                );
            $insertId = $this->accountDB->addAccount($array);

            $msg = ($insertId > 0) ? '添加成功' : '添加失败';
            $status = ($insertId > 0) ? 1 : 0;
        }
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *账户列表页
    */
    public function accountList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('accountList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('accountList');
    }
    /**
    *账户列表数据处理
    */
    public function accountListAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('accountList',1); //模块关键词 //是否ajax 0 1
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
        $where['account_id'] = array('neq',1);

        $data = $this->accountDB->getData($where);
        foreach ($data as $key => $val) 
        {
            $organ = $this->organDB->getInfo(array('organ_id' => $val['organ_id']),'organ_name,link_email');
            $data[$key]['organ_name'] = $organ['organ_name'];
            $data[$key]['email'] = $organ['link_email'] ? $organ['link_email'] : $val['email'];
            $role = $this->roleDB->getInfo(array('role_id' => $val['role_id']),'role_name');
            $data[$key]['role_name'] = $role['role_name'];
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
    *修改账户页
    */
    public function editAccount()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editAccount',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $info = $this->accountDB->getInfo(array('account_id' => I('get.id')));
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
        $role = $this->roleDB->getData($where);

        $this->assign('info',$info);
        $this->assign('role',$role);
        $this->display('editAccount');
    }
    /**
    *修改账户数据处理
    */
    public function editAccountAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editAccount',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $info = $this->accountDB->getInfo(array('account_name' => I('post.account_name')),'account_id');
        
        if(!empty($info) && $info['account_id'] != I('post.account_id'))
        {
            $msg = '账户名已存在，请重新输入';
            $status = 0;
        }
        else
        {
            $array = array(
                'account_name' => I('post.account_name'),
                'real_name' => I('post.real_name'),
                'email' => I('post.email'),
                'role_id' => I('post.role_id')
                );
            $id = $this->accountDB->editAccount(array('account_id' => I('post.account_id')),$array);
            $msg = ($id > 0) ? '修改成功' : '修改失败或未修改';
            $status = ($id > 0) ? 1 : 0;
        }
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *删除账户
    */
    public function delAccount()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('delAccount',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $id = $this->accountDB->delAccount(I('get.id'));

        $msg = ($id > 0) ? '删除成功' : '删除失败';
        $status = ($id > 0) ? 1 : 0;
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;

    }
    /**
    *修改密码页
    */
    public function editPwd()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editPwd',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $info = $this->accountDB->getInfo(array('account_id' => $this->sessionArr['account_id']),'account_id,account_name');
        $this->assign('info',$info);
        $this->display('editPwd');
    }
    /**
    *修改密码数据处理
    */
    public function editPwdAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editPwd',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $where = array('account_id' => I('post.account_id'));
        $array = array('pwd' => I('post.pwd'));
        $id = $this->accountDB->editAccount($where,$array);
        
        $msg = ($id > 0) ? '修改成功' : '修改失败';
        $status = ($id > 0) ? 1 : 0;
        session('account_id',null);
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *获取角色信息
    */
    public function getRole()
    {
        $role = $this->roleDB->getInfo(array('role_id' => I('get.id')),'organ_id,role_name');
        $organ = $this->organDB->getInfo(array('organ_id' => $role['organ_id']),'organ_name,link_email');
        $role['organ_name'] = $organ['organ_name'];
        $role['email'] = $organ['link_email'];
        echo json_encode($role);
        exit;
    }
    /**
    *更改冻结状态
    */
    public function freezeUp()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('freezeUp',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $id = $this->accountDB->editAccount(array('account_id' => I('post.id')),array('is_use' => I('post.is_use')));
        
        $status = ($id > 0) ? 1 : 0;
        echo json_encode(array('status' => $status));
        exit;
    }
}
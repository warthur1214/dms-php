<?php
namespace Home\Controller;

class ModuleController extends BaseController
{
    private $moduleDB;

    function __construct()
    {
        parent::__construct();
        $this->moduleDB = D('Module');
    }
    /**
    *添加模块页
    */
    public function index()
    {
        /*##############验证当前角色是否拥有模块访问权限##############**/
        A('Check')->isUse('addModule',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前角色是否拥有模块访问权限##############**/
        $where['m_lv'] = array('neq',2);
        $parent = $this->moduleDB->getData($where,'m_id,m_name');
        $this->assign('parent',$parent);
        $this->display('addModule');
    }
    /**
    *添加模块数据处理
    */
    public function addModuleAjax()
    {
        /*##############验证当前角色是否拥有模块访问权限##############**/
        A('Check')->isUse('addModule',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前角色是否拥有模块访问权限##############**/

        $m_name = $this->moduleDB->getInfo(array('m_name' => I('post.m_name')),'m_id');
        $m_key = $this->moduleDB->getInfo(array('m_key' => I('post.m_key')),'m_id');

        if(!empty($m_name))
        {
            $msg = '模块名已存在，请重新输入';
            $status = 0;
        }
        else if(!empty($m_key))
        {

            $msg = '关键词已存在，请重新输入';
            $status = 0;
        }
        else
        {
            if(empty(I('post.m_parent')))
            {
                $mLv = array(
                    'm_lv' => 0
                    );
            }else
            {
                $parent = $this->moduleDB->getInfo(array('m_id' => I('post.m_parent')),'m_lv');
                $mLv = array(
                    'm_lv' => $parent['m_lv'] + 1
                    );
            }

            $array = array(
                'm_name' => I('post.m_name'),
                'm_url' => I('post.m_url'),
                'm_key' => I('post.m_key'),
                'm_parent' => I('post.m_parent'),
                'm_order' => I('post.m_order'),
                'is_show' => I('post.is_show')
                );
            $array = array_merge($mLv,$array);

            $insertId = $this->moduleDB->addModule($array);
            $data = array(
                'm_id' => $insertId,
                'role_id' => -1
                );
            $this->roleMDB->addRoleM($data);
            $msg = ($insertId > 0) ? '添加成功' : '添加失败';
            $status = ($insertId > 0) ? 1 : 0;
        }
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *模块列表页
    */
    public function moduleList()
    {
        /*##############验证当前角色是否拥有模块访问权限##############**/
        A('Check')->isUse('moduleList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前角色是否拥有模块访问权限##############**/

        $this->display('moduleList');
    }
    /**
    *模块列表数据处理
    */
    public function moduleListAjax()
    {
        /*##############验证当前角色是否拥有模块访问权限##############**/
        A('Check')->isUse('moduleList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前角色是否拥有模块访问权限##############**/

        $draw = I('post.draw');//计数器
        $start = I('post.start');//分页偏移值
        $end = I('post.length');//分页每页显示数
        $search = I('post.search');//查询框提交参数 array 取消使用
        $sort = I('post.order');//排序字段 array
        $columns = I('post.columns');//数据列 array
        $where = array('m_parent' => 0);
        $data = $this->moduleDB->getData($where);
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
    *修改模块页
    */
    public function editModule()
    {
        /*##############验证当前角色是否拥有模块访问权限##############**/
        A('Check')->isUse('editModule',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前角色是否拥有模块访问权限##############**/

        $info = $this->moduleDB->getInfo(array('m_id' => I('get.id')));
        $where['m_lv'] = array('neq',2);
        $parent = $this->moduleDB->getData($where,'m_id,m_name');

        $this->assign('info',$info);
        $this->assign('parent',$parent);
        $this->display('editModule');
    }
    /**
    *修改模块数据处理
    */
    public function editModuleAjax()
    {
        /*##############验证当前角色是否拥有模块访问权限##############**/
        A('Check')->isUse('editModule',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前角色是否拥有模块访问权限##############**/

        $m_name = $this->moduleDB->getInfo(array('m_name' => I('post.m_name')),'m_id');
        $m_key = $this->moduleDB->getInfo(array('m_key' => I('post.m_key')),'m_id');
        if(!empty($m_name) && $m_name['m_id'] != I('post.m_id'))
        {
            $msg = '模块名已存在，请重新输入';
            $status = 0;
        }
        else if(!empty($m_key) && $m_key['m_id'] != I('post.m_id'))
        {
            $msg = '关键词已存在，请重新输入';
            $status = 0;
        }
        else
        {
            if(empty(I('post.m_parent')))
            {
                $mLv = array(
                    'm_lv' => 0
                    );
            }else
            {
                $parent = $this->moduleDB->getInfo(array('m_id' => I('post.m_parent')),'m_lv');
                $mLv = array(
                    'm_lv' => $parent['m_lv'] + 1
                    );
            }
            $array = array(
                'm_name' => I('post.m_name'),
                'm_url' => I('post.m_url'),
                'm_key' => I('post.m_key'),
                'm_parent' => I('post.m_parent')
                );
            $array = array_merge($mLv,$array);
            $id = $this->moduleDB->editModule(array('m_id' => I('post.m_id')),$array);
            $msg = ($id > 0) ? '修改成功' : '修改失败或未修改';
            $status = ($id > 0) ? 1 : 0;
        }
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *删除模块
    */
    public function delModule()
    {
        /*##############验证当前角色是否拥有模块访问权限##############**/
        A('Check')->isUse('delModule',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前角色是否拥有模块访问权限##############**/

        $where = array(
            'm_parent' => I('get.id')
            );
        $info = $this->moduleDB->getInfo($where,'m_id');
        if(!empty($info))
        {
            $msg = '删除失败，该模块存在子模块';
            $status = 0;
        }
        else
        {
            $id = $this->moduleDB->delModule(I('get.id'));
            $where = array(
                'm_id' => I('get.id')
                );
            $this->roleMDB->delRoleM($where);
            $msg = ($id > 0) ? '删除成功' : '删除失败';
            $status = ($id > 0) ? 1 : 0;
        }
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *修改排序和是否显示数据处理
    */
    public function editOther()
    {
        $where = array(
            'm_id' => I('get.id')
            );
        if(I('get.act') == 'order')
        {
            $array = array(
                'm_order' => I('post.m_order')
                );
        }
        else
        {
            $array = array(
                'is_show' => I('post.is_show')
                );
        }
        $this->moduleDB->editModule($where,$array);

        $status = 1;
        echo json_encode(array('status' => $status));
        exit;
    }
    /**
    *获取子模块
    */
    public function getSonM()
    {
        $son = $this->moduleDB->getData(array('m_parent' => I('get.id')));
        foreach ($son as $key => $val) 
        {
            $gson = $this->moduleDB->getData(array('m_parent' => $val['m_id']));
            foreach ($gson as $keys => $vals) 
            {
                $son[] = $vals;
            }
        }
        
        echo json_encode(array('data' => $son));
        exit;
    }
}
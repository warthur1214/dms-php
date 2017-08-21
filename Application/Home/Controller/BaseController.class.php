<?php

namespace Home\Controller;

use Home\Common\RedisModel;
use Think\Controller;

class BaseController extends Controller
{
    public $organDB;//企业model
    public $login_role;  //当前登录帐号所属角色
    public $organ_id;  //当前登录帐号所属企业
    public $organ_channel_id;  //选择要展示的信息所属企业id
    public $bizDB;
    public $sessionArr;

    function __construct()
    {
        parent::__construct();

        $this->sessionArr = (new RedisModel())->getSessionArr();
        $this->checkLogin();
        $this->bizDB = "biz_" . $this->sessionArr['organ_channel_id'];

        $this->organDB = D('Organ');

        //根据当前登录帐号获取帐号角色
        $loginRole = D('AccountRoleRel')->field('role_id')->where(array('account_id' => $this->sessionArr['account_id']))->select();
        $login_role_id = [];
        foreach ($loginRole as $key => $val) {
            $login_role_id[] = $val['role_id'];
        }
        $this->login_role = implode(',', $login_role_id);
    }

    protected function getNowDB()
    {
        //年份
        $year = sprintf("%02s", intval((date('Y') - 2016) / 5));
        //当前年份数据库
        return 'ubi_' . $this->sessionArr['organ_channel_id'] . '_0_' . $year . '_0000';
    }

    /**
     * 验证登陆状态
     */
    private function checkLogin()
    {
        //验证是否已经登陆
        if (!$this->sessionArr['account_id']) {
            echo "<script>window.top.location.href='/';</script>";
            exit;
        }
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

    /**
     *企业信息无限极递归函数
     */
    public function getList($pid = '0', $field, $one = 'organ.parent_organ_id', $otherWhere = '')
    {
        $where = "1=1 and {$one} = '{$pid}' and organ.is_available = '1'" . $otherWhere;
        if ($this->login_role_organ()) {
            $organ_id = $this->belong_organ() . "," . $this->login_role_organ();
        } else {
            $organ_id = $this->belong_organ();
        }
        $where .= " and organ.organ_id in (" . $organ_id . ")";
        $list = $this->organDB->organList($where, $field);

        if ($list) {
            foreach ($list as $key => $val) {
                if ($val['organ_id']) {
                    $val['son'] = $this->getList($val['organ_id'], $field, $one, $otherWhere);
                }
                $array[] = $val;
            }
        }
        return $array;
    }

    /**
     *根据帐号获取所属机构信息
     */
    public function belong_organ()
    {
        $data = D('Account')->field('belonged_organ_id')->where(array('account_id' => $this->sessionArr['account_id']))->find();
        return $data['belonged_organ_id'];
    }

    /**
     *根据帐号所属机构获取上级机构信息
     */
    public function belong_organ_parent()
    {
        $data = $this->organDB->field('parent_organ_id')->where(array('organ_id' => $this->belong_organ()))->find();
        return $data['parent_organ_id'];
    }

    /**
     *根据帐号角色获取机构信息
     */
    public function login_role_organ()
    {
        $where['role_id'] = array('in', $this->login_role);
        $data = D('RoleOrganRel')->where($where)->select();
        foreach ($data as $key => $val) {
            $role_organ[] = $val['organ_id'];
        }
        $organ = implode(',', array_filter(array_unique($role_organ)));
        return $organ;
    }

    /**
     *重组帐号角色管理机构信息
     */
    public function new_login_role_organ()
    {
        if ($this->login_role_organ()) {
            $organ_id = $this->login_role_organ() . "," . $this->sessionArr['organ_id'];
        } else {
            $organ_id = $this->sessionArr['organ_id'];
        }
        $where['organ_id'] = array('in', $organ_id);
        $data = $this->organDB->field('organ_id,channel_id')->where($where)->select();
        $info = array();
        foreach ($data as $key => $val) {
            $info[$val['channel_id']][] = $val['organ_id'];
        }
        return $info;
    }

    /**
     *获取企业机构id
     */
    public function ownOrgan()
    {
        $organ_id = $this->new_login_role_organ()[$this->sessionArr['organ_channel_id']];

        $organ_id = implode(',', $organ_id);
        return $organ_id;
    }

    /**
     *根据帐号角色获取模块信息
     */
    public function login_role_module()
    {
        $where['role_id'] = array('in', $this->login_role);
        $data = D('RoleModule')->where($where)->select();
        foreach ($data as $key => $val) {
            $role_module[] = $val['module_id'];
        }
        $module = implode(',', array_filter(array_unique($role_module)));
        return $module;
    }

    /**
     *分页公共函数
     */
    public function getPage($count, $where)
    {
        // 实例化分页类 传入总记录数和每页显示的记录数(20)
        $Page = new \Think\Page($count, 20);
        $Page->setConfig('header', '共<b>%TOTAL_ROW%</b>条记录&nbsp;&nbsp;每页<b>20</b>条&nbsp;&nbsp;第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页&nbsp;&nbsp;');
        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '末页');
        //分页跳转的时候保证查询条件
        foreach ($where as $key => $val) {
            $Page->parameter[$key] = urlencode($val);
        }
        $show = $Page->show();// 分页显示输出
        return array('show' => $show, 'firstRow' => $Page->firstRow, 'listRows' => $Page->listRows);
    }
}
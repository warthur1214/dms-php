<?php

namespace Home\Controller;

use Home\Common\RedisModel;
use Think\Controller;

class LoginController extends Controller
{
    private $redisDB;

    function __construct()
    {
        parent::__construct();
        $this->redisDB = new RedisModel();
    }

    /**
     *登录页面或者首页
     */
    public function index()
    {
        $redis_key = cookie('redis_key');
        $account_id = $this->redisDB->getRedis()->hget($redis_key, 'account_id');
        if ($account_id) {
            $this->display('Index/index');
        } else {
            $this->display('Index/login');
        }
    }

    /**
     *登录数据处理
     */
    public function loginAjax()
    {
        $accountDB = D('Account');
        $account = $accountDB->field('account_id,password')->where(array('is_available' => 1, 'account_name' => I('post.account_name')))->find();
        if (!empty($account)) {
            $msg = (I('post.password') == $account['password']) ? '登录成功' : '账号或密码错误';
            $status = (I('post.password') == $account['password']) ? 1 : 0;
            if (I('post.password') == $account['password']) {

                $loginRole = D('AccountRoleRel')->field('role_id')->where(array('account_id' => $account['account_id']))->select();
                $login_role_id = [];
                foreach ($loginRole as $key => $val) {
                    $login_role_id[] = $val['role_id'];
                }
                $where['role_id'] = array('in', implode(',', $login_role_id));
                $data = D('RoleModule')->where($where)->select();
                if (!$data) {
                    $msg = '账号所属角色未分配功能权限，请联系管理员';
                    $status = 0;
                    echo json_encode(array('msg' => $msg, 'status' => $status));
                    exit;
                }

                $array['account_id'] = $account['account_id'];
                //根据当前登录帐号获取帐号所属企业
                $organ_id = $accountDB->field('belonged_organ_id')->where(array('account_id' => $account['account_id']))->find();
                if ($organ_id['belonged_organ_id']) {
                    //获取帐号所属企业的标识
                    $organ = M('organ')->field('organ_id,channel_id,parent_organ_id')->where(array('organ_id' => $organ_id['belonged_organ_id']))->find();
                    //存储标识
                    $array['organ_channel_id'] = $organ['channel_id'];
                    $array['parent_organ_id'] = $organ['parent_organ_id'];
                    $array['organ_id'] = $organ['organ_id'];
                } else {
                    //存储标识
                    $array['organ_channel_id'] = 'pj_9999';
                    $array['parent_organ_id'] = '0';
                    $array['organ_id'] = '1';
                }

                $redis_key = 'session_' . time() . rand(1000, 9999);
                $this->redisDB->getRedis()->hmset($redis_key, $array);
                $this->redisDB->getRedis()->setTimeout($redis_key, 3600);
                cookie('redis_key', $redis_key, time() + 3600);
                //登录成功更新登录时间
                $accountDB->data(array('last_login_time' => date("Y-m-d H:i:s")))->where(array('account_id' => $account['account_id']))->save();
            }
        } else {
            $msg = '账号不存在或被冻结，请联系管理员';
            $status = 0;
        }
        echo json_encode(array('msg' => $msg, 'status' => $status));
        exit;
    }

    /**
     *退出
     */
    public function loginOut()
    {
        header("Location:/");
        $this->redisDB->getRedis()->delete(cookie('redis_key'));
        exit();
    }
}
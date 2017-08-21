<?php

namespace Home\Controller;


use Home\Model\SIMModel;

ini_set('memory_limit', '-1');  //所需内存
class SIMController extends BaseController
{
    private $simDB;

    function __construct()
    {
        parent::__construct();
        $this->simDB = new SIMModel($this->bizDB,'tp_');
    }

    public function index()
    {

    }

    /**
     *添加sim卡页
     */
    public function addSIM()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('addSIM', 0); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $where['coop_type_id'] = array('like', '%2%');
        $where['is_available'] = 1;
        $where['organ_type_id'] = C("ORGAN_TYPE_ID");
        $vender = $this->organDB->where($where)->field('organ_id as vender_id,organ_name as vender_name')->select();

        $this->assign('vender', $vender);
        $this->display('addSIM');
    }

    /**
     *添加sim卡数据处理
     */
    public function addSIMAjax()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('addSIM', 1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $info = $this->simDB->field('sim_card_id')->where(array('imsi' => I('post.imsi'), 'belonged_organ_id' => I('post.organ_id')))->find();

        if (!empty($info)) {
            echo json_encode(array('msg' => 'imsi号已存在，请重新输入', 'status' => 0));
            exit;
        }

        $array = array(
            'imsi' => trim(I('post.imsi')),
            'iccid' => trim(I('post.sim_iccid')),
            'msisdn' => trim(I('post.msisdn')),
            'total_flow' => I('post.total_flow'),
            'plan_term' => I('post.package_month'),
            'belonged_organ_id' => I('post.organ_id'),
            'supplied_organ_id' => I('post.vender_id')
        );
        $insertId = $this->simDB->data($array)->add();

        $msg = ($insertId > 0) ? '添加成功' : '添加失败';
        $status = ($insertId > 0) ? 1 : 0;

        echo json_encode(array('msg' => $msg, 'status' => $status));
        exit;
    }

    /**
     *sim卡列表页
     */
    public function simList()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('simList', 0); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $where['coop_type_id'] = array('like', '%2%');
        $where['is_available'] = 1;
        $where['organ_type_id'] = C("ORGAN_TYPE_ID");
        $vender = $this->organDB->where($where)->field('organ_id as vender_id,organ_name as vender_name')->select();

        $this->assign('vender', $vender);
        $this->display('simList');
    }

    /**
     *sim卡列表数据处理
     */
    public function simListAjax()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('simList', 1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $where = "1=1";
        if ($this->ownOrgan()) {
            $where .= " and sim.belonged_organ_id in (" . $this->ownOrgan() . ")";
        }
        //imsi搜索
        if (I('param.imsi')) {
            $where .= " and sim.imsi like '%" . I('param.imsi') . "%'";
        }
        //sim_iccid搜索
        if (I('param.sim_iccid')) {
            $where .= " and sim.iccid like '%" . I('param.sim_iccid') . "%'";
        }
        //设备号搜索
        if (I('param.device_id')) {
            $where .= " and v.device_no like '%" . I('param.device_id') . "%'";
        }
        //绑定状态搜索
        if (I('param.bind_status') != '') {
            $where .= " and sim.is_binded_device = " . I('param.bind_status');
        }
        //归属搜索
        if (I('param.organ_id')) {
            $where .= " and sim.belonged_organ_id ='" . I('param.organ_id') . "'";
        }
        //手机号搜索
        if (I('param.tel')) {
            $where .= " and user.tel like '%" . I('param.tel') . "%'";

        }
        //实名制状态搜索
        if (I('param.uni_real_name') != '') {
            $where .= " and sim.real_name_status = " . I('param.uni_real_name');
        }
        //应用硬件厂家搜索
        if (I('param.sim_vender')) {
            $where .= " and sim.supplied_organ_id ='" . I('param.sim_vender') . "'";
        }
        // 查询满足要求的总记录数
        $count = $this->simDB->simCnt($where);
        // 实例化分页类
        $page = $this->getPage($count[0]['cnt'], $where);

        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $data = $this->simDB->simList($where, $page['firstRow'], $page['listRows']);

        if (I('param.fileOut') == 1) {
            $data = $this->simDB->simList($where, '0', $count[0]['cnt']);
        }
        foreach ($data as $key => $val) {
            $val = array_map(array($this, 'filterNull'), $val);
            $data[$key] = $val;
            $data[$key]['uni_error_info'] = '';
            switch ($val['uni_real_name']) {
                case '4':
                    $data[$key]['uni_real_name'] = '未申请';
                    break;
                case '1':
                    $data[$key]['uni_real_name'] = '认证中';
                    break;
                case '2':
                    $data[$key]['uni_real_name'] = '已通过';
                    break;
                case '3':
                    $data[$key]['uni_real_name'] = '未通过';
                    $data[$key]['uni_error_info'] = $val['uni_error_info'];
                    break;
                default:
                    $data[$key]['uni_real_name'] = '未认证';
                    break;
            }
            $data[$key]['reg_time'] = $val['reg_time'] ? date('Y-m-d H:i:s', $val['reg_time']) : '';

            if ($val['bind_status'] != 1) {
                $data[$key]['device_no'] = '未绑定';
                $data[$key]['tel'] = '未绑定';
            }
            $organ_id[] = $val['belonged_organ_id'];
        }

        if (I('param.fileOut') == 1) {
            A('Excel')->simOut($data, $organ_id);
            exit;
        }
        echo json_encode(array('data' => $data, 'page' => $page['show']));
        exit;
    }

    /**
     *查看sim卡
     */
    public function getInfoById()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('simList', 1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/
        $fields = ['msisdn', 'belonged_organ_id', 'activated_time' => 'active_time', 'plan_term'];
        $info = $this->simDB->field($fields)->where(array('sim_card_id' => I('get.id')))->find();
        //归属
        $name = A('Vehicle')->getOwner($info['belonged_organ_id']);
        //归属企业
        $info['organ_name'] = $name['organ_name'];
        //归属公司
        $info['company_name'] = $name['company_name'];
        //归属机构
        $info['son_name'] = $name['son_name'];
        $info['expire_time'] = "";
        if ($info['active_time']) {
            $date=date_create($info['active_time']);
            date_add($date, date_interval_create_from_date_string($info['plan_term'] . ' months'));
            $info['expire_time'] = $date->format("Y-m-d H:i:s");
        }
        $info = array_map(array($this, 'filterNull'), $info);
        echo json_encode($info);
        exit;
    }

    /**
     *解绑sim卡
     */
    public function unbindSIM()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('unbindSIM', 1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $info = $this->simDB->field('is_binded_device')->where(array('sim_card_id' => I('get.id')))->find();
        if ($info['is_binded_device'] == '1') {
            $save = $this->simDB->data(array('device_id' => 0, 'activated_time' => '0000-00-00 00:00:00', 'is_binded_device' => 0))->where(array('sim_card_id' => I('get.id')))->save();
            $msg = ($save > 0) ? '解绑成功' : '解绑失败';
            $status = ($save > 0) ? 1 : 0;
        }
        echo json_encode(array('msg' => $msg, 'status' => $status));
        exit;

    }
}
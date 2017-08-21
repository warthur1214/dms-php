<?php

namespace Home\Controller;

use Home\Model\UserModel;

class UserController extends BaseController
{
    private $userDB;

    function __construct()
    {
        parent::__construct();
        $this->userDB = new UserModel($this->bizDB, 'tp_');
    }

    /**
     *用户列表页
     */
    public function userList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('userList', 0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('userList');
    }

    /**
     *获取用户信息
     */
    public function getInfo()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('userList', 1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $where = "1=1";

        if ($this->ownOrgan()) {
            $where .= " and v.belonged_organ_id in (" . $this->ownOrgan() . ")";
        }
        //查询手机号
        if (I('param.user_phone')) {
            $where .= " and user.tel like '%" . I('param.user_phone') . "%'";
        }
        //查询车牌号
        if (I('param.car_no')) {
            $where .= " and cp.plate_no like '%" . I('param.car_no') . "%'";
        }
        //查询设备号
        if (I('param.device_id')) {
            $where .= " and v.device_no like '%" . I('param.device_id') . "%'";
        }
        if (I('param.organ_id')) {
            $where .= " and v.belonged_organ_id = '" . I('param.organ_id') . "'";
        }
        // 查询满足要求的总记录数
        $count = $this->userDB->userCnt($where);
        // 实例化分页类
        $page = $this->getPage($count[0]['cnt'], $where);

        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $data = $this->userDB->userList($where, $page['firstRow'], $page['listRows']);

        if (I('param.fileStatus') == 1) {
            $data = $this->userDB->userList($where, 0, $count[0]['cnt']);
        }
        foreach ($data as $key => &$val) {
            $val = array_map(array($this, 'filterNull'), $val);
        }
        if (I('param.fileStatus') == 1) {
            A('Excel')->userOut($data);
            exit;
        }
        echo json_encode(array('data' => $data, 'page' => $page['show']));
        exit;
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

}
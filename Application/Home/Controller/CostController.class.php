<?php

namespace Home\Controller;

use Home\Model\CarModel;
use Home\Model\CostModel;
use Home\Model\DriverModel;
use Home\Model\VehicleModel;

class CostController extends BaseController
{
    private $costDB;
    private $driverDB;
    private $carDB;
    private $vehicleDB;

    function __construct()
    {
        parent::__construct();
        $this->costDB = new CostModel($this->bizDB, 'tp_');
        $this->driverDB = new DriverModel($this->bizDB, 'tp_');
        $this->carDB = new CarModel($this->bizDB, 'tp_');
        $this->vehicleDB = new VehicleModel($this->bizDB, 'tp_');
    }

    /**
     *车辆费用列表页
     */
    public function costList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('costList', 0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('costList');
    }

    /**
     *车辆费用列表数据
     */
    public function costListAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('costList', 1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $where = "1=1";
        $organWhere = [];
        if ($this->ownOrgan()) {
            $organWhere['organ_id'] = array('in', $this->ownOrgan());
        }

        $device = $this->vehicleDB->field('device_id')->where($organWhere)->select();
        foreach ($device as $key => $val) {
            $device_id[] = $val['device_id'];
        }
        if ($device_id) {
            $where .= " and v.device_id in (" . implode(',', $device_id) . ")";
        } else {
            $where .= " and v.device_id = '-1'";
        }
        //查询车牌号
        if (I('param.car_no')) {
            $where .= " and cost.car_id = '" . I('param.car_no') . "'";
        }
        //查询司机
        if (I('param.driver_id')) {
            $where .= " and cost.car_id = '" . I('param.driver_id') . "'";
        }
        //查询开始时间断
        if (I('param.cost_stime')) {
            $where .= " and cost.occur_time >= '" . I('param.cost_stime') . "'";
        }
        //查询结束时间断
        if (I('param.cost_etime')) {
            $where .= " and cost.occur_time <= '" . I('param.cost_etime') . "'";
        }
        //查询费用类型
        if (I('param.cost_type')) {
            $where .= " and cost.expense_type = '" . I('param.cost_type') . "'";
        }

        $data = $this->costDB->costList($where);
        echo json_encode(array('data' => $data));
        exit;
    }

    /**
     *添加车辆费用
     */
    public function addCost()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addCost', 0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('addCost');
    }

    /**
     *添加车辆费用数据处理
     */
    public function addCostAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addCost', 1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        if (!I('post.car_id')) {
            echo json_encode(['status'=>0, 'msg'=>'车牌号不存在！']);
            die;
        }

        $array = array(
            'expense_amount' => I('post.cost'),
            'expense_type' => I('post.cost_type'),
            'car_id' => I('post.car_id'),
            'occur_time' => I('post.cost_time')
        );

        $id = $this->costDB->data($array)->add();
        $msg = ($id > 0) ? '添加成功!' : '添加失败!';
        $status = ($id > 0) ? '1' : '0';
        echo json_encode(array('msg' => $msg, 'status' => $status));
        exit;
    }

    /**
     *修改车辆费用
     */
    public function editCost()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editCost', 0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('editCost');
    }

    /**
     *获取车辆费用信息
     */
    public function getInfo()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editCost', 1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $info = $this->costDB->costList("expense_id = " . I('get.id'));
        echo json_encode($info[0]);
        exit;
    }

    /**
     *修改车辆费用数据处理
     */
    public function editCostAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editCost', 1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $array = array(
            'expense_amount' => I('post.cost'),
            'expense_type' => I('post.cost_type'),
            'car_id' => I('post.car_id'),
            'occur_time' => I('post.cost_time')
        );
        $id = $this->costDB->data($array)->where(array('expense_id' => I('post.cost_id')))->save();
        $msg = ($id > 0) ? '修改成功!' : '修改失败或未修改!';
        $status = ($id > 0) ? '1' : '0';
        echo json_encode(array('msg' => $msg, 'status' => $status));
        exit;
    }

    /**
     *删除车辆费用
     */
    public function delCost()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('delCost', 1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $id = $this->costDB->where(array('expense_id' => I('get.id')))->delete();
        $msg = ($id > 0) ? '删除成功!' : '删除失败!';
        $status = ($id > 0) ? '1' : '0';
        echo json_encode(array('msg' => $msg, 'status' => $status));
        exit;
    }

    /**
     *获取车牌号信息
     */
    public function getCarNo($car_no = null)
    {
        $organWhere = [];
        if ($this->ownOrgan()) {
            $organWhere['d.belonged_organ_id'] = ['in', $this->ownOrgan()];
        }
        $device = $this->costDB->getCarNoByUser($organWhere);
        foreach ($device as $key => $val) {
            $device_id[] = $val['car_id'];
        }
        if ($device_id) {
            $carWhere['pr.car_id'] = array('in', implode(',', $device_id));
        }

        if ($car_no) {
            $carWhere['c.car_no'] = $car_no;
        }

        $carWhere['plate_no'] = array('neq', '');
        $data = $this->carDB->getCarNoById($carWhere);

        echo json_encode(array('data' => $data));
        exit;
    }

    /**
     *获取司机信息
     */
    public function getDriver()
    {
        $where = [];
        $data = $this->driverDB->getDriverById($where);
        echo json_encode(array('data' => $data));
        exit;
    }
}
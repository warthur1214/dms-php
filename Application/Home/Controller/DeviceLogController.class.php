<?php
namespace Home\Controller;

class DeviceLogController extends BaseController
{
    function __construct()
    {
        parent::__construct();
    }
    /**
    *设备日志列表页
    */
    public function logList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('logList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('logList');
    }
    /**
    *硬件厂家列表数据处理
    */
    public function logListAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('logList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $draw = I('post.draw');//计数器
        $start = I('post.start');//分页偏移值
        $end = I('post.length');//分页每页显示数
        $search = I('post.search');//查询框提交参数 array 取消使用
        $sort = I('post.order');//排序字段 array
        $columns = I('post.columns');//数据列 array

        //$data = $this->venderDB->order('vender_id desc')->select();

        //$dataCnt = count($data);

        $result = array(
            "draw"=>$draw,
            "recordsTotal"=>$dataCnt,
            "recordsFiltered"=>$dataCnt,
            "data"=>$data
        );

        echo json_encode($result);
        exit();
    }
}
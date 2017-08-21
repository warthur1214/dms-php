<?php
namespace Home\Controller;

use Home\Model\TrailModel;

class ScoreCountController extends BaseController
{
    private $trailDB;

    function __construct()
    {
        parent::__construct();
        $this->trailDB = new TrailModel($this->getNowDB(), $this->bizDB);
    }
    /**
    *得分统计列表
    */
    public function scoreCount()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('scoreCount',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('scoreCount');
    }
    /**
    *得分统计列表数据处理
    */
    public function scoreListAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('scoreCount',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $where = "1=1";
        $where .= " and v.belonged_organ_id in (".$this->ownOrgan().")";
        //查询数据库条件
        $dbWhere['TABLE_SCHEMA'] = array('like','ubi_'.$this->sessionArr['organ_channel_id'].'_%');
        
        $dbWhere['TABLE_NAME'] = array('like','%tp_sgl_jny_analysis_%');
        //获取数据库名称
        $dbName = D('GetDB')->dbName($dbWhere);
        //查询车牌号
        if(I('param.car_no'))
        {
            $where .= " and cp.plate_no like '%".I('param.car_no')."%'";
        }
        //查询设备号
        if(I('param.device_id'))
        {
            $where .= " and v.device_no like '%".I('param.device_no')."%'";
        }
        //查询车辆分组
        if(I('param.car_group'))
        {
            $where .= " and car.group_id = ".I('param.car_group');
        }
        //查询开始时间断
        if(I('param.score_stime'))
        {
            $where .= " and jny.end_time >= ".strtotime(I('param.score_stime'));
        }
        //查询结束时间断
        if(I('param.score_etime'))
        {
            $endTime = strtotime(I('param.score_etime')) + 3600*24 - 1;
            $where .= " and jny.end_time <= ".$endTime;
        }
        if(empty(I('param.score_stime')) && empty(I('param.score_etime')))
        {
            $endTime = strtotime(date('Y-m-d',strtotime('-1 days'))) + 3600*24 - 1;
            $where .= " and jny.end_time between ".strtotime(date('Y-m-d',strtotime('-3 days')))." and ".$endTime;
        }

        // 查询满足要求的总记录数
        $count = $this->trailDB->getScoreCnt($dbName,$where,$this->bizDB);
        // 实例化分页类
        $page = $this->getPage($count[0]['cnt'],$where);

        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $data = $this->trailDB->getScore($dbName,$where,$this->bizDB,$page['firstRow'],$page['listRows']);
        if(I('param.fileOut') == 1)
        {
            $data = $this->trailDB->getScore($dbName,$where,$this->bizDB,0,$count[0]['cnt']);
            A('Excel')->scoreOut($data);
            exit;
        }
        echo json_encode(array('data' => $data,'page' => $page['show']));
        exit;
    }
}
<?php

namespace Home\Controller;


use Home\Model\AgencyModel;

set_time_limit(0);
ini_set('memory_limit', '1024M');  //所需内存
ini_set('max_input_time', 900);  //PHP页面接收数据所需的最大时间
ini_set('max_execution_time', 900);  //每个PHP页面运行的最大时间
ini_set('session.gc_maxlifetime', 1800);
ini_set('session.cookie_lifetime', 1800);

class ExcelController extends BaseController
{
    public $objPHPExcel;
    public $objWriter;
    public $objActSheet;
    public $agencyDB;

    function __construct()
    {
        parent::__construct();
        vendor("PHPExcel.PHPExcel");
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel.PHPExcel_Cell");
        vendor("PHPExcel.PHPExcel.Settings");
        vendor("PHPExcel.PHPExcel.CachedObjectStorageFactory");
        $this->objPHPExcel = new \PHPExcel();
        $this->objWriter = new \PHPExcel_Writer_Excel5($this->objPHPExcel);
        $this->objActSheet = $this->objPHPExcel->getActiveSheet();
        $this->agencyDB = new AgencyModel($this->sessionArr['organ_channel_id']);
    }

    /**
     * excel导入
     * $filename 文件名
     * $type excel扩展类型
     * $encode 字符编码
     */
    public function read($filename, $type, $encode = 'utf-8')
    {
        $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objReader->setReadDataOnly(true);
        $objPHPExcel = \PHPExcel_IOFactory::load($filename);
        $objWorksheet = $objPHPExcel->getSheet(0);
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = array();
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row][] = (string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }
        return $excelData;
    }

    /**
     * 历史轨迹导出
     * $result 导出的数据
     */
    public function trailOut($result)
    {
        //列
        $col = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
        //标题
        $title = array('设备号', '车牌号', '点火时间', '熄火时间', '本次行驶距离（km）', '本次行驶时长', '本次行驶油耗（L）', '本次行程');
        //列值
        foreach ($result as $key => $val) {
            $value = array(
                $val['device_no'],
                $val['car_no'],
                date('Y-m-d H:i:s', $val['start_time']),
                date('Y-m-d H:i:s', $val['end_time']),
                $val['distance_travelled'],
                $val['duration'],
                $val['oil_wear'] / 1000,
                $val['start_address'] . " — " . $val['end_address']
            );
            $this->excel_text($col, $title, $value, $key);
        }

        $fileName = 'trail_list.xls';
        $this->export_excel($fileName);
        exit;
    }

    /**
     * 用户信息导出
     * $result 导出的数据
     */
    public function userOut($result)
    {
        //列
        $col = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
        //标题
        $title = array('用户手机号', '用户名', '车牌号', '车架号', '发动机号', '设备号', '所属企业', ' 注册时间');
        //列值
        foreach ($result as $key => $val) {
            $value = array(
                $val['tel'],
                $val['nickname'],
                $val['car_no'],
                $val['car_vin'],
                $val['motor_no'],
                $val['device_no'],
                $val['organ_name'],
                $val['create_time']
            );
            $this->excel_text($col, $title, $value, $key);
        }

        $fileName = 'user_list.xls';
        $this->export_excel($fileName);
        exit;
    }

    /**
     * 行驶统计信息导出
     * $result 导出的数据
     */
    public function travelOut($result)
    {
        //列
        $col = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K');
        //标题
        $title = array('开始时间', '结束时间', '车牌号', '设备号', '司机姓名', '驾驶时长(h)', '平均速度(km/h)', ' 最大速度(km/h)', '急加速(次)', '急减速(次)', '油耗(L)');
        //列值
        foreach ($result as $key => $val) {
            $value = array(
                $val['start_time'],
                $val['end_time'],
                $val['car_no'],
                $val['device_no'],
                $val['driver_name'],
                $val['duration'],
                $val['avg_speed'],
                $val['max_speed'],
                $val['accel_count'],
                $val['decel_count'],
                $val['oil_wear']
            );
            $this->excel_text($col, $title, $value, $key);
        }
        $fileName = 'travel_list.xls';
        $this->export_excel($fileName);
        exit;
    }

    /**
     * 得分统计信息导出
     * $result 导出的数据
     */
    public function scoreOut($result)
    {
        //列
        $col = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M');
        //标题
        $title = array('用户账号', '车牌号', '设备号', '总分', '加速', '减速', '速度', '夜间', '区域', '时长', '距离', '开始时间', '结束时间');
        //列值
        foreach ($result as $key => $val) {
            $value = array(
                $val['tel'],
                $val['car_no'],
                $val['device_no'],
                $val['risk_score'],
                $val['accel_score'],
                $val['decel_score'],
                $val['speed_score'],
                $val['night_score'],
                $val['area_score'],
                $val['duration_score'],
                $val['distance_score'],
                $val['start_time'],
                $val['end_time']
            );
            $this->excel_text($col, $title, $value, $key);
        }

        $fileName = 'score_list.xls';
        $this->export_excel($fileName);
        exit;
    }

    /**
     * 费用统计信息导出
     * $result 导出的数据
     */
    public function costOut($result)
    {
        //列
        $col = array('A', 'B', 'C', 'D', 'E', 'F');
        //标题
        $title = array('日期', '设备号', '车牌号', '司机名称', '费用类型', '费用');
        //列值
        foreach ($result as $key => $val) {
            $value = array(
                $val['cost_time'],
                $val['device_no'],
                $val['car_no'],
                $val['driver_name'],
                $val['cost_type'],
                $val['cost']
            );
            $this->excel_text($col, $title, $value, $key);
        }

        $fileName = 'cost_list.xls';
        $this->export_excel($fileName);
        exit;
    }

    /**
     * 设备信息导出
     * $result 导出的数据
     */
    public function vehicleOut($result)
    {
        //列
        $col = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P');
        //标题
        $title = array('设备号(IMEI)', '设备状态', '设备公司', '设备类型', '设备型号', 'IMSI（sim卡）', 'ICCID（sim卡）', 'MSISDN', '总流量', '套餐月份', '用户手机号', '车牌号', '车架号', '归属企业', '归属公司', '归属机构');
        //列值
        foreach ($result as $key => $val) {
            $value = array(
                $val['device_no'],
                $val['active_status'],
                $val['device_com'],
                $val['device_type'],
                $val['device_model'],
                $val['imsi'],
                $val['sim_iccid'],
                $val['msisdn'],
                $val['total_flow'],
                $val['package_month'],
                $val['tel'],
                $val['car_no'],
                $val['v_code'],
                $val['organ_name'],
                $val['company_name'],
                $val['son_name']
            );
            $this->excel_text($col, $title, $value, $key);
        }

        $fileName = 'device_list.xls';
        $this->export_excel($fileName);
        exit;
    }

    /**
     * sim卡导出
     * $result 导出的数据
     */
    public function simOut($result, $organ_id)
    {
        //列
        $col = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        //标题
        $title = array('IMSI（sim卡）', 'ICCID（sim卡）', 'MSISDN', '设备号', '总流量（MB）', '套餐月份', '归属企业', '归属公司', '归属机构', '应用硬件厂家');
        $new_organ = array_unique($organ_id);
        foreach ($new_organ as $key => $val) {
            $name[$val] = A('Vehicle')->getOwner($val);
        }
        //列值
        foreach ($result as $key => $val) {
            $value = array(
                $val['imsi'],
                $val['sim_iccid'],
                $val['msisdn'],
                $val['device_no'],
                $val['total_flow'],
                $val['package_month'],
                $name[$val['organ_id']]['organ_name'],
                $name[$val['organ_id']]['company_name'],
                $name[$val['organ_id']]['son_name'],
                $val['vender_name']
            );
            $this->excel_text($col, $title, $value, $key);
        }

        $fileName = 'sim_list.xls';
        $this->export_excel($fileName);
        exit;
    }

    /**
     *excel文档内容处理
     */
    public function excel_text($col, $title, $value, $key)
    {
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
        $cacheSettings = array();
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        $obj = $this->objPHPExcel->setActiveSheetIndex(0);
        $row = $key + 2;//内容行值
        //居中
        for ($i = 0; $i < count($col); $i++) {
            $obj->setCellValue($col[$i] . '1', $title[$i]);//标题行
            $obj->setCellValue($col[$i] . $row, $value[$i]);
            //标题
            //$this->objActSheet->getStyle($col[$i].'1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //正文
            //$this->objActSheet->getStyle($col[$i].$row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $this->objActSheet->setCellValueExplicit($col[$i] . $row, $value[$i], \PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objActSheet->getColumnDimension($col[$i])->setWidth(30);
        }
    }

    /**
     *导出excel
     */
    public function export_excel($fileName, $file_dir = "php://output")
    {
        header('Pragma:public');
        header('Expires:0');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');

        if ($file_dir == "php://output") {
            header('Content-Type:application/force-download');
            header('Content-Type:application/download');
        }
        header('Content-Type:application/vnd.ms-excel;charset=UTF-8');
        header('Content-Type:application/octet-stream');
        header('Content-Disposition:attachment;filename=' . $fileName);
        header('Content-Transfer-Encoding:binary');

        $this->objWriter->save($file_dir);
    }

    /**
     * 对账结算导出功能
     * $result 导出的数据
     */
    public function agencyInfoLoadExcel()
    {
        //列
        $col = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        //标题
        $title = ['ID', '国寿财分公司', '国寿财中支名称', '一级商全称', '购车客户名', 'VIN码', '结算费用', '有无实销',
            '车辆激活状态', '保单收付日期'];

        $where = "";

        if ($ids = I("param.id")) {
            $where .= " and b.id in ({$ids})";
        } else {
            if ($buyer = I("param.buyer")) {
                $where .= " and b.buyer like '%{$buyer}%'";
            }
            if ($company_branch = I("param.company_branch")) {
                $where .= " and a.company_branch = '{$company_branch}'";
            }
            if ($agency_full_name = I("param.agency_full_name")) {
                $where .= " and a.agency_full_name like '%{$agency_full_name}%'";
            }
            if (($sale_status = I("param.sale_status")) != null) {
                $where .= " and b.sale_status = {$sale_status}";
            }
            if (($active_status = I("param.active_status")) != null) {
                if ($active_status == 0) {
                    $where .= " and (v.status = {$active_status} or v.status is null)";
                } else {
                    $where .= " and v.status = {$active_status}";
                }
            }
            if ($start_pay_date = I("param.start_pay_date")) {
                $where .= " and b.pay_business_date >= '{$start_pay_date}'";
            }
            if ($end_pay_date = I("param.end_pay_date")) {
                $where .= " and b.pay_business_date <= '{$end_pay_date}'";
            }
            if (($email_status = I("param.email_status")) != null) {
                $where .= " and b.email_status = '{$email_status}'";
            }

            if (($pj_status = I("param.pj_status")) != null) {
                $where .= " and b.pj_status = {$pj_status}";
            }

            if (($balance_status = I("param.balance_status")) != null) {
                if ($balance_status == 0) {
                    $where .= " and (v.status = 0 or v.status is null or b.sales_status=0)";
                } else {

                    $where .= " and (v.status = 1 and b.sales_status = 1)";
                }
            }
        }

        $agencyList = $this->agencyDB->selectMoneyList($where);

        if (count($agencyList) == 0) {
            echo json_encode(['msg' => '无数据导出!', 'status' => 0]);
            die;
        }

        //列值
        foreach ($agencyList as $key => &$val) {

            unset($val['pj_status']);
            $rows = array_values($val);
            $this->excel_text($col, $title, $rows, $key);
        }

        $fileName = 'agency_info.xls';
        $this->export_excel($fileName);
        exit;
    }

    /**
     * 邮件对账信息附件
     */
    public function BillDataToExcel($file_dir, $agency_id, $bill_date, $update_time) {
        //列
        $col = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S'
            , 'T'];
        //标题
        $title = ['编号','省份', '国寿财省分名称', '国寿财中支名称', '经销商全称', '一级商全称'
            , 'VIN码', '购车客户名', '交强险实收金额', '交强保单号', '收付日期', '商业险实收金额', '商业保单号'
            , '收付日期', '商业险2600-3000元保单结算费用', '商业险超3000元结算费率', '商业险超3000元结算费用'
            , '长安匹配', '激活状态', '是否结算'];



        $agencyList = $this->agencyDB->selectAttechmentList($agency_id, $bill_date, $update_time);

        if (count($agencyList) == 0) {
            echo json_encode(['msg' => '无数据导出!', 'status' => 0]);
            die;
        }

        //列值
        foreach ($agencyList as $key => &$val) {

            $val['balance_by_pj'] = $val['active_status'] && $val['sales_status'] ? "是" : "否";
            $val['sales_status'] = $val['sales_status'] == 1 ? "实销" : "非实销";
            $val['active_status'] = $val['active_status'] == 1 ? "是" : "否";

            switch ($val['pj_status']) {
                case 0:
                    $val['pj_status'] = "未处理";
                    break;
                case 1:
                    $val['pj_status'] = "国寿财已打款";
                    break;
                case 2:
                    $val['pj_status'] = "评驾已核对";
                    break;
                case 3:
                    $val['pj_status'] = "经销商已开票";
                    break;
                case 4:
                    $val['pj_status'] = "评驾已结算";
                    break;
                default:
                    $val['pj_status'] = "异常状态";
            }

            $rows = array_values($val);
            $this->excel_text($col, $title, $rows, $key);
        }

        $fileName = 'attachment_info.xls';
        $this->export_excel($fileName, $file_dir);
    }
}

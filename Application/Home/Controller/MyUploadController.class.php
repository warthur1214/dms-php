<?php

namespace Home\Controller;

use Exception;
use Home\Common\RedisModel;
use Home\Model\AgencyModel;
use Home\Model\BillModel;
use Home\Model\DeviceModel;
use Home\Model\DeviceModelModel;
use Home\Model\FenceModel;
use Home\Model\SIMModel;
use Home\Model\VehicleModel;
use Home\Model\VenderModel;
use Think\Upload;


ini_set('memory_limit', '1024M');  //所需内存
set_time_limit(0);
ini_set('upload_max_filesize', '10M');  //上传文件最大值
ini_set('post_max_size', '10M');  //表单上传最大值
ini_set('max_input_time', 900);  //PHP页面接收数据所需的最大时间
ini_set('max_execution_time', 900);  //每个PHP页面运行的最大时间
ini_set('session.gc_maxlifetime', 1800);
ini_set('session.cookie_lifetime', 1800);

class MyUploadController extends BaseController
{
    private $upload;
    private $agencyDB;
    private $billDB;
    private $simDB;
    private $fenceDB;
    private $venderDB;
    private $deviceDB;
    private $deviceModelDB;
    private $vehicleDB;
    private $redisDB;

    function __construct()
    {
        parent::__construct();

        vendor("PHPExcel.PHPExcel.Shared.Date");
        $this->upload = new Upload();
        $this->agencyDB = new AgencyModel($this->sessionArr['organ_channel_id']);
        $this->billDB = new BillModel($this->sessionArr['organ_channel_id']);
        $this->simDB = new SIMModel($this->bizDB,'tp_');
        $this->fenceDB = new FenceModel($this->bizDB, 'tp_');
        $this->venderDB = new VenderModel('auth', 'tp_organ_type');
        $this->deviceDB = new DeviceModel('biz', 'tp_device_type');
        $this->deviceModelDB = new DeviceModelModel('biz', 'tp_device_series');
        $this->vehicleDB = new VehicleModel($this->bizDB, 'tp_');
        $this->redisDB = new RedisModel();
    }

    public function index()
    {
        $i = 1;
        //$this->upload->maxSize   =     3145728 ;// 设置附件上传大小
        $this->upload->exts = array('xls', 'xlsx');// 设置附件上传类型
        $this->upload->rootPath = SITE_PATH . '/Public/upload/'; // 设置附件上传根目录
        $this->upload->savePath = ''; // 设置附件上传（子）目录
        $this->upload->autoSub = true; // 开启子目录保存 并以日期为子目录
        $this->upload->subName = array('date', 'Ymd');
        $this->upload->saveName = time() . '_' . mt_rand();
        // 上传文件
        $info = $this->upload->upload();
        switch (I('get.import')) {
            case 'device':
                $this->deviceImport($info);
                break;
            case 'sim':
                $this->simImport($info);
                break;
            case 'moneyList':
                $this->uploadMoneyList($info);
                break;
            case 'agencyList':
                $this->uploadAgencyList($info);
                break;
        }
        exit;
    }

    /**
     * 设备号导入
     * $info 导入的数据
     */
    public function deviceImport($info)
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('deviceImport', 1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $new_vender = [];
        $new_channel = [];
        $new_device = [];
        $new_organ = [];

        //设备类型
        $device_type = $this->deviceDB->select();
        foreach ($device_type as $key => $val) {
            $new_device[$val['device_type_name']] = $val['device_type_id'];
        }
        //硬件厂家
        $where['coop_type_id'] = array('like','%2%');
        $where['is_available'] = 1;
        $where['organ_type_id'] = C("ORGAN_TYPE_ID");
        $vender = $this->organDB->where($where)->field('organ_id as vender_id,organ_name as vender_name')->select();

        foreach ($vender as $key => $val) {
            $new_vender[$val['vender_name']] = $val['vender_id'];
        }
        //归属
        $organ = $this->organDB->field('organ_id,organ_name,channel_id')->select();
        foreach ($organ as $key => $val) {
            $new_organ[$val['organ_name']] = $val['organ_id'];
            $new_channel[$val['organ_name']] = $val['organ_channel_id'];
        }

        $defect = $repeat = $sum = 0;
        //事务开启
        M()->startTrans();

        $this->redisDB->getRedis()->select(0);
        //redis事务
        $this->redisDB->getRedis()->multi();

        if ($info) {
            $file = $this->upload->rootPath . $info['Filedata']['savepath'] . $info['Filedata']['savename'];
            $type = $info['Filedata']['ext'];
            $file = str_replace('\\', '/', $file);
            $data = A('Excel')->read($file, $type);

            foreach ($data as $key => $val) {
                $val = array_filter($val);

                if (empty($val)) {
                    continue;
//                    unlink($file);
//                    echo json_encode(array('msg' => '导入失败，请确认设备信息是否正确！', 'status' => '0'));
//                    exit;
                }
                if ($key == 1 && $val[0] != '*设备号(IMEI)') {
                    unlink($file);
                    echo json_encode(array('msg' => '导入失败，请先下载设备信息模板！', 'status' => '0'));
                    exit;
                }
                if ($key != 1) {
                    $sum++;
                    $device['device_no'] = strtoupper(trim($val[0]));
                    //根据硬件厂家名称获取id
                    $device['supplied_organ_id'] = $new_vender[$val[1]];
                    if (!$device['supplied_organ_id']) {
                        unlink($file);
                        echo json_encode(array('msg' => '导入失败，硬件厂商机构不存在，请重新验证数据！', 'status' => '0'));
                        exit;
                    }

                    //根据所属企业名称获取id
                    $device['belonged_organ_id'] = $new_organ[$val[4]];
                    if (!$device['belonged_organ_id']) {
                        unlink($file);
                        echo json_encode(array('msg' => '导入失败，所导入设备归属不存在，请重新验证数据！', 'status' => '0'));
                        exit;
                    }

                    $device_series = M("biz.device_series ds")
                        ->where(['ds.device_series'=>$val[3], 'dt.supplied_organ_id'=>$device['supplied_organ_id']])
                        ->join("biz.tp_device_type dt ON dt.device_type_id = ds.device_type_id")
                        ->field('ds.device_series_id')
                        ->find();
//                    $sql = M("biz.device_series ds")->getLastSql();
                    $device['device_series_id'] = $device_series['device_series_id'];
                    if (!$device['device_series_id']) {
                        unlink($file);
                        echo json_encode(array('msg' => '导入失败，所导入设备型号不存在，请重新验证数据！', 'status' => '0'));
                        exit;
                    }

                    try {
                        $insertId = $this->vehicleDB->addVehicle($device);
                        if ($insertId) {
                            $this->redisDB->getRedis()->hset($this->sessionArr['organ_channel_id'] . ':hardwareInfo', $device['device_no'], $device['belonged_organ_id']);
                            $this->redisDB->getRedis()->hset($this->sessionArr['organ_channel_id'] . ':device_pk', $device['device_no'], $insertId);
                            $con[] = $insertId;
                        }
                    } catch (\Think\Exception $e) {
                        $msg = $e->getMessage();
                        $code = trim(explode(':', $msg)[0]);
                        switch ($code) {
                            case '1048'://必填项缺失
                                $defect++;
                                break;
                            case '1062'://重复数据
                                $repeat++;
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
            //成功数量
            $success = $sum - $defect - $repeat;
            unlink($file);
            if (count($con) > 0) {
                //事务提交
                M()->commit();
                $this->redisDB->getRedis()->exec();
                $msg = '导入成功！总共' . $sum . '条。<br />成功' . $success . '条。<br />数据不全' . $defect . '条。<br />重复' . $repeat . '条。';
                $status = 1;
            } else {
                //事务回滚
                M()->rollback();
                //$this->in_redis->dis_card();
                $msg = '导入失败！';
                $status = 0;
            }
        }
        echo json_encode(array('msg' => $msg, 'status' => $status));
        exit;
    }

    /**
     * sim导入
     * $info 导入的数据
     */
    public function simImport($info)
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('simImport', 1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $new_organ = [];
        $new_vender = [];

        //归属
        $organ = $this->organDB->field('organ_id,organ_name')->select();
        foreach ($organ as $key => $val) {
            $new_organ[$val['organ_name']] = $val['organ_id'];
        }

        //硬件厂家
        $where['coop_type_id'] = array('like','%2%');
        $where['is_available'] = 1;
        $where['organ_type_id'] = C("ORGAN_TYPE_ID");
        $vender = $this->organDB->where($where)->field('organ_id as vender_id,organ_name as vender_name')->select();

        foreach ($vender as $key => $val) {
            $new_vender[$val['vender_name']] = $val['vender_id'];
        }
        $defect = $repeat = $sum = 0;
        if ($info) {
            $file = $this->upload->rootPath . $info['Filedata']['savepath'] . $info['Filedata']['savename'];
            $type = $info['Filedata']['ext'];
            $file = str_replace('\\', '/', $file);
            $data = A('Excel')->read($file, $type);

            //事务开启
            M()->startTrans();
            foreach ($data as $key => $val) {
                $val = array_filter($val);

                if (empty($val)) {
//                    continue;
                    unlink($file);
                    echo json_encode(array('msg' => '导入失败，请确认sim信息是否正确！', 'status' => '0'));
                    exit;
                }
                if ($key == 1 && $val[0] != '*IMSI（sim卡）') {
                    unlink($file);
                    echo json_encode(array('msg' => '导入失败，请先下载sim信息模板！', 'status' => '0'));
                    exit;
                }
                if ($key != 1) {
                    $sum++;
                    $sim['imsi'] = trim($val[0]);
                    $sim['iccid'] = trim($val[1]);
                    $sim['msisdn'] = trim($val[2]);
                    $sim['total_flow'] = $val[3];
                    $sim['plan_term'] = $val[4];
                    //根据所属企业名称获取id
                    $sim['belonged_organ_id'] = $new_organ[$val[5]];
                    if (!$sim['belonged_organ_id']) {
                        unlink($file);
                        echo json_encode(array('msg' => '导入失败，设备归属机构不存在，请重新验证数据！', 'status' => '0'));
                        exit;
                    }

                    $sim['supplied_organ_id'] = $new_vender[$val[6]];
                    if (!$sim['supplied_organ_id']) {
                        unlink($file);
                        echo json_encode(array('msg' => '导入失败，硬件厂商机构不存在，请重新验证数据！', 'status' => '0'));
                        exit;
                    }
                    try {
                        $insertId = $this->simDB->data($sim)->add();
                        $con[] = $insertId;
                    } catch (\Think\Exception $e) {
                        $msg = $e->getMessage();
                        $code = trim(explode(':', $msg)[0]);
                        switch ($code) {
                            case '1048'://必填项缺失
                                $defect++;
                                break;
                            case '1062'://重复数据
                                $repeat++;
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
            //成功数量
            $success = $sum - $defect - $repeat;
            if (count($con) > 0) {
                M()->commit();
                $msg = '导入成功！总共' . $sum . '条。<br />成功' . $success . '条。<br />数据不全' . $defect . '条。<br />重复' . $repeat . '条。';
                $status = 1;
            } else {
                M()->rollback();
                $msg = '导入失败！';
                $status = 0;
            }
            unlink($file);

        }
        echo json_encode(array('msg' => $msg, 'status' => $status));
        exit;
    }

    /**
     * 导入对账信息数据
     * @param $info
     */
    public function uploadMoneyList($info)
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('uploadMoneyList', 1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        $status = 0;
        $msg = "导入失败！";
        if ($info) {
            $file = $this->upload->rootPath . $info['Filedata']['savepath'] . $info['Filedata']['savename'];
            $type = $info['Filedata']['ext'];
            $file = str_replace('\\', '/', $file);
            $data = A('Excel')->read($file, $type);

            try {
                //事务开启
                M()->startTrans();
                $new = $extend = $repeat = $jump = 0;
                $extend = 0;
                $sum = count($data) - 1;
                $extendArr = [];
                foreach ($data as $key => $val) {

                    if ($key == 1) {
                        continue;
                    }

                    $val = array_filter($val);

                    if (empty($val) || in_array("", $val) || in_array(null, $val)) {
                        unlink($file);
                        echo json_encode(array('msg' => '导入失败，请确认文档信息是否完整！', 'status' => '0'));
                        exit;
                    }

                    $agencyInfo['agency_full_name'] = trim($val[4]);
                    $agencyInfo['agency_proxy'] = trim($val[5]);
                    $agencyInfo['agency_province'] = trim($val[1]);
                    $agencyInfo['company_branch'] = trim($val[2]);
                    $agencyInfo['company_department'] = trim($val[3]);
                    $agencyInfo['sale_count'] = trim($val[0]);

                    $billData['vin'] = trim($val[6]);
                    $billData['buyer'] = trim($val[7]);
                    $billData['car_type'] = trim($val[8]);
                    $billData['car_type_no'] = trim($val[9]);
                    $billData['insurance_traffic_money'] = trim($val[10]) ? trim($val[10]) : 0;
                    $billData['insurance_traffic_number'] = trim($val[11]);
                    $billData['insurance_business_money'] = trim($val[13]) ? trim($val[13]) : 0;
                    $billData['insurance_business_number'] = trim($val[14]);
                    $billData['balance_by_pj'] = trim($val[19]);
                    $billData['actual_sales'] = trim($val[20]);
                    $billData['sales_status'] = $billData['actual_sales'] == "实销" ? "1" : "0";

                    // 无法识别 2015.5.9 这种日期
                    $pay_traffic_date = is_numeric(trim($val[12])) ? \PHPExcel_Shared_Date::ExcelToPHP(trim($val[12])) : strtotime(trim($val[12]));
                    $pay_business_date = is_numeric(trim($val[15])) ? \PHPExcel_Shared_Date::ExcelToPHP(trim($val[15])) : strtotime(trim($val[15]));

                    $billData['pay_traffic_date'] = date("Y-m-d", $pay_traffic_date);
                    $billData['pay_business_date'] = date("Y-m-d", $pay_business_date);
                    $billData['ins_money_i'] = trim($val[16]) ? trim($val[16]) : 0;
                    $billData['ins_money_ii'] = trim($val[17]) ? trim($val[17]) : 0;
                    $billData['ins_money_ii_rate'] = trim($val[18]) ? trim($val[18]) : 0;
                    $billData['email_status'] = 0;

                    $where = ['agency_full_name' => $agencyInfo['agency_full_name']];
                    $agency = $this->agencyDB->selectAgencyInfo($where, ['id']);

                    if (!$agency) {
                        $agency_id = $this->agencyDB->insertAgencyByExcel($agencyInfo);
                    } else {
                        $this->agencyDB->updateAgencyByExcel($agencyInfo, ["id" => $agency['id']]);
                        $agency_id = $agency['id'];
                    }

                    $where1['vin'] = $billData['vin'];
                    $where1['agency_id'] = $agency_id;

                    $billInfo = $this->billDB->getBillInfo($where1, ['id', 'pj_status']);
//                    $sql = $this->billDB->getLastSql();
                    if ($billInfo) {
                        if ($billInfo['pj_status'] == 0) {
                            $this->billDB->updateAgencyBillByExcel($billData, ["id" => $billInfo['id']]);
                            $extend++;
                            $extendArr[] = $key;
                        } else {
                            $jump++;
                        }

                    } else {

                        $billData['pj_status'] = 0;
                        $billData['agency_id'] = $agency_id;

                        $this->billDB->insertAgencyBillByExcel($billData);
                        $new++;
                    }

                }

                //事务提交
                M()->commit();

                //成功数量
                $extIds = implode(",", $extendArr);
                unlink($file);
                $msg = "导入成功！总共" . $sum . "条。<br />新增导入：{$new} 条数据<br/>"
                    . "跳过：{$jump}条数据<br/>覆盖数据：{$extend}条数据<br/>";
                if ($extend>0) {
                    $msg .= "原始表中：第{$extIds}行";
                }

                $status = 1;
            } catch (Exception $e) {
                //事务回滚
                M()->rollback();
                $status = 0;
                $msg = explode("\n", $e->getMessage())[0];
            }
        }
        echo json_encode(array('msg' => $msg, 'status' => $status));
        exit;
    }

    /**
     * 导入经销商数据
     * @param $info
     */
    public function uploadAgencyList($info)
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('uploadAgencyList', 1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $defect = $repeat = $sum = 0;
        $status = 0;
        $msg = "导入失败！";
        if ($info) {
            $file = $this->upload->rootPath . $info['Filedata']['savepath'] . $info['Filedata']['savename'];
            $type = $info['Filedata']['ext'];
            $file = str_replace('\\', '/', $file);
            $data = A('Excel')->read($file, $type);

            try {
                //事务开启
                M()->startTrans();

                $sum = count($data) - 1;
                $repeatArr = [];
                $jump = 0;
                $title = ['经销商全称', '银行账号（经销商）', '开户行（经销商）', '结算联系人（经销商）', '电话（经销商）',
                    '邮箱（经销商）'];
                foreach ($data as $key => $val) {
                    $val = array_filter($val);

                    if (empty($val) || in_array("", $val) || in_array(null, $val)) {
                        unlink($file);
                        echo json_encode(array('msg' => '导入失败，请确认文档信息是否完整！', 'status' => '0'));
                        exit;
                    }



                    if ($key == 1) {
                        continue;
                    }

//					$agencyInfo['agency_full_name'] = trim($val[4]);
//                    $agencyInfo['sale_count'] = trim($val[0]);
                    $agencyInfo['balance_person'] = trim($val[1]);
                    $agencyInfo['telephone'] = trim($val[2]);
                    $agencyInfo['email'] = trim($val[3]);
//                    $agencyInfo['balance_status'] = trim($val[21]);
                    $agencyInfo['bank_account'] = trim($val[5]);
                    $agencyInfo['bank_name'] = trim($val[4]);
//                    $agencyInfo['pay_date'] = trim($val[11]);
//                    $agencyInfo['invoice_pj'] = trim($val[12]);
//                    $agencyInfo['agency_confirm'] = trim($val[13]);
//                    $agencyInfo['pay_count'] = trim($val[14]);
//                    $agencyInfo['pay_money'] = trim($val[15]);
//                    $agencyInfo['remark'] = trim($val[16]);

                    $agency_full_name = trim($val[0]);
                    $res = $this->agencyDB->updateAgencyByExcel($agencyInfo, ["agency_full_name" => $agency_full_name]);
                    if ($res > 0) {
                        $repeatArr[] = $key;
                    } else {
                        $jump++;
                    }
                }

                //事务提交
                M()->commit();
                //成功数量
                $count = count($repeatArr);
                $repeatString = implode(",", $repeatArr);
                unlink($file);

                $msg = "导入成功！总共{$sum}条。<br />新增：0 条数据<br/>跳过：{$jump} 条数据<br/>更新：{$count}条数据<br/>";
                if (count($repeatArr) > 0) {
                    $msg .= "原始表中：第{$repeatString}行";
                }
                $status = 1;
            } catch (Exception $e) {
                //事务回滚
                M()->rollback();
                $status = 0;
                $msg = explode("\n", $e->getMessage())[0];
            }
        }
        echo json_encode(array('msg' => $msg, 'status' => $status));
        exit;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/4/20
 * Time: 17:50
 */

namespace Home\Controller;


use Home\Common\MailModel;
use Home\Common\RedisModel;
use Home\Model\AgencyModel;
use phpmailerException;
use Think\Exception;

class AgencyController extends BaseController
{

    protected $agencyDB;
    protected $mailer;
    protected $redisDB;

    public function __construct()
    {
        parent::__construct();
        $this->agencyDB = new AgencyModel($this->sessionArr['organ_channel_id']);
        $this->mailer = new MailModel();
        $this->redisDB = new RedisModel();
    }

    /**
     * 对账管理
     */
    public function moneyList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('moneyList', 0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $date['startDate'] = date("Y-m-d", strtotime("-7 days"));
        $date['endDate'] = date("Y-m-d");
        $this->assign('date', $date);
        $this->display('moneyList');
    }

    public function getCompanyBranchList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
//		A('Check')->isUse('moneyList', 0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $fields = ['tp_agency_bill.id' => 'agency_id'];
        $where = [];
        $group = "";
        $having = "";

        if (isset($_REQUEST['company_branch'])) {
            $fields[] = "company_branch";
            $company_branch = I("param.company_branch");
            if ($company_branch != null) {
                $where['company_branch'] = ['like', "$company_branch%"];
            }
            $group = "company_branch";
            $having = "company_branch is not null";
        }

        if (isset($_REQUEST['buyer'])) {
            $fields[] = "buyer";
            $buyer = I("param.buyer");
            if ($buyer) {
                $where['buyer'] = ['like', "{$buyer}%"];
            }
            $group = "buyer";
            $having = "buyer is not null";
        }

        if (isset($_REQUEST['agency_full_name'])) {
            $fields[] = "agency_full_name";
            $agency_full_name = I("param.agency_full_name");
            if ($agency_full_name != null) {
                $where['agency_full_name'] = ['like', "{$agency_full_name}%"];
            }
            $group = "agency_full_name";
            $having = "agency_full_name is not null";
        }

        if (isset($_REQUEST['balance_person'])) {
            $fields[] = "balance_person";
            $balance_person = I("param.balance_person");
            if ($balance_person != null) {

                $where['balance_person'] = ['like', "{$balance_person}%"];
            }
            $group = "balance_person";
            $having = "balance_person is not null";
        }


        $branchList = $this->agencyDB->selectCompanyBranch($where, $fields, $group, $having);

        echo json_encode(['msg' => "请求成功", "status" => 1, "data" => $branchList]);
        die;
    }

    public function moneyListAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('moneyList', 0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        $where = "";
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


        $cnt = $this->agencyDB->selectMoneyCount($where);
        $page = $this->getPage(count($cnt), $where);
        $moneyList = $this->agencyDB->selectMoneyList($where, $page['firstRow'], $page['listRows']);
        echo json_encode(['msg' => '请求成功', 'status' => 1, 'data' => $moneyList, 'page' => $page['show']]);
        die;
    }

    public function mailBody()
    {
        $this->display("body");
    }

    /**
     * 批量修改状态
     */
    public function updateMultiStatus()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('updateMultiStatus', 0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        $result = ['msg' => "请求失败", "status" => 0];
        if (empty(I("param.vins"))) {
            $result['msg'] = "错误参数";
            echo json_encode($result);
            die;
        }

        $vin_string = I("param.vins");
        $status = I("param.pj_status");
//        $status = 2;
        $this->agencyDB->startTrans();
        try {
            $update_time = date("Y-m-d H:i:s");
            $this->agencyDB->updatePjStatus($vin_string, $status, $update_time, 1);

            $result['msg'] = "请求成功";
            // 评驾已核算发邮件
            if ($status == 2) {
                $mailData = $this->agencyDB->selectMailAgencylist($vin_string);
                foreach ($mailData as $data) {
                    if ($data['email'] == "") {
                        $result['msg'] = "经销商联系人邮箱异常，无法发送邮件";
                        $result['status'] = 0;
                        echo json_encode($result);
                        die;
                    }

                    $number = $this->redisDB->getRedis()->hGet("ca_9999:bill_number", date("Y-m-d"));
                    $bill_number = $number ? $number + 1 : 1;
                    $data['bill_number'] = "PJ".date("ymd").sprintf("%05s", $bill_number);
                    $this->redisDB->getRedis()->hSet("ca_9999:bill_number", date("Y-m-d"), $bill_number);

                    $this->assign('mail', $data);
                    $contents = $this->fetch("body");
                    $bill_date = $data['bill_year'] . $data['bill_month'];

                    $attach_file = C("EXCEL_FIR_DIR") . "{$data['bill_year']}年{$data['bill_month']}月国寿财对账信息详情.xls";

                    A("Excel")->BillDataToExcel($attach_file, $data['agency_id'], $bill_date, $update_time);

                    $this->mailer->send($data['email'], C("EMAIL_TITLE"), $contents, $attach_file);

                    $this->mailer->clearAddresses();
                    $this->mailer->clearAttachments();
                    unlink($attach_file);
                }
                $result['msg'] = "此次核对数据库存在一下情况：<br/>核对完成：" . count($mailData) . " 家一级商<br/>核对失败：0 家一级商
                                  <br/> 失败名称：无";
            }

            $result['status'] = 1;
            $this->agencyDB->commit();
        } catch (Exception $e) {
            $this->agencyDB->rollback();
            if ($e instanceof phpmailerException) {
                $status = 1;
                $this->agencyDB->updatePjStatus($vin_string, $status, -1, true);
                $result['msg'] = "邮件发送失败，核算状态已回退！";
            } else {
                $result['msg'] = $e->getMessage();
            }

            $result['msg'] = $e->getMessage();
        }

        echo json_encode($result);
        die;
    }

    public function sendEmail()
    {
        $result = ['msg' => 'mail sucess', 'status' => 1];
        try {
            $mailData = $this->agencyDB->selectMailAgencylist('1');
            foreach ($mailData as $data) {

                $accept = "wuyongqiang@chinaubi.com";
                $title = "测试邮件";
                $this->assign('mail', $data);
                $contents = $this->fetch("body");
                $bill_date = $data['bill_year'] . $data['bill_month'];
//                A("Excel")->BillDataToExcel(C('EXCEL_FIR_DIR'), $data['agency_id'], $bill_date);
//                $attachment = C("EXCEL_FIR_DIR");

                $this->mailer->send($accept, $title, $contents);
//                unlink($attachment);
            }

        } catch (phpmailerException $e) {
            $result['msg'] = $e->getMessage();
            $result['status'] = 0;
        }
        echo json_encode($result);
        die;
    }

    /**
     * 经销商管理
     */
    public function agencyList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('agencyList', 0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('agencyList');
    }

    public function agencyListAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('agencyList', 0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $where = [];
        $fields = ['id', 'agency_full_name', 'balance_person', 'telephone', 'email', 'bank_name', 'bank_account'];
        if ($agency_full_name = I("param.agency_full_name")) {
            $where['agency_full_name'] = ['like', "%{$agency_full_name}%"];
        }

        if ($balance_person = I("param.balance_person")) {
            $where['balance_person'] = ['like', "%{$balance_person}%"];
        }


        $data = $this->agencyDB->selectAgencyCount($where, $fields);
        $sql = $this->agencyDB->getLastSql();

        $page = $this->getPage(count($data), $where);

        $agencyList = $this->agencyDB->selectAgencyList($where, $fields, $page['firstRow'], $page['listRows']);
        echo json_encode(['msg' => '请求成功！', 'status' => 1, 'data' => $agencyList, "page" => $page['show']]);
        die;
    }

    public function editAgency()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editAgency', 0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $agencyId = I("param.id");
        if ($agencyId == null) {
            echo json_encode(['msg' => '非法请求！', 'status' => 0]);
            die;
        }

        $where['tp_agency.id'] = $agencyId;
        $fields = ['tp_agency.id' => 'agency_id', 'agency_full_name', 'balance_person', 'telephone', 'email', 'bank_name', 'bank_account'];

        $agencyInfo = $this->agencyDB->selectAgencyInfo($where, $fields);

        $this->assign('agency', $agencyInfo);
        $this->display('editAgency');
    }

    public function editAgencyAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editAgency', 0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $data = [];
        $where = [];
        if (!I("param.agency_id")) {
            echo json_encode(array('msg' => "错误参数", 'status' => "0"));
            die;
        }
        $where['id'] = I("param.agency_id");
        $data['agency_full_name'] = I("param.agency_full_name");
        $data['balance_person'] = I("param.balance_person");
        $data['telephone'] = I("param.telephone");
        $data['email'] = I("param.email");
        $data['bank_name'] = I("param.bank_name");
        $data['bank_account'] = I("param.bank_account");

        $resultId = $this->agencyDB->updateAgencyInfo($where, $data);
        $msg = ($resultId > 0) ? '修改成功' : '修改失败或无修改';
        $status = ($resultId > 0) ? 1 : 0;

        echo json_encode(array('msg' => $msg, 'status' => $status));
        die;
    }

}
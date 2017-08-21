<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/4/21
 * Time: 11:33
 */

namespace Home\Model;


use Home\Common\BaseModel;
use Think\Exception;
use Think\Model;

class AgencyModel extends Model
{
    protected $dbName = "biz";
    protected $trueTableName = 'tp_agency';
    protected $channel;

    public function __construct($channel)
    {
        $this->channel = $channel;
        parent::__construct();
    }

    public function selectAgencyCount($where, $field = "*") {
        try {
            $where['channel_id'] = $this->channel;
            return $this->field($field)->where($where)->select();
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function selectAgencyList($where, $field = "*", $first=0, $size=20)
    {
        try {
            $where['channel_id'] = $this->channel;
            return $this->field($field)->where($where)->limit($first, $size)->select();
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function selectAgencyInfo($where, $field = "*")
    {
        try {
            $where['channel_id'] = $this->channel;
            return $this->where($where)
                ->field($field)
                ->find();
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function updateAgencyInfo($where, $data)
    {
        try {
            $where['channel_id'] = $this->channel;
            return $this->where($where)->data($data)->save();
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function selectCompanyBranch($where, $field = "", $group, $having)
    {
        try {
            $where['channel_id'] = $this->channel;
            return $this->where($where)
                ->field($field)
                ->join("{$this->dbName}.tp_agency_bill on tp_agency.id = tp_agency_bill.agency_id")
                ->group($group)
                ->having($having)
                ->select();
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function selectMoneyCount($where) {
        try {
            $sql = "SELECT 
						count(1) AS cnt
					FROM biz.tp_agency a, biz.tp_agency_bill b
					LEFT JOIN biz_{$this->channel}.tp_car c ON b.vin = c.vin
					LEFT JOIN biz_{$this->channel}.tp_device v ON c.car_id = v.car_id
					WHERE a.id = b.agency_id
					AND a.channel_id='{$this->channel}'
				    {$where}
				    GROUP BY b.buyer
				    ";
            return $this->query($sql);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function selectMoneyList($where, $first=0, $size=20)
    {
        try {
            $sql = "SELECT 
						b.id, a.company_branch, a.company_department, a.agency_full_name, b.buyer, b.vin,
 						(ifnull(b.ins_money_i, 0) + ifnull(b.ins_money_ii, 0)) AS insurance_money, 
 						if(b.sales_status=1, '实销', '非实销') AS sales_status, b.pj_status,
	                    CASE v.status WHEN 1 THEN '已激活' ELSE '未激活' END AS active_status, 
	                    date_format(b.pay_business_date, '%Y-%m-%d') AS pay_business_date
					FROM biz.tp_agency a, biz.tp_agency_bill b
					LEFT JOIN biz_{$this->channel}.tp_car c ON b.vin = c.vin
					LEFT JOIN biz_{$this->channel}.tp_device v ON c.car_id = v.car_id
					WHERE a.id = b.agency_id
					AND a.channel_id='{$this->channel}'
				    {$where}
				    GROUP BY b.buyer
				    LIMIT {$first}, {$size}
				    ";
            return $this->query($sql);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function updatePjStatus($vin_string, $status, $update_time, $email_status, $mail_fail=false)
    {
        $vinArr = explode(",", $vin_string);
        $vins = implode(",", $vinArr);
        try {

            $sql = "UPDATE biz.tp_agency_bill b, biz.tp_agency a
                    SET b.pj_status={$status}, b.email_status={$email_status}, b.update_time='{$update_time}'
                    WHERE a.id = b.agency_id
                    AND b.id IN ({$vins}) 
                    AND a.channel_id='{$this->channel}'
                    ";
            if ($mail_fail) {
                $sql .= " and email_status=-1";
            }

            return $this->execute($sql);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function selectMailAgencylist($vin_string)
    {
        try {
            $sql = "SELECT 
                        date_format(b.pay_business_date, '%y') bill_year, 
                        date_format(b.pay_business_date, '%c') bill_month, '长安创新项目' AS project_name,
                        a.agency_full_name, sum((ifnull(b.ins_money_i, 0) + ifnull(b.ins_money_ii, 0))) AS insurance_money, 
                        now() AS mail_date, date_format(b.pay_business_date, '%c月') AS pay_month, 
                        a.balance_person, a.telephone, a.id AS agency_id, a.email
                    FROM biz.tp_agency_bill b, biz.tp_agency a 
                    WHERE a.id = b.agency_id
                    AND a.channel_id = '{$this->channel}'
                    AND b.id IN ({$vin_string})
                    GROUP BY a.id, DATE_FORMAT(b.pay_business_date, '%Y-%m')
                    ORDER BY b.pay_business_date ASC";
            return $this->query($sql);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function selectAttechmentList($agency_id, $bill_date, $update_time)
    {
        try {
            $sql = "SELECT 
                        b.id, a.agency_province, a.company_branch, a.company_department, a.agency_full_name, 
                        agency_proxy, b.vin, b.buyer, b.insurance_traffic_money, b.insurance_traffic_number,
                        date_format(b.pay_traffic_date, '%c月%e日') AS pay_traffic_date, b.insurance_business_money,
                        b.insurance_business_number, date_format(b.pay_business_date, '%c月%e日') AS pay_business_date,
                        b.ins_money_i, concat(b.ins_money_ii_rate, '%') AS ins_money_ii_rate, b.ins_money_ii, b.sales_status, 
                        v.status AS active_status, b.balance_by_pj
                    FROM biz.tp_agency_bill b
                    INNER JOIN biz.tp_agency a ON a.id = b.agency_id
					LEFT JOIN biz_{$this->channel}.tp_car c ON b.vin = c.vin
					LEFT JOIN biz_{$this->channel}.tp_device v ON c.car_id = v.car_id
                    WHERE a.channel_id = '{$this->channel}'
                    AND a.id = {$agency_id}
                    AND date_format(b.pay_business_date, '%y%c') = {$bill_date}
                    AND (b.update_time = '{$update_time}' OR v.active_status IS NULL OR v.active_status=0 OR b.sales_status = 0)
                    ORDER BY b.id ASC";
            return $this->query($sql);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function insertAgencyByExcel($data)
    {
        try {
            $data['channel_id'] = $this->channel;
            return $this->add($data);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function updateAgencyByExcel($data, $where)
    {
        try {
            $where['channel_id'] = $this->channel;
            return $this->where($where)
                ->data($data)
                ->save();
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}
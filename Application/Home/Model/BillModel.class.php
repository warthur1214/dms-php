<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/5/13
 * Time: 11:42
 */

namespace Home\Model;


use Exception;
use Think\Model;

class BillModel extends Model
{
    protected $dbName = "biz";
    protected $trueTableName = 'tp_agency_bill';
    protected $channel;

    function __construct($channel)
    {
        $this->channel = $channel;
        parent::__construct();
    }

    public function updateAgencyBillByExcel($data, $where)
    {
        try {
            $where['channel_id'] = $this->channel;
            return $this->where($where)->data($data)->save();
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function insertAgencyBillByExcel($data)
    {
        try {
            $where['channel_id'] = $this->channel;
            return $this->add($data);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function getBillInfo($where, $field="*")
    {
        try {
            return $this->where($where)->field($field)->find();
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}
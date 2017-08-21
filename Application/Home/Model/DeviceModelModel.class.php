<?php
namespace Home\Model;
use Think\Model;
class DeviceModelModel extends Model
{
    protected $dbName;
    protected $trueTableName;
    public function __construct($dbName,$trueTableName) 
    {
        $this->dbName = $dbName;
        $this->trueTableName = $trueTableName;
        parent::__construct();
    }

}
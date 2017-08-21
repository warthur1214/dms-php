<?php
namespace Home\Model;
use Think\Model;
class FenceCarModel extends Model
{
    protected $dbName;
    protected $trueTableName  = 'tp_device_e_fence_rel';
    public function __construct($dbName) 
    {
        $this->dbName = $dbName;
        parent::__construct();
    }

}
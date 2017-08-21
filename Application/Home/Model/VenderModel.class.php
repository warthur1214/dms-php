<?php
namespace Home\Model;
use Think\Model;
class VenderModel extends Model
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
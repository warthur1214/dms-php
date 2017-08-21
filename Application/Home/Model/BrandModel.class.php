<?php
namespace Home\Model;
use Think\Model;
class BrandModel extends Model
{
    protected $dbName = 'biz';
    protected $trueTableName  = 'tp_car_brand';
    public function __construct() 
    {
        parent::__construct();
    }

}
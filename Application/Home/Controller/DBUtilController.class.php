<?php
namespace Home\Controller;


define("START_TIME", 1451577600);
define("WH_TIME_UNIT", 31536000);
define("OBD_WH_USER_UNIT", 30000);
define("OBD_TB_USER_UNIT", 30);

define("API_WH_TIME_UNIT", 315360000);
define("API_WH_USER_UNIT", 1000000);
define("API_TB_USER_UNIT", 10000);

/**
 * 获取数据库分库分表
 */
class DBUtilController extends BaseController
{
	public $sys_id;


    public function getSchemaPeriodPart($time, $schemaStorePeriod) {
        return sprintf("%02d", ($time - START_TIME) / $schemaStorePeriod);
    }
    
    public function getSchemaRestPart($userId, $patitionCapacity) {
        return sprintf("%04d", $userId / $patitionCapacity);
    }

    public function getTablePart($userId, $patitionCapacity) {
        return sprintf("%04d", $userId / $patitionCapacity);
    }

    /**
     * 获取行程数据库分库分表信息
     * $channelId  标识id
     * $userId  用户id
     * $jnyTime  行程开始时间
     * $schemaPrefix  数据库前缀
     * $tablePrefix  数据表前缀
     * $schemaStoreCapacity  库数量
     * $tableStoreCapacity  表数量
     * $schemaStorePeriod  年限  
     */
    public function getJnyTableName($channelId, $userId, $jnyTime, $schemaPrefix = 'ubi_', $tablePrefix = 'tp_sgl_jny_analysis_', $schemaStoreCapacity = '100000', $tableStoreCapacity = '1000',$schemaStorePeriod = '157680000') 
    {

        $userId--;
        
        $sb = $schemaPrefix.substr($channelId,0, 2).'_'
        .substr($channelId,3).'_'
        .$this->getSchemaPeriodPart($jnyTime, $schemaStorePeriod).'_'
        .$this->getSchemaRestPart($userId, $schemaStoreCapacity)
        .'.'.$tablePrefix
        .$this->getTablePart($userId, $tableStoreCapacity);

        return $sb;
    }
	/**
	 * 获取表名
	 */
	public function getTableName($uId, $tb='tp_org_jny_', $wh='obd_') {
		$uId--;
		$table = "";
		$tme = time();
		$table = $wh.$this->sys_id.'_'.$this->getTPartNo($tme, WH_TIME_UNIT).$this->getUWPartNo($uId, OBD_WH_USER_UNIT).'.'.$tb.$this->getUTPartNo($uId, OBD_TB_USER_UNIT);

		return $table;
	}

	/**
	 * 获取locus库名表名
	 */
	public function getLocusTable($uId, $tb='tp_org_locus', $wh='obd_') {
		$uId--;
		$table = "";
		$tme = time();
		$table = $wh.$this->sys_id.'_'.$this->getTPartNo($tme, WH_TIME_UNIT).$this->getUWPartNo($uId, OBD_WH_USER_UNIT).'.'.$tb;

		return $table;
	}

	/**
	 * 获取api表
	 */
	public function getInsertTb($uId, $tb='tp_jny_scr_', $wh='api_') {
		$uId--;
		$table = "";
		$tme = time();
		$table = $wh.$this->sys_id.'_'.$this->getTPartNo($tme, API_WH_TIME_UNIT).$this->getUWPartNo($uId, API_WH_USER_UNIT).'.'.$tb.$this->getUTPartNo($uId, API_TB_USER_UNIT);

		return $table;
	}

	/**
	 * 查询表名
	 */
	public static function getTNames($uId, $sTme, $eTme, $tb='tp_org_jny_', $wh='obd_') {
		$uId--;
		$list = array();

		$tsNo = self::getTPartNo($sTme, WH_TIME_UNIT);
		$teNo = self::getTPartNo($eTme, WH_TIME_UNIT);

		$list[] = $wh. self::sys_id .'_'.$tsNo.self::getUWPartNo($uId, OBD_WH_USER_UNIT).'.'.$tb.self::getUTPartNo($uId, OBD_TB_USER_UNIT);
		$i=1;
		while ($tsNo < $teNo) {
			$tsNo++;
			$list[$i] = $wh. self::sys_id .'_'.$tsNo.self::getUWPartNo($uId,OBD_WH_USER_UNIT).'.'.$tb.self::getUTPartNo($uId, OBD_TB_USER_UNIT);
			$i++;
		}

		return $list;
	}

	private static function getTPartNo($tme, $unt) {
		return sprintf("%02s", floor(($tme - START_TIME) / $unt));
	}

	private static function getUTPartNo($uId, $unt) {
		return sprintf("%04d", $uId / $unt);
	}

	private static function getUWPartNo($uId, $unt) {
		return sprintf("%03d", $uId / $unt);
	}
}
?>
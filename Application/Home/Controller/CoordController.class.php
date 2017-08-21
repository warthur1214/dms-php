<?php
namespace Home\Controller;

class CoordController extends BaseController
{
    private $x_pi;
    private $PI;
    private $a;//a: 卫星椭球坐标投影到平面地图坐标系的投影因子。
    private $ee;//// ee: 椭球的偏心率。

    function __construct()
    {
        parent::__construct();
        $this->x_pi = 3.14159265358979324 * 3000.0 / 180.0;

        $this->PI = 3.1415926535897932384626;
        $this->a = 6378245.0;//a: 卫星椭球坐标投影到平面地图坐标系的投影因子。
        $this->ee = 0.00669342162296594323;//// ee: 椭球的偏心率。
    }



/**
 * 将 BD-09 To GCJ-02
 * @param bd_latitude 纬度
 * @param bd_longitude 经度
 * @return 
 */
    public function bd_decrypt($bd_latitude, $bd_longitude) {
        $gg_lat = 0.0;
        $gg_lon = 0.0;
        $location[] = [];
        $x = $bd_longitude - 0.0065;
        $y = $bd_latitude - 0.006;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $this->x_pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * $this->x_pi);
        $gg_lon = $z * cos($theta);
        $gg_lat = $z * sin($theta);
        return $this->gcj02towgs84($gg_lon, $gg_lat);
    }

/**
 * GCJ02 To WGS84
 * @param longitude
 * @param latitude
 * @return
 */
    public function gcj02towgs84($longitude, $latitude) {
        $location = [$longitude, $latitude];
        if ($this->out_of_china($longitude, $latitude)) {
            return $location;
        } else {
            $dlat = $this->transformlat($longitude - 105.0, $latitude - 35.0);
            $dlng = $this->transformlng($longitude - 105.0, $latitude - 35.0);
            $radlat = $latitude / 180.0 * $this->PI;
            $magic = sin($radlat);
            $magic = 1 - $this->ee * $magic * $magic;
            $sqrtmagic = sqrt($magic);
            $dlat = ($dlat * 180.0) / (($this->a * (1 - $this->ee)) / ($magic * $sqrtmagic) * $this->PI);
            $dlng = ($dlng * 180.0) / ($this->a / $sqrtmagic * cos($radlat) * $this->PI);
            $mglat = $latitude + $dlat;
            $mglng = $longitude + $dlng;
            $longitude = $longitude * 2 - $mglng;
            $latitude = $latitude * 2 - $mglat;
            $location[0] = $longitude;
            $location[1] = $latitude;
            return $location;
        }
    }

    /****
     * WGS-84 To GCJ-02
     * 
     * @param wgslatitude
     * @param wgslongitude
     * @return [参数说明]
     * @return [返回类型说明]
     */
    public function gcj_encrypt($wgslatitude, $wgslongitude) {
        $gcj_02 = [$wgslatitude, $wgslongitude];

        if ($this->out_of_china($wgslongitude, $wgslatitude)) {
            return $gcj_02;
        }
        $d = $this->delta($wgslatitude, $wgslongitude);

        $gcj_02[0] = $gcj_02[0] + $d[0];
        $gcj_02[1] = $gcj_02[1] + $d[1];

        return $this->bd_encrypt($gcj_02[0], $gcj_02[1]);
        
    }

  /**
   * GCJ-02 To BD-09
   * @param gglatitude
   * @param gglongitude
   * @return
   */
    public function bd_encrypt($gglatitude, $gglongitude) {
        $bd_lat = 0.0;
        $bd_lon = 0.0;
        $location = array();
        $x = $gglongitude;
        $y = $gglatitude;
        $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $this->x_pi);
        $theta = atan2($y, $x) + 0.000003 * cos($x * $this->x_pi);
        $bd_lon = $z * cos($theta) + 0.0065;
        $bd_lat = $z * sin($theta) + 0.006;
        $location[0] = $bd_lat;
        $location[1] = $bd_lon;
        return $location;
    
    }
/**
 * 偏移算法
 * @param longitude
 * @param latitude
 * @return
 */
    private function transformlat($longitude, $latitude) {
        $ret = -100.0 + 2.0 * $longitude + 3.0 * $latitude + 0.2 * $latitude * $latitude + 0.1
                * $longitude * $latitude + 0.2 * sqrt(abs($longitude));
        $ret += (20.0 * sin(6.0 * $longitude * $this->PI) + 20.0 * sin(2.0 * $longitude
                * $this->PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($latitude * $this->PI) + 40.0 * sin($latitude / 3.0 * $this->PI)) * 2.0 / 3.0;
        $ret += (160.0 * sin($latitude / 12.0 * $this->PI) + 320 * sin($latitude * $this->PI
                / 30.0)) * 2.0 / 3.0;  
        return $ret;
    }
/**
 * 偏移算法
 * @param longitude
 * @param latitude
 * @return
 */
    private function transformlng($longitude, $latitude) {
        $ret = 300.0 + $longitude + 2.0 * $latitude + 0.1 * $longitude * $longitude + 0.1 * $longitude * $latitude + 0.1 * sqrt(abs($longitude));
        $ret += (20.0 * sin(6.0 * $longitude * $this->PI) + 20.0 * sin(2.0 * $longitude
                * $this->PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($longitude * $this->PI) + 40.0 * sin($longitude / 3.0 * $this->PI)) * 2.0 / 3.0;
        $ret += (150.0 * sin($longitude / 12.0 * $this->PI) + 300.0 * sin($longitude / 30.0
                * $this->PI)) * 2.0 / 3.0;
        return $ret;
    }

    /**
     * 判断是否在国内，不在国内则不做偏移
     * 
     * @param longitude
     * @param latitude
     * @returns {boolean}
     */
    private function out_of_china($longitude, $latitude) {
        return ($longitude < 72.004 || $longitude > 137.8347)
                || (($latitude < 0.8293 || $latitude > 55.8271) || false);
    }
/**
 * 偏移
 * @param lat
 * @param lon
 * @return
 */
    private function delta($lat, $lon) {
        $dLat = $this->transformlat($lon - 105.0, $lat - 35.0);
        $dLon = $this->transformlng($lon - 105.0, $lat - 35.0);
        $radLat = $lat / 180.0 * $this->PI;
        $magic = sin($radLat);
        $magic = 1 - $this->ee * $magic * $magic;
        $sqrtMagic = sqrt($magic);

        $dLat = ($dLat * 180.0) / (($this->a * (1 - $this->ee)) / ($magic * $sqrtMagic) * $this->PI);
        $dLon = ($dLon * 180.0) / ($this->a / $sqrtMagic * cos($radLat) * $this->PI);
        $lang = [$dLat, $dLon];
        return $lang;
    }	
	
}

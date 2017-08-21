<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/6/7
 * Time: 11:40
 */

namespace Home\Common;


class RedisModel
{
    private $redis_key;
    private $in_redis;
    private $out_redis;

    public function __construct()
    {
        $this->redis_key = cookie('redis_key');
    }

    public function getRedis()
    {
        if (!$this->in_redis) {
            $this->in_redis = new \redis();
            $this->in_redis->pconnect(C('REDIS_HOST'), C('REDIS_PORT'));
            $this->in_redis->auth(C('REDIS_AUTH'));
        }
        return $this->in_redis;
    }

    public function getCodis()
    {
        if (!$this->out_redis) {

//        vendor('Zookeeper.PCodis');
//        $this->out_redis = \PCodis::getCodisInstance(C('ZK_ADDRESS'), C('ZK_PROXYPATH'), C('ZK_RETRYTIME'));

            $this->out_redis = new \redis();
            $this->out_redis->connect(C('CODIS_HOST'), C('CODIS_PORT'));
            $this->out_redis->auth(C('CODIS_AUTH'));
        }

        return $this->out_redis;
    }

    public function getSessionArr()
    {
        $sessionArr = $this->getRedis()->hmget($this->redis_key, array('account_id', 'organ_channel_id', 'parent_organ_id', 'organ_id'));
        $this->getRedis()->setTimeout($this->redis_key, 3600);
        return $sessionArr;
    }
}
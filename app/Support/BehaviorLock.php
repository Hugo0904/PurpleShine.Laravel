<?php
/**
 * Created by PhpStorm.
 * User: liangyuehchen
 * Date: 2019-09-27
 * Time: 16:27
 */

namespace App\Support;

use Illuminate\Support\Facades\Redis;

use Carbon\Carbon;

class BehaviorLock
{
    private $prefix = 'cache:behavior_lock:';
    private $expire;

    /**
     * BehaviorLock constructor.
     * @param int $keyExpire
     */
    public function __construct($keyExpire = 300)
    {
        $this->expire = $keyExpire;
    }

    /**
     * 試著取得鎖定
     *
     * @param $key
     * @param $value
     * @return bool
     */
    public function enter($key, $value = null): bool
    {
        $key = $this->getKey($key);
        $value = $value ?? Carbon::now()->toDateTimeString();
        if ($result = Redis::setnx($key, $value)) {
            Redis::expire($key, $this->expire);
            Redis::ttl($key);
        }

        return $result;
    }

    /**
     * 試著時間內取得鎖定
     *
     * @param $key
     * @param $value
     * @param float $timeout
     * @return bool
     */
    public function tryEnter($key, $value = null, $timeout = 0.00): bool
    {
        $now = microtime(true);
        while ($timeout === 0 || (microtime(true) - $now) < $timeout) {
            if ($this->enter($key, $value)) {
                return true;
            }
            usleep(5 * 1000);
        }
        return false;
    }

    /**
     * 釋放鎖定
     *
     * @param $key
     * @return bool
     */
    public function release($key): bool
    {
        return Redis::del($this->getKey($key));
    }

    private function getKey($key): string
    {
        $key = explode('/', $key);
        $key = implode('_', $key);
        return $this->prefix . strtolower($key);
    }
}
<?php

namespace App\Support\Utils;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

use App\Models\User;

class Generator
{
    /**
     * 唯一單號在表格中
     *
     * @param $table
     * @param $column
     * @param int $length
     * @return string
     */
    public static function uniqueForTable($table, $column, $length = 20)
    {
        /** @var Model $table */
        $table = app()->make('App\\Models\\' . ucfirst($table));

        do {
            $sn = static::uniqueNo(strtolower("{$table}_{$column}"), $length);
            if (! $table::onWriteConnection()->where($column, $sn)->exists()) {
                return $sn;
            }
        } while (true);
    }

    /**
     * 取得指定長度的流水號
     *
     * @param string $type
     * @param int $length
     * @return string
     */
    public static function uniqueNo(string $type, int $length): string
    {
        $keyExpireTime = 70;
        $currentTime = date('ymdHis', time());

        /*
         * 現在秒數的 incr
         * 年月日時分秒 + 6 位數
         */
        $redisKey = "cache:sn:{$type}:{$currentTime}";
        $incrNo = Redis::incr($redisKey);
        Redis::expire($redisKey, $keyExpireTime); // 設定過期時間
        $uniqueNo = $currentTime . sprintf('%06d', $incrNo);
        $uniqueNo = base_convert($uniqueNo, 10, 36); // 36 進制
        $uniqueNo = substr($uniqueNo, $length * -1);
        if (strlen($uniqueNo) < $length)  {
            $uniqueNo .= strtolower(str_random($length - strlen($uniqueNo)));
        }
        $uniqueNo = substr($uniqueNo, strlen($incrNo), $length) . $incrNo;
        return $uniqueNo;
    }
}

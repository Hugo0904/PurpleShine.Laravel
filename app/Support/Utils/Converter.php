<?php
/**
 * Created by PhpStorm.
 * User: liangyuehchen
 * Date: 2018/11/5
 * Time: 下午1:39
 */

namespace App\Support\Utils;

use App\Handlers\ValueHandler;

class Converter
{
    /**
     * 捨去小數點後幾位
     *
     * @param int $value
     * @param int $decimals
     * @return float|int
     */
    public static function roundDown($value, int $decimals = 2)
    {
        $pow = pow(10, $decimals);

        // 先將浮點數轉成字串, 避免floor()時浮點數誤差
        $absValue = (string)(abs($value * $pow));

        $resultValue = floor($absValue) / $pow;
        if ($value < 0) {
            $resultValue *= -1;
        }

        return $resultValue;
    }

    /**
     * 計算百分比
     *
     * @param $value
     * @param $percent
     * @return ValueHandler
     */
    public static function percent($value, $percent)
    {
        $result = $value * ($percent / 100);

        return new ValueHandler($result);
    }
}
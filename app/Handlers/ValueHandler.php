<?php
/**
 * Created by PhpStorm.
 * User: liangyuehchen
 * Date: 2019/1/29
 * Time: 下午12:47
 */

namespace App\Handlers;

use App\Support\Utils\Converter;

class ValueHandler
{

    /**
     * @var string|float|int
     */
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * 無條件捨去
     *
     * @param int $decimals
     * @return float|int
     */
    public function floor(int $decimals = 2)
    {
        return Converter::roundDown($this->value, $decimals);
    }

    /**
     * 百分比
     *
     * @param null $divisor
     * @return string
     */
    public function percent($divisor = null)
    {
        if (empty($divisor)) {
            $value = 0;
        } else {
            $value = $this->value / $divisor;
        }

        return number_format($value * 100, 2);
    }

    /**
     * @return float|int|string
     */
    public function get()
    {
        return $this->value;
    }

    public function __toString()
    {
        return (string)$this->value;
    }
}
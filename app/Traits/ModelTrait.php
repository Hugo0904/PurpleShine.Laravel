<?php
/**
 * Created by PhpStorm.
 * User: liangyuehchen
 * Date: 2018/11/15
 * Time: 下午5:23
 */

namespace App\Traits;

use Carbon\Carbon;

trait ModelTrait
{
    /**
     * 載入Relation
     * 方法必須是 get 開頭 加上 載入Relation名字
     *
     * ex: 我要載入 platforms 就是 getPlatforms
     *
     * @param $relation
     * @return $this
     */
    public function loadRelation($relation)
    {
        if (! isset($this->{$relation})) {
            $function = 'get' . ucfirst($relation);
            $this->setRelation($relation, $this->{$function}());
        }
        return $this;
    }

    /**
     * 該數組是否年滿
     *
     * @param int $year
     * @param string $column
     * @return bool
     */
    public function isFullYear(int $year, $column = 'created_at'): bool
    {
        return Carbon::parse($this->{$column})->diffInYears() >= $year;
    }

    /**
     * 該數組是否月滿
     *
     * @param int $month
     * @param string $column
     * @return bool
     */
    public function isFullMonth(int $month, $column = 'created_at'): bool
    {
        return Carbon::parse($this->{$column})->diffInMonths() >= $month;
    }
}
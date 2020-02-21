<?php
/**
 * Created by PhpStorm.
 * User: liangyuehchen
 * Date: 2019/1/26
 * Time: 上午12:29
 */

namespace App\Support\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;

use Carbon\Carbon;

/**
 * Trait QueryTrait
 *
 * Query的集合
 * 1. 可以配合Criteria使用
 *
 * @package App\Support\Traits
 */
trait QueryTrait
{
    /**
     * 檢測對象是否為查詢Builder
     *
     * @param $instance
     * @return bool
     */
    public static function isQueryBuilder($instance): bool
    {
        return $instance instanceof Builder || $instance instanceof QueryBuilder;
    }

    /**
     * 檢測對象是否為查詢庫
     *
     * @param $instance
     * @return bool
     */
    public static function isEloquent($instance): bool
    {
        return $instance instanceof Model || static::isQueryBuilder($instance);
    }

    protected $columnMapping = [];

    /**
     * 取得資料表名稱
     *
     * @param $query
     * @return string
     * @throws \ReflectionException
     */
    protected function getTableName($query): string
    {
        if ($query instanceof Model) {
            return $query->getTable();
        } elseif ($query instanceof Builder) {
            return $query->getModel()->getTable();
        } elseif ($query instanceof QueryBuilder) {
            return $query->from;
        } else {
            throw new \ReflectionException('Must be Model or Builder');
        }
    }

    /**
     * 若傳進來是Model則產生Query
     *
     * @param $query
     * @return Builder
     * @throws \ReflectionException
     */
    protected function convertBuilder($query)
    {
        if ($query instanceof Model) {
            return $query->newQuery();
        } elseif ($query instanceof Builder || $query instanceof QueryBuilder) {
            return $query;
        } else {
            throw new \ReflectionException('Must be Model or Builder');
        }
    }

    /**
     * 選擇時間Range
     *
     * @param $query
     * @param $beginAt
     * @param $endAt
     * @param string $timeColumn
     * @param $boolean
     * @return Builder
     * @throws \ReflectionException
     */
    private function selectTimeRange($query, $beginAt, $endAt, $timeColumn = 'created_at', $boolean = 'and')
    {
        $query = $this->convertBuilder($query);
        if (empty($beginAt)) {
            $beginAt = Carbon::today()->startOfDay()->toDateTimeString();
        }

        if (empty($endAt)) {
            $endAt = Carbon::today()->endOfDay()->toDateTimeString();
        }

        return $this->executeQuery($query, 'whereBetween', $boolean, $timeColumn, [$beginAt, $endAt]);
    }

    /**
     * 選擇時間Range
     *
     * @param $query
     * @param $beginAt
     * @param $endAt
     * @param string $timeColumn
     * @return Builder
     * @throws \ReflectionException
     */
    private function orSelectTimeRange($query, $beginAt, $endAt, $timeColumn = 'created_at')
    {
        return $this->selectTimeRange($query, $beginAt, $endAt, $timeColumn, 'or');
    }

    /**
     * 加入Partition條件
     * 目前預設是用 p_month 如果名稱不一樣請實作 $columns mapping
     *
     * @param $query
     * @param $beginAt
     * @param $endAt
     * @param $boolean
     * @return Builder
     * @throws \ReflectionException
     * @throws \Exception
     */
    private function selectPartition($query, $beginAt, $endAt, $boolean = 'and')
    {
        $query = $this->convertBuilder($query);

        if (is_null($beginAt)) {
            // 正式上線日子
            $beginAt = Carbon::parse('2018-05-01 00:00:00')->firstOfMonth();
        } elseif (is_string($beginAt)) {
            $beginAt = Carbon::parse($beginAt)->firstOfMonth();
        }

        if (is_string($endAt) || is_null($endAt)) {
            $endAt = Carbon::parse($endAt)->endOfMonth();
        }

        // 取得Y-m-d 之間的月份差異
        $period = collect(new \DatePeriod(
            new \DateTime($beginAt->toDateString()),
            new \DateInterval('P1M'),
            new \DateTime($endAt->toDateString())
        ));

        if ($period->isEmpty()) {
            $period->push(new \DateTime($beginAt->toDateString()));
        }

        // ex. 2018-06-01 ~ 2018-08-31 => [1806, 1807, 1808]
        //     2018-07-01 ~ 2018-08-01 => [1807, 1808]
        //     2018-07-01 ~ 2018-07-31 => [1807]
        //     2018-07-01 ~ 2018-07-01 => [1807]
        $range = $period->map(function ($item, $key) {
            return (int)$item->format('ym');
        })->unique()->values();

        $column = $this->getMappingColumn($query, 'p_ym');

        $this->executeQuery($query, 'whereIn', $boolean, $column, $range->all());

        return $query;
    }

    /**
     * 加入Partition條件
     * 目前預設是用 p_month 如果名稱不一樣請實作 $columns mapping
     *
     * @param $query
     * @param $beginAt
     * @param $endAt
     * @return Builder
     * @throws \ReflectionException
     * @throws \Exception
     */
    private function orSelectPartition($query, $beginAt, $endAt)
    {
        return $this->selectPartition($query, $beginAt, $endAt);
    }

    /**
     * 選擇時間區間
     *
     * @param $query
     * @param $interval
     * @param string $timeColumn
     * @param string $day
     * @param $boolean
     * @return Builder
     * @throws \ReflectionException
     */
    private function selectInterval($query, $interval, $timeColumn = 'created_at', $day = 'today', $boolean = 'and')
    {
        $query = $this->convertBuilder($query);

        switch ($interval) {
            case 'day':
                $beginAt = Carbon::parse($day)->startOfDay();
                $endAt = Carbon::parse($day)->endOfDay();
                break;
            case 'week':
                $beginAt = Carbon::parse($day)->startOfWeek();
                $endAt = Carbon::parse($day)->endOfWeek();
                break;
            case 'month':
                $beginAt = Carbon::parse($day)->startOfMonth();
                $endAt = Carbon::parse($day)->endOfMonth();
                break;
            case 'year':
                $beginAt = Carbon::parse($day)->startOfYear();
                $endAt = Carbon::parse($day)->endOfYear();
                break;
            default:
                break;
        }

        if (isset($beginAt) && isset($endAt)) {
            $this->executeQuery($query, 'whereBetween', $boolean, $timeColumn, [$beginAt, $endAt]);
        }

        return $query;
    }

    /**
     * 選擇時間區間
     *
     * @param $query
     * @param $interval
     * @param string $timeColumn
     * @param string $day
     * @return Builder
     * @throws \ReflectionException
     */
    private function orSelectInterval($query, $interval, $timeColumn = 'created_at', $day = 'today')
    {
        return $this->selectInterval($query, $interval, $timeColumn, $day, 'or');
    }

    /**
     * 選擇多個條件
     *
     * @param $query
     * @param array $where
     * @param $boolean
     * @return Builder
     * @throws \ReflectionException
     */
    private function selectConditions($query, array $where, $boolean = 'and')
    {
        $query = $this->convertBuilder($query);
        $operators = ['>', '>=', '=', '<', '<=', '!=', 'like'];
        $count = 0;
        foreach ($where as $field => $value) {
            if ($count++ > 0) $boolean = 'and';

            if (is_array($value) && count($value) == 3 && in_array($value[1], $operators)) {
                list($field, $condition, $val) = $value;
                if (is_array($val) || $val instanceof Collection) {
                    $this->executeQuery($query, 'whereNotIn', $boolean, $field, $val);
                } else {
                    $this->executeQuery($query, 'where', $boolean, $field, $condition, $val);
                }
            } else if (is_array($value) || $value instanceof Collection) {
                $this->executeQuery($query, 'whereIn', $boolean, $field, $value);
            } else {
                $this->executeQuery($query, 'where', $boolean, $field, $value);
            }
        }

        return $query;
    }

    /**
     * 選擇多個條件
     *
     * @param $query
     * @param array $where
     * @return Builder
     * @throws \ReflectionException
     */
    private function orSelectConditions($query, array $where)
    {
        return $this->selectConditions($query, $where, 'or');
    }

    private function executeQuery($query, $method, $boolean, ...$value)
    {
        if (strtolower($boolean) === 'or') {
            return $query->{('or' . ucfirst($method))}(...$value);
        } else {
            return $query->{$method}(...$value);
        }
    }

    /**
     * 取得mapping後的column
     *
     * @param $table
     * @param $column
     * @return array|mixed
     * @throws \ReflectionException
     */
    protected function getMappingColumn($table, $column)
    {
        if (! is_string($table)) {
            $table = $this->getTableName($table);
        }

        if (isset($this->columnMapping[$table]) && is_array($this->columnMapping[$table])) {
            $columnMapping = $this->columnMapping[$table];
        } else {
            $columnMapping = $this->columnMapping ?? [];
        }

        if (is_array($column)) {
            foreach ($column as $key => $value) {
                if (isset($columnMapping[$value])) {
                    $column[$key] = $columnMapping[$value];
                }
            }
            return $column;
        }

        return $columnMapping[$column] ?? $column;
    }

    private function parseField($object, $field)
    {
        if ($object instanceof Collection || is_array($object)) {
            return collect($object)->map(function ($obj) use ($field) {
                return $this->parseValue($obj, $field);
            })->toArray();
        }
        return $this->parseValue($object, $field);
    }

    private function parseValue($object, $field)
    {
        if (is_object($object)) {
            return $object->{$field};
        } elseif (is_array($object)) {
            return $object[$field];
        }

        return $object;
    }
}

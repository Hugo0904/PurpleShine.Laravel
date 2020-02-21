<?php
/**
 * Created by PhpStorm.
 * User: liangyuehchen
 * Date: 2019/2/27
 * Time: 3:36 PM
 */

namespace App\Handlers;

use ArrayAccess;
use IteratorAggregate;
use Countable;
use ArrayIterator;
use JsonSerializable;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;

use Carbon\Carbon;

/**
 * Class Conditions
 *
 * @package App\Handlers
 *
 * @property array time_range
 */
class Conditions implements Arrayable, ArrayAccess, IteratorAggregate, Countable, JsonSerializable, Jsonable
{

    /**
     * 查詢條件
     *
     * @var array
     */
    protected $conditions = [];

    /**
     * @var Request
     */
    protected $request;

    /**
     * @return $this
     */
    public static function builder()
    {
        return app(static::class);
    }

    /**
     * 設置Request
     *
     * @param Request $request
     * @return $this
     */
    public function bindRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    protected function getKeyValuePair($key, $value = null)
    {
        if (isset($this->request)) {
            if (is_numeric($key) && is_string($value) && $this->request->has($value)) {
                $key = $value;
                $value = $this->request->input($value);
            } elseif (is_null($value)) {
                $key = $this->request->input($key);
            }
        }

        return [$key, $value];
    }

    /**
     * 取得條件
     *
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getCondition($key, $default = null)
    {
        return $this->conditions[$key] ?? (isset($this->request) ? $this->request->input($key, $default) : $default);
    }

    /**
     * 加入條件
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function addCondition($key, $value)
    {
        list($key, $value) = $this->getKeyValuePair($key, $value);
        $this->conditions[$key] = $value;

        return $this;
    }

    /**
     * 從Request加入條件
     *
     * @param $key
     * @param null $default
     * @return $this
     */
    public function addConditionFromRequest($key, $default = null)
    {
        $this->addCondition($key, $this->request->input($key, $default));

        return $this;
    }

    /**
     * 加入多個條件
     *
     * @param $conditions
     * @return $this
     */
    public function addConditions($conditions)
    {
        foreach ($conditions as $key => $value) {
            $this->addCondition($key, $value);
        }

        return $this;
    }

    /**
     * 從request加入多個條件
     *
     * @param $conditions
     * @return $this
     */
    public function addConditionsFromRequest($conditions)
    {
        foreach ($conditions as $key => $value) {
            if (is_numeric($key)) {
                $this->addConditionFromRequest($value);
            } else {
                $this->addConditionFromRequest($key, $value);
            }
        }

        return $this;
    }

    /**
     * 移除條件
     *
     * @param $key
     * @return $this
     */
    public function removeCondition($key)
    {
        unset($this->conditions[$key]);
        return $this;
    }

    /**
     * 移除多個條件
     *
     * @param $keys
     * @return $this
     */
    public function removeConditions(...$keys)
    {
        foreach ($keys as $key) {
            $this->removeCondition($key);
        }

        return $this;
    }

    /**
     * 建立條件
     *
     * @return array
     */
    public function build()
    {
        return $this->toArray();
    }

    /**
     * 重置
     *
     * @return $this
     */
    public function reset()
    {
        $this->conditions = [];

        return $this;
    }

    /**
     * 加入日期條件
     *
     * @param null|string $period
     * @param string|Carbon $beginAt
     * @param string|Carbon $endAt
     * @param string $timeColumn
     * @return $this
     */
    public function addDateTimeCondition($period, $beginAt, $endAt, $timeColumn = 'created_at')
    {
        list($period) = $this->getKeyValuePair($period);
        $today = Carbon::today();

        switch ($period) {
            case 'daily':
                $beginAt = $today->startOfDay()->toDateTimeString();
                $endAt = $today->endOfDay()->toDateTimeString();
                break;

            case 'weekly':
                $beginAt = $today->startOfWeek()->toDateTimeString();
                $endAt = $today->endOfWeek()->toDateTimeString();
                break;

            case 'monthly':
                $beginAt = $today->startOfMonth()->toDateTimeString();
                $endAt = $today->endOfMonth()->toDateTimeString();
                break;

            case 'specifically':
                $beginAt = $beginAt instanceof Carbon ? $beginAt->toDateTimeString() : $beginAt;
                $endAt = $endAt instanceof Carbon ? $endAt->toDateTimeString() : $endAt;
                break;

            default:
                list($beginAt) = $this->getKeyValuePair($beginAt);
                list($endAt) = $this->getKeyValuePair($endAt);
                break;
        }

        $this->addCondition('time_range', [$timeColumn, $beginAt, $endAt]);

        return $this;
    }

    public function toArray() {
        return $this->conditions;
    }

    public function offsetExists($name)
    {
        return isset($this->{$name});
    }

    public function offsetGet($name)
    {
        return $this->{$name};
    }

    public function offsetSet($name, $value)
    {
        $this->{$name} = $value;

        return $value;
    }

    public function offsetUnset($name)
    {
        unset($this->{$name});
    }

    public function getIterator()
    {
        return new ArrayIterator($this->conditions);
    }

    public function count()
    {
        return count($this->conditions);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->getCondition($name);
    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        $this->addCondition($name, $value);

        return $value;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __unset($name)
    {
        unset($this->conditions[$name]);
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->conditions[$name]);
    }

    public function __debugInfo()
    {
        return json_encode($this);
    }

    /**
     * 將條件輸出http build query
     *
     * @return string
     */
    public function __toString()
    {
        return http_build_query($this->conditions);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: liangyuehchen
 * Date: 2019/1/23
 * Time: 下午6:19
 */

namespace App\Querents;

use Symfony\Component\Debug\Exception\FatalThrowableError;

use ArrayAccess;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\{Model, Builder};
use Prettus\Repository\Eloquent\BaseRepository;

use App\Support\Traits\QueryTrait;

/**
 * Class Querent
 *
 * 搜尋器
 *
 * 所有 搜尋的條件 或 取資料的方式 的集合
 * 可同時使用Repository or Eloquent 做完查詢的庫
 *
 * @package App\Querents
 * @method static $this from($model)
 * @method static $this setModel($model)
 * @method static $this setQuery($query)
 * @method static $this setSubQuery($subQuery)
 * @method static $this fromSubQuery($subQuery)
 * @method $this selectTimeRange($beginAt, $endAt, $timeColumn = 'created_at')
 * @method $this orSelectTimeRange($beginAt, $endAt, $timeColumn = 'created_at')
 * @method $this selectPartition($beginAt, $endAt)
 * @method $this orSelectPartition($beginAt, $endAt)
 * @method $this selectInterval($interval, $timeColumn = 'created_at', $day = 'today')
 * @method $this orSelectInterval($interval, $timeColumn = 'created_at', $day = 'today')
 * @method $this selectConditions(array $where)
 * @method $this orSelectConditions(array $where)
 *
 * @property array time_range 時間區間
 */
class Querent implements ArrayAccess
{
    use QueryTrait;

    /**
     * 取得搜尋器
     *
     * @param null|string $name
     * @param array $parameters
     * @param null|string|Querent $defaultQuerent 預設搜尋器
     * @return static
     * @throws \ReflectionException
     */
    public static function build($name = null, $parameters = [], $defaultQuerent = null): Querent
    {
        try {
            if (empty($name) || static::isEloquent($name)) {
                $instance = resolve(static::class, $parameters)->table($name);
            } elseif (static::isEloquent($parameters)) {
                $instance = resolve($name)->table($parameters);
            } else {
                $instance = resolve($name, $parameters);
                if (static::isEloquent($defaultQuerent)) {
                    $instance->table($defaultQuerent);
                }
            }

            return static::isEloquent($instance) ? static::build($instance) : $instance;
        } catch (\ReflectionException $e) {

            if (is_null($defaultQuerent)) {
                throw $e;
            }

            if (is_string($defaultQuerent)) {
                return resolve($defaultQuerent, $parameters);
            } else {
                return $defaultQuerent;
            }
        }
    }

    /**
     * @var Model|BaseRepository
     */
    protected $model;

    /**
     * @var Builder
     */
    protected $query;

    /**
     * @var QueryBuilder
     */
    protected $coreQuery;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * 自動reset
     *
     * @var bool
     */
    protected $autoReset = true;

    /**
     * 自動query attributes內相關參數
     *
     * @var bool
     */
    protected $autoFillQuery = true;

    /**
     * 此次要跳過的自動關聯Query
     *
     * @var array
     */
    protected $skipRelation = [];

    /**
     * 自動同步分頁參數
     *
     * @var bool
     */
    protected $autoSyncPaginateParams = true;

    /**
     * 魔術方法呼叫這些方法後需要reset Querent
     *
     * @var array
     */
    protected $mustResetMethods = [
        'all',
        'get',
        'paginate',
        'simplePaginate',
        'find',
        'findMany',
        'findOrFail',
        'first',
        'firstOrFail',
        'findOrNew',
        'firstOrNew',
        'firstOrCreate',
        'updateOrCreate',
        'update',
        'increment',
        'decrement',
        'delete',
        'forceDelete',
        'paginateWhere',
        'count',
        'findWhere'
    ];

    /**
     * 重置搜尋器
     *
     * @param bool $force
     * @return $this
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \ReflectionException
     */
    public function reset(bool $force = false)
    {
        if ($this->model instanceof Model) {
            $model = $this->model;
        } elseif ($this->model instanceof BaseRepository) {
            if ($force) {
                $this->model->resetCriteria()->resetScope();
            }
            $this->model->resetModel();
            $model = $this->model->getModel();
        }

        if (isset($model)) {
            $this->query = $this->convertBuilder($model);
            $this->coreQuery = $this->query->getQuery();
        } else {
            $this->query = null;
            $this->coreQuery = null;
        }

        return $this;
    }

    /**
     * 設置模組
     *
     * @param $model
     * @return $this
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \ReflectionException
     */
    protected function table($model)
    {
        if (is_string($model)) {
            $model = resolve($model);
        }

        if (static::isEloquent($model)) {
            // use model
            if ($model instanceof Model && ! isset($this->model)) {
                $this->model = $model;
                $this->reset();
            }
            // use builder
            elseif ($model instanceof Builder && $this->query !== $model) {
                $this->query = $model;
            }
            // use sub builder
            elseif ($model instanceof QueryBuilder && $this->coreQuery !== $model) {
                $this->coreQuery = $model;
            }
        }

        return $this;
    }

    /**
     * 取得模組
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * 取得內部查詢器
     *
     * @return Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * 取得內部查詢器
     *
     * @return QueryBuilder
     */
    public function getCoreQuery()
    {
        return $this->coreQuery;
    }

    /**
     * 使用 Master 查詢
     *
     * @return $this
     */
    public function useWriteConnection()
    {
        $this->query = $this->query->useWritePdo();

        return $this;
    }

    /**
     * 自訂查詢
     *
     * @param \Closure $scope
     * @return $this
     */
    public function scopeQuery(\Closure $scope)
    {
        /**
         *  $querent->scopeQuery(function ($query) use ($querent, $user) {
         *     return $querent->selectUser($query, $user);
         *  });
         */

        $scope($this->query, $this);

        return $this;
    }

    /**
     * 加入子查詢
     *
     * @param QueryBuilder $subQuery
     * @param \Closure $function
     * @return $this
     */
    public function subQuery(QueryBuilder $subQuery, \Closure $function)
    {
        $subQuerent = new static();
        $subQuerent->setModel($subQuery);

        $function($subQuerent);

        return $this;
    }

    /**
     * 判斷式條件
     *
     * @param $value
     * @param \Closure $callback
     * @param \Closure $default
     * @return $this
     */
    public function when($value, $callback, $default = null)
    {
        if ($value) {
            if (! is_null($callback)) {
                $callback($this);
            }
        } elseif (! is_null($default)) {
            $default($this);
        }

        return $this;
    }

    /**
     * 取得mapping中value
     *
     * @param $key
     * @param $mapping
     * @param bool $autoCheckMethod
     * @return mixed
     */
    protected function getMappingValue($key, $mapping, $autoCheckMethod = true)
    {
        $value = $mapping[$key] ?? $key;

        if ($autoCheckMethod && is_string($value) && method_exists($this, $value)) {
            return $this->{$value}();
        }

        return $value;
    }

    /**
     * 設置參考條件
     *
     * @param $field
     * @param $value
     * @return $this
     */
    public function setAttribute($field, $value)
    {
        $this->attributes[$field] = $value;

        return $this;
    }

    /**
     * 設置多組參考條件
     *
     * @param $pairs
     * @return $this
     */
    public function setAttributes($pairs)
    {
        foreach ($pairs as $key => $value) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    /**
     * 取得設置在Querent內的參考條件
     *
     * @param $field
     * @param null $default
     * @return null
     */
    public function getAttribute($field, $default = null)
    {
        return $this->attributes[$field] ?? $default;
    }

    /**
     * 判斷設置在Querent內的參考條件是否該key
     *
     * @param $field
     * @return boolean
     */
    public function hasAttribute($field)
    {
        return isset($this->attributes[$field]);
    }

    /**
     * 清除 Attributes
     *
     * @return $this
     */
    public function resetAttributes()
    {
        $this->attributes = [];

        return $this;
    }

    /**
     * 跳過這次的自動填充Query
     *
     * @param array $skipRelation 跳過的關聯 預設全部
     * @return $this
     */
    public function skipFillQuery($skipRelation = [])
    {
        if (! is_array($skipRelation)) {
            $skipRelation = [$skipRelation];
        }

        $this->autoFillQuery = count($skipRelation) !== 0;
        $this->skipRelation = $skipRelation;

        return $this;
    }

    /**
     * 自動化將 Attributes 內的部分欄位加入搜尋條件
     *
     * @return $this
     * @throws \ReflectionException
     */
    public function fillQuery()
    {
        // 填充日期區間
        if ($this->canFillQuery('time_range')) {
            [$timeColumn, $beginAt, $endAt] = $this->time_range;
            $timeColumn = $this->getMappingColumn($this->query, $timeColumn);
            $this->selectTimeRange($this->query, $beginAt, $endAt, $timeColumn);
        }

        return $this;
    }

    /**
     * 是否可以將 Attributes 內的指定欄位加入搜尋條件
     *
     * @param string $relation
     * @return bool
     */
    protected function canFillQuery(string $relation)
    {
        return isset($this->{$relation}) && ! in_array($relation, $this->skipRelation, true);
    }

    /**
     * @param bool $autoSyncPaginateParams
     * @return $this
     */
    public function setAutoSyncPaginateParams(bool $autoSyncPaginateParams)
    {
        $this->autoSyncPaginateParams = $autoSyncPaginateParams;

        return $this;
    }

    /**
     * @param bool $autoReset
     * @return $this
     */
    public function setAutoReset(bool $autoReset)
    {
        $this->autoReset = $autoReset;

        return $this;
    }

    /**
     * core magic
     *
     * @param $method
     * @param $arguments
     * @return $this|Querent|mixed
     * @throws FatalThrowableError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \ReflectionException
     */
    public function __call($method, $arguments)
    {
        switch ($method) {
            case 'setModel':
                return $this->table(...$arguments);

            default:
                break;
        }

        if (method_exists($this, $method)) {
            if (isset($this->query)) {
                $this->{$method}($this->query, ...$arguments);
            } elseif (isset($this->coreQuery)) {
                $this->{$method}($this->coreQuery, ...$arguments);
            }
            return $this;
        }

        if (in_array($method, $this->mustResetMethods)) {
            // may be skip current
            if ($this->autoFillQuery) {
                $this->fillQuery();
            }
            $this->skipRelation = [];
            $this->autoFillQuery = true;
        }

        // call repository method
        if ($this->model instanceof BaseRepository && method_exists($this->model, $method)) {
            $this->model->setModel($this->query);
            $result = $this->model->{$method}(...$arguments);
            return $result instanceof BaseRepository ? $this : $this->disposeIfResult($method, $result);
        }

        // \Illuminate\Database\Query\Builder
        if (method_exists($this->query, $method) || (isset($this->query) && ! is_null($this->query->getMacro($method)))) {
            $result = $this->query->{$method}(...$arguments);
            return static::isQueryBuilder($result) ? $this : $this->disposeIfResult($method, $result);
        }

        // \Illuminate\Database\Query
        if (method_exists($this->coreQuery, $method) || ($userMacro = $this->coreQuery->hasMacro($method))) {
            if (! isset($this->query) || isset($userMacro) && $userMacro) {
                $result = $this->coreQuery->{$method}(...$arguments);
            } else {
                $result = $this->query->{$method}(...$arguments);
            }

            return static::isQueryBuilder($result) ? $this : $this->disposeIfResult($method, $result);
        }

        throw new FatalThrowableError(new \Exception('Call to undefined method ' . __class__ . '::' . $method . '()'));
    }

    /**
     * 確認是否查詢完畢, 並重置查詢器
     *
     * @param $method
     * @param $result
     * @return mixed
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \ReflectionException
     */
    private function disposeIfResult($method, $result)
    {
        if ($this->autoReset && in_array($method, $this->mustResetMethods)) {
            $this->reset();
        }

        if ($this->autoSyncPaginateParams && $result instanceof LengthAwarePaginator) {
            $result->appends(app('request')->query());
        }

        return $result;
    }

    /**
     * @param $method
     * @param $arguments
     * @return Querent|null|mixed
     * @throws FatalThrowableError
     * @throws \ReflectionException
     */
    public static function __callStatic($method, $arguments)
    {
        switch ($method) {
            case 'table':
            case 'from':
            case 'setModel':
            case 'setQuery':
            case 'setSubQuery':
            case 'fromSubQuery':
                return static::build(...$arguments);

            default:
                break;
        }

        throw new FatalThrowableError(new \Exception('Call to undefined method ' . __class__ . '::' . $method . '()'));
    }

    /**
     * ArrayAccess interface method
     *
     * @param mixed $name
     * @return bool
     */
    public function offsetExists($name)
    {
        return isset($this->{$name});
    }

    /**
     * ArrayAccess interface method
     *
     * @param mixed $name
     * @return mixed|null
     */
    public function offsetGet($name)
    {
        return $this->{$name};
    }

    /**
     * ArrayAccess interface method
     *
     * @param mixed $name
     * @param mixed $value
     * @return mixed
     */
    public function offsetSet($name, $value)
    {
        $this->{$name} = $value;

        return $value;
    }

    /**
     * ArrayAccess interface method
     *
     * @param mixed $name
     */
    public function offsetUnset($name)
    {
        unset($this->{$name});
    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;

        return $value;
    }

    /**
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __unset($name)
    {
        $value = $this->attributes[$name] ?? null;
        unset($this->attributes[$name]);
        return $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->attributes[$name]);
    }
}

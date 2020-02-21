<?php
/**
 * Created by PhpStorm.
 * User: liangyuehchen
 * Date: 2018/11/19
 * Time: 上午11:53
 */

namespace App\Handlers;

use App\Exceptions\AuthFailedException;

class Validator
{
    /**
     * @var array
     */
    private $authHandlers;

    private function __construct($authHandlers = [])
    {
        $this->authHandlers = $authHandlers;
    }

    /**
     * 建立驗證器
     *
     * @param array $authHandlers
     * @return Validator
     */
    public static function create($authHandlers = []): Validator
    {
        return new static($authHandlers);
    }

    /**
     * 開始驗證
     *
     * @param bool $needAllPassed true 則任何一個為true時返回成功結果
     * @return AuthResult 返回第一個失敗的結果
     */
    public function validate(bool $needAllPassed = true): AuthResult
    {
        $message = '';
        $code = 0;
        $passCount = 0;

        /** @var AuthHandler $handler */
        foreach ($this->authHandlers as $auth) {

            list($handler, $message, $code) = $auth;
            $result = $handler->check();
            if ($result) {
                $passCount++;
                if (! $needAllPassed) {
                    break;
                }
            } else {
                if ($needAllPassed) {
                    break;
                }
            }
        }

        if ($needAllPassed) {
            $pass = ($passCount === count($this->authHandlers));
        } else {
            $pass = ($passCount > 0);
        }

        return new AuthResult($pass, $message, $code);
    }

    /**
     * 開始驗證
     *
     * @param bool $needAllPassed
     * @return AuthResult
     */
    public function validateOrFail(bool $needAllPassed = true): AuthResult
    {
        $result = $this->validate($needAllPassed);
        if ($result->isFailed()) {
            throw new AuthFailedException($result->getMessage(), $result->getErrorCode());
        }

        return $result;
    }

    /**
     * 加入驗證
     *
     * @param AuthHandler $handler
     * @param string $message
     * @param int $code
     * @return Validator
     */
    public function add(AuthHandler $handler, $message = '', $code = 0): Validator
    {
        $this->authHandlers[] = [$handler, $message, $code];
        return $this;
    }

    /**
     * 加入驗證至最前面
     *
     * @param AuthHandler $handler
     * @param string $message
     * @param int $code
     * @return Validator
     */
    public function addFirst(AuthHandler $handler, $message = '', $code = 0): Validator
    {
        array_unshift($this->authHandlers, [$handler, $message, $code]);
        return $this;
    }

    /**
     * 若條件滿足則加入驗證
     *
     * @param bool $add
     * @param AuthHandler $handler
     * @param string $message
     * @param int $code
     * @return Validator
     */
    public function addWhen(bool $add, AuthHandler $handler, $message = '', $code = 0): Validator
    {
        if ($add) {
            $this->add($handler, $message, $code);
        }

        return $this;
    }

    /**
     * 重置驗證器
     *
     * @return $this
     */
    public function reset()
    {
        $this->authHandlers = [];
        return $this;
    }

    /**
     * 複製驗證器
     *
     * @return Validator
     */
    public function copy(): Validator
    {
        return Validator::create($this->authHandlers);
    }
}
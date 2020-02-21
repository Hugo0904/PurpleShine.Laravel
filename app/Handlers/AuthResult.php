<?php
/**
 * Created by PhpStorm.
 * User: liangyuehchen
 * Date: 2018/11/19
 * Time: 上午11:41
 */

namespace App\Handlers;

/**
 * Class AuthResult
 *
 * 驗證結果
 *
 * @package App\Handlers
 */
class AuthResult
{
    /**
     * 驗證是否通過
     *
     * @var bool
     */
    private $isPassed;

    /**
     * 錯誤代碼
     *
     * @var int
     */
    private $errorCode;

    /**
     * 錯誤原因
     *
     * @var string
     */
    private $message;

    /**
     * AuthResult constructor.
     *
     * @param bool $isPassed
     * @param string $message
     * @param int $errorCode
     */
    public function __construct(bool $isPassed, string $message = '', int $errorCode = 0)
    {
        $this->isPassed = $isPassed;
        $this->errorCode = $errorCode;
        $this->message = $message;
    }

    /**
     * @return bool
     */
    public function isPassed(): bool
    {
        return $this->isPassed;
    }

    /**
     * @return bool
     */
    public function isFailed(): bool
    {
        return ! $this->isPassed();
    }

    /**
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
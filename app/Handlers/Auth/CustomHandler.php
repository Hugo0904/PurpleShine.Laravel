<?php
/**
 * Created by PhpStorm.
 * User: liangyuehchen
 * Date: 2018/11/19
 * Time: 下午12:18
 */

namespace App\Handlers\Auth;

use App\Handlers\AuthHandler;
use App\Handlers\AuthResult;

/**
 * Class AuthCustomHandler
 *
 * 客製化驗證
 *
 * @package App\Handlers\Auth
 */
class CustomHandler implements AuthHandler
{
    /**
     * @var \Closure
     */
    private $closure;

    /**
     * CustomAuthHandler constructor.
     *
     * @param \Closure $closure [0]是否通過 [1]ErrorCode [2]錯誤訊息
     */
    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function check(): bool
    {
        return ($this->closure)();
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: liangyuehchen
 * Date: 2018/11/19
 * Time: 上午11:39
 */

namespace App\Handlers;


/**
 * Interface CheckHandler
 *
 * 確認條件是否滿足
 *
 * @package App\Handlers
 */
interface AuthHandler
{
    /**
     * 執行條件檢查
     *
     * @return bool
     */
    public function check(): bool ;
}
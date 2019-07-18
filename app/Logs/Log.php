<?php
/**
 * Created by PhpStorm.
 * User: yueh
 * Date: 2018/7/3
 * Time: 下午4:51
 */
namespace App\Logs;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Log
{
    public static function createLogger($logFileName, $path = '/logs'): Logger
    {
        $filename = storage_path() . $path . '/' . $logFileName . '.log';
        $handler = new RotatingFileHandler($filename);
        $handler->setFormatter(new LineFormatter(null, null, true, true));
        $logger = new Logger(env('APP_ENV'));
        $logger->pushHandler($handler);
        return $logger;
    }
}
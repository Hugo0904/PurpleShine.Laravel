<?php

use Jenssegers\Agent\Agent;

if (! function_exists('print_exception')) {
    /**
     * 輸出例外
     *
     * @param Throwable $e
     * @param bool $trace
     * @return string
     */
    function print_exception(Throwable $e, bool $trace = false)
    {
        $url = \Request::url();
        $method = \Request::method();
        $message = 'Time: ' . \Carbon\Carbon::now()->toDateTimeString() . PHP_EOL . "URL: {$method}@{$url}" . PHP_EOL . 'Message: ';

        if (! $trace) {
            return $message . $e->getMessage();
        }

        $previousText = '';
        if ($previous = $e->getPrevious()) {
            do {
                $previousText .= sprintf(', %s(code: %s): %s at %s:%s', get_class($previous), $previous->getCode(), $previous->getMessage(), $previous->getFile(), $previous->getLine());
            } while ($previous = $previous->getPrevious());
        }

        $str = sprintf('[object] (%s(code: %s): %s at %s:%s%s)', get_class($e), $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), $previousText);

        return $message . $str;
    }
}

if (! function_exists('pkcs5_pad')) {
    /**
     * @param string $text
     * @param int $blockSize
     * @return string
     */
    function pkcs5_pad(string $text, int $blockSize): string
    {
        $pad = $blockSize - (strlen($text) % $blockSize);

        return $text . str_repeat(chr($pad), $pad);
    }
}

if (! function_exists('pkcs5_unpad')) {
    /**
     * @param string $text
     * @return string
     */
    function pkcs5_unpad(string $text): string
    {
        $pad = ord($text{strlen($text) - 1});

        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }

        return substr($text, 0, -1 * $pad);
    }
}

if (! function_exists('hashcode64')) {
    /**
     * @param $str
     * @return int
     */
    function hashcode64($str)
    {
        $str = (string)$str;
        $hash = 0;
        $len = strlen($str);
        if ($len == 0)
            return $hash;

        for ($i = 0; $i < $len; $i++) {
            $h = $hash << 5;
            $h -= $hash;
            $h += ord($str[$i]);
            $hash = $h;
            $hash &= 0xFFFFFFFF;
        }
        return $hash;
    }
}

if (! function_exists('hashcode32')) {
    /**
     * @param $s
     * @return int
     */
    function hashcode32( $s )
    {
        $h = 0;
        $len = strlen($s);
        for($i = 0; $i < $len; $i++)
        {
            $h = overflow32(31 * $h + ord($s[$i]));
        }

        return $h;
    }
}

if (! function_exists('overflow32')) {
    /**
     * @param $v
     * @return int
     */
    function overflow32($v)
    {
        $v = $v % 4294967296;
        if ($v > 2147483647) return $v - 4294967296;
        elseif ($v < -2147483648) return $v + 4294967296;
        else return $v;
    }
}

if (! function_exists('root')) {
    /**
     *
     * @return string
     */
    function root(): string
    {
        return Request::root();
    }
}

if (! function_exists('exception_detail')) {
    /**
     * 取得詳細錯誤資訊
     *
     * @param Throwable $e
     * @return string
     */
    function exception_detail(\Throwable $e): string
    {
        $previousText = '';
        if ($previous = $e->getPrevious()) {
            do {
                $previousText .= sprintf(', %s(code: %s): %s at %s:%s', get_class($previous), $previous->getCode(), $previous->getMessage(), $previous->getFile(), $previous->getLine());
            } while ($previous = $previous->getPrevious());
        }
        return sprintf('[object] (%s(code: %s): %s at %s:%s%s)', get_class($e), $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), $previousText);
    }
}

if (! function_exists('join_value')) {
    /**
     * @param array $strings
     * @param int $offset
     * @param string $delimiter
     * @return string
     */
    function join_value(array $strings, int $offset = 0, string $delimiter = ''): string
    {
        return collect($strings)->splice($offset)->join($delimiter);
    }
}


if (! function_exists('first_class_world')) {
    /**
     * @param $class
     * @return string
     */
    function first_class_world($class): string
    {
        if (! is_string($class)) {
            $class = class_basename($class);
        }
        return explode('_', \Illuminate\Support\Str::snake($class))[0];
    }
}

if (! function_exists('is_cross_day')) {
    /**
     * 判斷是否跨日
     *
     * @param int $offset 負數則往前推算
     * @param null $tz
     * @return bool
     */
    function is_cross_day(int $offset = 0, $tz = null): bool
    {
        $offsetTime = \Carbon\Carbon::now($tz)->addSeconds($offset);

        return ($offset >= 0 ? $offsetTime->isTomorrow() : $offsetTime->isYesterday());
    }
}

if (! function_exists('is_cross_hour')) {
    /**
     * 判斷是否某時
     *
     * @param int $crossHour
     * @param int $offset 負數則往前推算
     * @param bool $ignoreCrossDay
     * @param null $tz
     * @return bool
     */
    function is_cross_hour(int $crossHour, int $offset = 0, bool $ignoreCrossDay = true, $tz = null): bool
    {
        $offsetTime = \Carbon\Carbon::now($tz)->addSeconds($offset);

        if ($ignoreCrossDay) {
            return $offsetTime->hour >= $crossHour;
        }

        return $offsetTime->gte(\Carbon\Carbon::now($tz)->setTime(12, 0));
    }
}

if (! function_exists('decimals_amount')) {
    /**
     * 將金額精確到小數點
     *
     * @param int $amount
     * @param int $decimals
     * @return string
     */
    function decimals_amount($amount = 0, int $decimals = 2): string
    {
        return number_format($amount, $decimals, ".", ",");
    }
}

if (! function_exists('parse_bool')) {
    /**
     * @param $value
     * @return bool
     */
    function parse_bool($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}

if (! function_exists('parse_int')) {
    /**
     * @param $value
     * @return int
     */
    function parse_int($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT);
    }
}

if (! function_exists('parse_float')) {
    /**
     * @param $value
     * @return float
     */
    function parse_float($value)
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT);
    }
}

if (! function_exists('floor_dec')) {

    /**
     * @param $value
     * @param $precision
     * @return float|int
     */
    function floor_dec($value, $precision = 2)
    {
        $tempMagnification = pow(10, $precision);
        $value = $value * $tempMagnification;
        return floor((string)$value) / $tempMagnification;
    }
}

if (! function_exists('device_info')) {

    /**
     * @return array
     */
    function device_info()
    {
        $agent = new Agent();
        $browser = $agent->browser();
        $platform = $agent->platform();

        return [
            'type' => $agent->device(),
            'platform' => [
                'name' => $platform,
                'version' => $agent->version($platform)
            ],
            'browser' => [
                'name' => $browser,
                'version' => $agent->version($browser)
            ]
        ];
    }
}

if (! function_exists('is_json')) {

    /**
     * @param $string
     * @return bool
     */
    function is_json($string){
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE);
    }
}

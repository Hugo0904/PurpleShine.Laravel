<?php
/**
 * Created by PhpStorm.
 * User: yueh
 * Date: 2018/6/9
 * Time: 下午3:14
 */

namespace App\Notifications;

use Illuminate\Notifications\Notifiable;

class SlackRoute
{
    use Notifiable;

    public function routeNotificationForSlack()
    {
        return env('SLACK_WEBHOOK');
    }
}
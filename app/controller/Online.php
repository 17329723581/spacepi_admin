<?php

declare(strict_types=1);

namespace app\controller;

use think\facade\Cache;
use app\Request;

class Online
{
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle($request, \Closure $next)
    {
        return $next($request);
    }

    //中间件执行到最后执行
    public function end()
    {
        //记录用户操作时间，根据此时间判断$exp_date内为在线

        $exp_date = 300; //单位 秒
        $exp_time = time() + $exp_date;
        if (!empty($this->request->uid)) {
            Cache::store('redis')->zadd('online_uids', $exp_time, $this->request->uid); //添加或更新当前uid
            Cache::store('redis')->zremrangebyscore('online_uids', 0, time());          //删除小于等于当前时间的记录
            $online_now_num = Cache::store('redis')->zcard('online_uids');              //当前在线人数
            if ($online_now_num > Cache::store('redis')->get('online_max_num')) {
                Cache::store('redis')->set('online_max_num', $online_now_num);          //记录最大在线人数值
            }
        }
    }
}
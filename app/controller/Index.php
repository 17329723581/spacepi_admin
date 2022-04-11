<?php
namespace app\controller;//命名:app\控制器
use app\BaseController;//调用继承控制器
use think\facade\View;

class Index extends BaseController//Iindex    前端控制器
{
    public function index()
    {
        $param = input();
        if(empty($param['key']))
        {
            ECHO '<link rel="icon" href="__static__/admin/login_icon.png">';
            return 'Internal server exception 500';
        }else{
            header("Location: admin");
        }
    }
}


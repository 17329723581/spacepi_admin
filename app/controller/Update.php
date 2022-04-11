<?php

namespace app\controller; //命名:app\控制器

use app\BaseController; //调用继承控制器

use think\facade\Request; //调用请求对象

class Update extends BaseController //Update    上传控制器
{
    public function head()//上传头像
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        $savename = \think\facade\Filesystem::disk('public')->putFile('topic', $file);
        return json(['code' => 200, 'data' => $savename, 'mag' => '获取成功']);
    }
}

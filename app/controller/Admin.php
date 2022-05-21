<?php
//命名:app\控制器
namespace app\controller;
//调用继承控制器
use app\BaseController;
//调用后端用户模型
use app\model\AdminUserModel;
//调用后端菜单模型
use app\model\AdminMenuModel;
//调用后端角色模型
use app\model\AdminRoleModel;
//调用后端系统配置模型
use app\model\AdminSystemModel;
//调用腾讯云短信配置模型
use app\model\TencentsmsConfigModel;
//调用腾讯云手机管理模型
use app\model\TencentsmsPhoneModel;

//调用API接口控制器
use app\controller\Api;
use TencentCloud\Aa\V20200224\AaClient;
//调用存储
use think\facade\Session;
//调用视图
use think\facade\View;
//调用请求对象
use think\facade\Request;
//调用验证码
use think\captcha\facade\Captcha;
use think\db\Where;

//Admin 后端控制器
class Admin extends BaseController
{
    public $data = [];
    public $code = 0;
    public $message = '';
    public $getSession = [];
    public $where = [];
    //初始化
    protected function initialize()
    {
        $param = input(); //获取全部传过来的参数
        //获取存储
        $this->getSession = Session::get('elementAdmin');
        //视图渲染输出变量
        View::assign('elementAdmin', $this->getSession);
        $getSystem = AdminSystemModel::select(); //获取系统配置
        View::assign('getSystem', $getSystem); //视图渲染输出变量
        $request = Request::instance(); //调用请求对象初始化
        $getController = $request->controller(); //获取当前控制器
        $getAction = $request->action(); //获取当前操作
        $rules = $getController . '/' . $getAction; //规则
        
        if ($this->getSession == null) //判断获取存储是否等于空
        {
            $url = ['Admin/login']; //访问URL的页面:必须填写否则无法访问
            if (!in_array($rules, $url)) //判断规则是否有权限访问
            {
                
                $this->code = 0;
                $this->message = '身份异常，请重新登陆';
                $this->abnormal($this->message, 'Admin/login',$param);
            }
        } else {
            if ($this->getSession['role_id'] != 1) //判断用户角色id是否不等于1
            {
                $find = AdminRoleModel::where('id', $this->getSession['role_id'])->find(); //查询角色id
                $menu_id = explode(",", $find['menu_id']); //拆分menu_id转数组
                $select =  AdminMenuModel::where(['id' => $menu_id])->field('url')->select();
                $url = [];
                foreach ($select as $k => $V) {
                    $url[] = $select[$k]['url'];
                    
                }
                
                $url[] = 'Admin/home';
                $url[] = 'Admin/index';
                $url[] = 'Admin/menupermissions';
                $url[] = 'Admin/exit';
                
                
                if (!in_array($rules, $url)) //判断规则是否有权限访问
                {
                    //$this->code = 0;
                    //$this->message = '身份异常，请重新登陆';
                    $this->error('没有权限');
                }
            }
        }
    }
    public function login() //登陆
    {
        $param = input(); //获取全部传过来的参数
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            if (!Captcha::check($param['verify'])) //判断验证码是否正确
            {
                $this->code = 0;
                $this->message = '登陆失败!输入的验证码是否正确';
            } else {
                $AdminUser = AdminUserModel::where('username', $param['username'])->find(); //查询用户 where条件 find查询一条数据
                if (empty($AdminUser)) //判断用户是否为空
                {
                    $this->code = 0;
                    $this->message = '登陆失败!输入的用户名不存在';
                } else {
                    if ($AdminUser['password'] != saltDncryptionDecryption($param['password'], false)) //判断密码是否正确
                    {
                        $this->code = 0;
                        $this->message = '登陆失败!输入的密码是否正确';
                    } else {
                        $AdminUser['passwrod'] = '';
                        $this->getSession = Session::set('elementAdmin', $AdminUser);
                        $this->code = 200;
                        $this->message = '登陆成功';
                    }
                }
            }
            return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数
        } else {
            if(empty($param['key']))
            {
                ECHO '<link rel="icon" href="__static__/admin/login_icon.png">';
                return 'Internal server exception 500';
            }else{
                if($param['key'] == 'oovIbFPbDfD5BwUGmJFWJCnyb6fPtiYHVyEv6bca9de180244a0a5accbf94f4d3142d9_aw2YUVkhACavxAbcWQ')
                {
                    return View::fetch(); //视图渲染
                }else{
                    return 'Internal server exception 500';
                }
                
            }
            
        }
    }
    public function index() //首页
    {
        $param = input(); //获取全部传过来的参数
        if(empty($param['key']))
        {
            ECHO '<link rel="icon" href="__static__/admin/login_icon.png">';
            return 'Internal server exception 500';
        }else{
            if (Request::instance()->isPost()) //判断是否isPost请求
            {
            } else {
                return View::fetch(); //视图渲染
            }
        }
        
    }
    public function exit() //退出
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            Session::delete('elementAdmin');
            $this->code = 200;
            $this->message = '退出成功';
            return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数
        }
    }
    public function menu() //菜单
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $this->data = AdminMenuModel::where('mid', 0)->select(); //查询菜单mid父级
            foreach ($this->data as $key => $value) //循环数组
            {
                $this->data[$key]['children'] = AdminMenuModel::where('mid', $this->data[$key]['id'])->select(); //查询菜单mid父级
                $this->data[$key]['m_name'] = AdminMenuModel::where('id', $this->data[$key]['mid'])->find()['name']; //查询菜单名称
                $this->data[$key]['create_date'] = date('Y-m-d H:i:s', $this->data[$key]['create_date']); //时间戳转时间
                foreach ($this->data[$key]['children'] as $k => $v) //循环数组
                {
                    $this->data[$key]['children'][$k]['children'] = AdminMenuModel::where('mid', $this->data[$key]['children'][$k]['id'])->select(); //查询菜单mid父级
                    $this->data[$key]['children'][$k]['m_name'] = AdminMenuModel::where('id', $this->data[$key]['children'][$k]['mid'])->find()['name']; //查询菜单名称
                    $this->data[$key]['children'][$k]['create_date'] = date('Y-m-d H:i:s', $this->data[$key]['children'][$k]['create_date']); //时间戳转时间
                }
            }
            if (!empty($this->data)) //判断是否有数据
            {
                $this->code = 200;
                $this->message = '获取成功';
            } else {
                $this->code = 0;
                $this->message = '暂无数据';
            }
            return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数
        } else {
            return View::fetch('admin/menu/index'); //视图渲染
        }
    }
    public function menupermissions() //菜单权限
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $this->data = AdminMenuModel::where('mid', 0)->select(); //查询菜单mid父级
            foreach ($this->data as $key => $value) //循环数组
            {
                $this->data[$key]['children'] = AdminMenuModel::where('mid', $this->data[$key]['id'])->select(); //查询菜单mid父级
                $this->data[$key]['m_name'] = AdminMenuModel::where('id', $this->data[$key]['mid'])->find()['name']; //查询菜单名称
                foreach ($this->data[$key]['children'] as $k => $v) //循环数组
                {
                    $this->data[$key]['children'][$k]['children'] = AdminMenuModel::where('mid', $this->data[$key]['children'][$k]['id'])->select(); //查询菜单mid父级
                    $this->data[$key]['children'][$k]['m_name'] = AdminMenuModel::where('id', $this->data[$key]['children'][$k]['mid'])->find()['name']; //查询菜单名称
                }
            }
            if (!empty($this->data)) //判断是否有数据
            {
                $this->code = 200;
                $this->message = '获取成功';
            } else {
                $this->code = 0;
                $this->message = '暂无数据';
            }
            return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数
        }
    }
    public function addMenu() //添加菜单
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input('param.'); //获取全部传过来的参数
            $this->data = AdminMenuModel::insert([
                'icon' => empty($param['icon']) ? '' : $param['icon'],
                'name' => $param['name'],
                'type' => $param['type'],
                'url' => $param['url'],
                'mid' => empty($param['mid']) ? 0 : $param['mid'],
                'create_date' => time(),
            ]);
            if (!empty($this->data)) //判断状态是否成功或失败
            {
                $this->code = 200;
                $this->message = '添加成功';
            } else {
                $this->code = 0;
                $this->message = '添加失败！';
            }
            return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数
        }
    }
    public function editMenu() //编辑菜单
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input('param.'); //获取全部传过来的参数
            $this->data = AdminMenuModel::where('id', $param['id'])->update([
                'icon' => empty($param['icon']) ? '' : $param['icon'],
                'name' => $param['name'],
                'type' => $param['type'],
                'url' => $param['url'],
                'mid' => empty($param['mid']) ? 0 : $param['mid'],
            ]);
            if (!empty($this->data)) //判断状态是否成功或失败
            {
                $this->code = 200;
                $this->message = '更新成功';
            } else {
                $this->code = 0;
                $this->message = '更新失败！';
            }
            return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数
        }
    }
    public function delMenu() //删除菜单
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input('param.'); //获取全部传过来的参数
            $find = AdminMenuModel::where('mid', $param['id'])->find();
            if (!empty($find)) {
                $this->code = 0;
                $this->message = '删除失败！检查下级菜单是否存在';
            } else {
                $this->data = AdminMenuModel::where('id', $param['id'])->delete();
                if (!empty($this->data)) //判断状态是否成功或失败
                {
                    $this->code = 200;
                    $this->message = '删除成功';
                } else {
                    $this->code = 0;
                    $this->message = '删除失败！';
                }
            }

            return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数
        }
    }
    public function role() //角色
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input('param.'); //获取全部传过来的参数
            if (!empty($param['search'])) //判断搜索是否为空
            {
                $this->where[] = ['name', 'like', '%' . $param['search'] . '%'];
            }
            $this->data = AdminRoleModel::where($this->where)->page($param['page'], $param['rows'])->select();
            foreach ($this->data as $k => $V) {
                $this->data[$k]['menu_id'] = explode(",", $this->data[$k]['menu_id']); //拆分字符串转数组
                $this->data[$k]['create_date'] = date('Y-m-d H:i:s', $this->data[$k]['create_date']); //时间戳转时间
            }
            $count = AdminRoleModel::where($this->where)->count();
            if (!empty($this->data)) //判断是否有数据
            {
                $this->code = 200;
                $this->message = '获取成功';
            } else {
                $this->code = 0;
                $this->message = '暂无数据';
            }
            return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message, 'count' => $count]); //返回JSON参数
        } else {
            return View::fetch('admin/role/index'); //视图渲染
        }
    }
    public function addRole() //添加角色
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input('param.'); //获取全部传过来的参数
            $find = AdminRoleModel::where('name', $param['name'])->find();
            if (!empty($find)) {
                $this->code = 0;
                $this->message = '添加失败！角色名称已经存在';
            } else {
                $this->data = AdminRoleModel::insert([
                    'name' => $param['name'],
                    'create_date' => time(),
                ]);
                if (!empty($this->data)) //判断状态是否成功或失败
                {
                    $this->code = 200;
                    $this->message = '添加成功';
                } else {
                    $this->code = 0;
                    $this->message = '添加失败！';
                }
            }
            return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数
        }
    }
    public function editPermissions() //编辑权限
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input('param.'); //获取全部传过来的参数
            $this->data = AdminRoleModel::where('id', $param['id'])
                ->update([
                    'menu_id' => implode(",", $param['mid']),
                ]);
            if (!empty($this->data)) //判断状态是否成功或失败
            {
                $this->code = 200;
                $this->message = '更新成功';
            } else {
                $this->code = 0;
                $this->message = '更新失败！';
            }
        }
        return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数
    }
    public function delRole() //删除角色
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input('param.'); //获取全部传过来的参数
            $find = AdminUserModel::where('role_id', $param['id'])->find();

            if (!empty($find)) {
                $this->code = 0;
                $this->message = '删除失败！请先删除后台管理员已绑定信息';
            } else {
                $this->data = AdminRoleModel::where('id', $param['id'])->delete();
                if (!empty($this->data)) //判断状态是否成功或失败
                {
                    $this->code = 200;
                    $this->message = '删除成功';
                } else {
                    $this->code = 0;
                    $this->message = '删除失败！';
                }
            }
        }
        return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数
    }
    public function admin() //管理员
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input('param.'); //获取全部传过来的参数
            if (!empty($param['search'])) //判断搜索是否为空
            {
                $this->where[] = ['a.name|a.username', 'like', '%' . $param['search'] . '%'];
            }
            $this->data = AdminUserModel::alias('a')->join('admin_role r', 'a.role_id=r.id')->where($this->where)->page($param['page'], $param['rows'])
                ->field('
                    a.id,
                    a.role_id,
                    a.head,
                    a.name,
                    a.username,
                    a.password,
                    r.name role_name,
                    a.create_date
                ')->select();
            foreach ($this->data as $key => $value) {
                $this->data[$key]['create_date'] = date('Y-m-d H:i:s', $this->data[$key]['create_date']); //时间戳转时间
            }
            $count = AdminUserModel::alias('a')->join('admin_role r', 'a.role_id=r.id')->where($this->where)->count();
            if (!empty($this->data)) //判断是否有数据
            {
                $this->code = 200;
                $this->message = '获取成功';
            } else {
                $this->code = 0;
                $this->message = '暂无数据';
            }
            return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message, 'count' => $count]); //返回JSON参数
        } else {
            return View::fetch('admin/admin/index'); //视图渲染
        }
    }
    public function getAllRoleList() //获取全部角色
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $this->data = AdminRoleModel::select();
            $this->code = 200;
            return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数
        }
    }
    public function addAdminUser() //添加后端用户
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input('param.'); //获取全部传过来的参数
            $find = AdminUserModel::where('username', $param['username'])->find();
            if (!empty($find)) {
                $this->code = 0;
                $this->message = '添加失败！请重新输入该用户名已存在';
            } else {
                $this->data = AdminUserModel::insert([
                    'head' => empty($param['head']) ? '' : $param['head'],
                    'name' => $param['name'],
                    'username' => $param['username'],
                    'password' => saltDncryptionDecryption($param['password'], false),
                    'role_id' => $param['role_id'],
                    'create_date' => time(),
                ]);
                if (!empty($this->data)) //判断状态是否成功或失败
                {
                    $this->code = 200;
                    $this->message = '添加成功';
                } else {
                    $this->code = 0;
                    $this->message = '添加失败！';
                }
            }
            return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数 
        }
    }
    public function editAdminUser() //编辑后端用户
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input('param.'); //获取全部传过来的参数
            if (!empty($param['password'])) //判断密码是否为空
            {
                if(!preg_match("/^[a-zA-Z0-9_-]{6,12}$/",$param['password']))
                {
                    $this->code = 0;
                    $this->message = '请输入6-12位数字+英文的密码';
                }else{
                    
                    $this->data = AdminUserModel::where('id', $param['id'])->update([
                        'head' => empty($param['head']) ? '' : $param['head'],
                        'name' => $param['name'],
                        'password' => saltDncryptionDecryption($param['password'], false),
                        'role_id' => $param['role_id'],
                    ]);
                    if (!empty($this->data)) //判断状态是否成功或失败
                    {
                        $this->code = 200;
                        $this->message = '更新成功';
                    } else {
                        $this->code = 0;
                        $this->message = '更新失败！';
                    }
                }
                
            } else {
                $this->data = AdminUserModel::where('id', $param['id'])->update([
                    'head' => empty($param['head']) ? '' : $param['head'],
                    'name' => $param['name'],
                    'role_id' => $param['role_id'],
                ]);
                if (!empty($this->data)) //判断状态是否成功或失败
                {
                    $this->code = 200;
                    $this->message = '更新成功';
                } else {
                    $this->code = 0;
                    $this->message = '更新失败！';
                }
            }
            return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数 
        }
    }
    public function delAdminUser() //删除后端用户
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input('param.'); //获取全部传过来的参数
            $this->data = AdminUserModel::where('id', $param['id'])->delete();
            if (!empty($this->data)) //判断状态是否成功或失败
            {
                $this->code = 200;
                $this->message = '删除成功';
            } else {
                $this->code = 0;
                $this->message = '删除失败！';
            }
            return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数 
        }
    }
    public function adminCkPassword() //查看密码
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input('param.'); //获取全部传过来的参数

            $user = AdminUserModel::where('id', $param['id'])->find(); //查询id
            $this->data = saltDncryptionDecryption($user['password'], true); //解密
            $this->code = 200;
            return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数 
        }
    }
    public function system() //系统配置
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input('param.'); //获取全部传过来的参数

        } else {
            return View::fetch('admin/system/index'); //视图渲染
        }
    }
    public function getsystem() //获取全部系统配置
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            //查询网站配置
            $this->data['web']['admin_title'] = AdminSystemModel::where('key', 'admin_title')->find()['value'];
            $this->data['web']['key'] = AdminSystemModel::where('key', 'key')->find()['value'];
            return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数 
        }
    }
    public function editweb() //编辑网站配置
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input('param.'); //获取全部传过来的参数
            foreach ($param as $key => $value) {
                AdminSystemModel::where('key', $key)->update(['value' => $value]);
            }
            $this->data = 1;
            if (!empty($this->data)) //判断状态是否成功或失败
            {
                $this->code = 200;
                $this->message = '更新成功';
            } else {
                $this->code = 0;
                $this->message = '更新失败！';
            }
            return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数

        }
    }


    /**                         spacepi                                      */


    //顶部TOP按钮
    public function spacepiBuntton()
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input(); //获取全部传过来的参数
            $Partners = new \app\model\SpacepiBuntton();
            return $Partners->getlist($param);

        } else {
            return View::fetch('admin/spacepi/buntton'); //视图渲染
        }
    }
    //添加TOP按钮
    public function addSpacepiBuntton()
    {
        $param = input(); //获取全部传过来的参数
        $Partners = new \app\model\SpacepiBuntton();
        return $Partners->add($param);
    }
    //编辑TOP按钮
    public function editSpacepiBuntton()
    {
        $param = input(); //获取全部传过来的参数
        $Partners = new \app\model\SpacepiBuntton();
        return $Partners->edit($param);
    }
     //删除TOP按钮
     public function delSpacepiBuntton()
     {
         $param = input(); //获取全部传过来的参数
         $Partners = new \app\model\SpacepiBuntton();
         return $Partners->del($param);
     }
    //获取TOP按钮列表
    public function getSpacepiBunttonList()
    {
        $param = input(); //获取全部传过来的参数
        $SpacepiBunttonList = new \app\model\SpacepiBunttonList();
        return $SpacepiBunttonList->getList($param);
    }
    //添加TOP按钮列表
    public function addSpacepiBunttonList()
    {
        $param = input(); //获取全部传过来的参数
        $SpacepiBunttonList = new \app\model\SpacepiBunttonList();
        return $SpacepiBunttonList->add($param);
    }
    //编辑TOP按钮列表
    public function editSpacepiBunttonList()
    {
        $param = input(); //获取全部传过来的参数
        $SpacepiBunttonList = new \app\model\SpacepiBunttonList();
        return $SpacepiBunttonList->edit($param);
    }
    //删除TOP按钮列表
    public function delSpacepiBunttonList()
    {
        $param = input(); //获取全部传过来的参数
        $SpacepiBunttonList = new \app\model\SpacepiBunttonList();
        return $SpacepiBunttonList->del($param);
    }

    //电报
    public function spacepiTelegraph()
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input(); //获取全部传过来的参数
            $Telegraph = new \app\model\Telegraph();
            return $Telegraph->getlist($param);

        } else {
            return View::fetch('admin/spacepi/telegraph'); //视图渲染
        }
    }
    //添加电报
    public function addTelegraph()
    {
        $param = input(); //获取全部传过来的参数
        $Telegraph = new \app\model\Telegraph();
        return $Telegraph->add($param);
    }
    //编辑电报
    public function editTelegraph()
    {
        $param = input(); //获取全部传过来的参数
        $Telegraph = new \app\model\Telegraph();
        return $Telegraph->edit($param);
    }
    //删除电报
    public function delTelegraph()
    {
        $param = input(); //获取全部传过来的参数
        $Telegraph = new \app\model\Telegraph();
        return $Telegraph->del($param);
    }

    //友情合作商分类
    public function spacepiPartnersList()
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input(); //获取全部传过来的参数
            $PartnersList = new \app\model\PartnersList();
            return $PartnersList->getlist($param);

        } else {
            return View::fetch('admin/spacepi/partnersList'); //视图渲染
        }
    }
    //添加友情合作商分类
    public function addPartnersList()
    {
        $param = input(); //获取全部传过来的参数
        $PartnersList = new \app\model\PartnersList();
        return $PartnersList->add($param);
    }
    //编辑友情合作商分类
    public function editPartnersList()
    {
        $param = input(); //获取全部传过来的参数
        $PartnersList = new \app\model\PartnersList();
        return $PartnersList->edit($param);
    }
    //删除友情合作商分类
    public function delPartnersList()
    {
        $param = input(); //获取全部传过来的参数
        $PartnersList = new \app\model\PartnersList();
        return $PartnersList->del($param);
    }

    //友情合作商链接
    public function spacepiPartners()
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input(); //获取全部传过来的参数
            $Partners = new \app\model\Partners();
            return $Partners->getlist($param);

        } else {
            return View::fetch('admin/spacepi/partners'); //视图渲染
        }
    }
    //获取友情合作商分类
    public function getPartnersList()
    {
        $param = input(); //获取全部传过来的参数
        $PartnersList = new \app\model\PartnersList();
        return $PartnersList->getlists($param);
    }
    //添加友情合作商链接
    public function addPartners()
    {
        $param = input(); //获取全部传过来的参数
        $Partners = new \app\model\Partners();
        return $Partners->add($param);
    }
    //编辑友情合作商链接
    public function editPartners()
    {
        $param = input(); //获取全部传过来的参数
        $Partners = new \app\model\Partners();
        return $Partners->edit($param);
    }
    //删除友情合作商链接
    public function delPartners()
    {
        $param = input(); //获取全部传过来的参数
        $Partners = new \app\model\Partners();
        return $Partners->del($param);
    }

    //顶部导航菜单
    public function spacepiMenu()
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input(); //获取全部传过来的参数
            $SpacepiMenu = new \app\model\SpacepiMenu();
            return $SpacepiMenu->getlist($param);

        } else {
            return View::fetch('admin/spacepi/menu'); //视图渲染
        }
    }
    //添加顶部导航菜单
    public function addSpacepiMenu()
    {
        $param = input(); //获取全部传过来的参数
        $SpacepiMenu = new \app\model\SpacepiMenu();
        return $SpacepiMenu->add($param);
    }
    //编辑顶部导航菜单
    public function editSpacepiMenu()
    {
        $param = input(); //获取全部传过来的参数
        $SpacepiMenu = new \app\model\SpacepiMenu();
        return $SpacepiMenu->edit($param);
    }
    //删除顶部导航菜单
    public function delSpacepiMenu()
    {
        $param = input(); //获取全部传过来的参数
        $SpacepiMenu = new \app\model\SpacepiMenu();
        return $SpacepiMenu->del($param);
    }

    //重要合作商
    public function spacepiIndexPartners()
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input(); //获取全部传过来的参数
            $IndexPartners = new \app\model\IndexPartners();
            return $IndexPartners->getlist($param);

        } else {
            return View::fetch('admin/spacepi/indexPartners'); //视图渲染
        }
    }
    //添加重要合作商
    public function addSpacepiIndexPartners()
    {
        $param = input(); //获取全部传过来的参数
        $IndexPartners = new \app\model\IndexPartners();
        return $IndexPartners->add($param);
    }
    //编辑重要合作商
    public function editSpacepiIndexPartners()
    {
        $param = input(); //获取全部传过来的参数
        $IndexPartners = new \app\model\IndexPartners();
        return $IndexPartners->edit($param);
    }
    //删除重要合作商
    public function delSpacepiIndexPartners()
    {
        $param = input(); //获取全部传过来的参数
        $IndexPartners = new \app\model\IndexPartners();
        return $IndexPartners->del($param);
    }

    //底部友情链接
    public function SpacepiBottomPartners()
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input(); //获取全部传过来的参数
            $SpacepiBottomPartners = new \app\model\SpacepiBottomPartners();
            return $SpacepiBottomPartners->getlist($param);

        } else {
            return View::fetch('admin/spacepi/bottomPartners'); //视图渲染
        }
    }
    //添加底部友情链接
    public function addSpacepiBottomPartners()
    {
        $param = input(); //获取全部传过来的参数
        $SpacepiBottomPartners = new \app\model\SpacepiBottomPartners();
        return $SpacepiBottomPartners->add($param);
    }
    //编辑底部友情链接
    public function editSpacepiBottomPartners()
    {
        $param = input(); //获取全部传过来的参数
        $SpacepiBottomPartners = new \app\model\SpacepiBottomPartners();
        return $SpacepiBottomPartners->edit($param);
    }
    //删除底部友情链接
    public function delSpacepiBottomPartners()
    {
        $param = input(); //获取全部传过来的参数
        $SpacepiBottomPartners = new \app\model\SpacepiBottomPartners();
        return $SpacepiBottomPartners->del($param);
    }

    // 语言包
    public function spacepiLocale()
    {
        if (Request::instance()->isPost()) //判断是否isPost请求
        {
            $param = input(); //获取全部传过来的参数
            $SpacepiBottomPartners = new \app\model\SpacepiBottomPartners();
            return $SpacepiBottomPartners->getlist($param);

        } else {
            return View::fetch('admin/spacepi/locale'); //视图渲染
        }
    }

}

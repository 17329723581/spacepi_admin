<?php
//命名:app\控制器
namespace app\controller;
//调用继承控制器
use app\BaseController;

class Api extends BaseController //Api    AIP接口控制器
{
    private $key;
    public $data = [];
    public $code = 0;
    public $message = '';
    public $where = [];
    public function __construct() // 初始化构造
    {
        $AdminSystemModel = new \app\model\AdminSystemModel();
        $this->key = $AdminSystemModel->where('key','key')->value('value');
    }
    //TOP按钮
    public function spacepiBuntton()
    {
        $params = input();
        $SpacepiBuntton = new \app\model\SpacepiBuntton();
        $this->data = $SpacepiBuntton->order('sort asc')->select();
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
    //TOP按钮列表
    public function spacepiBunttonList()
    {
        $SpacepiBuntton = new \app\model\SpacepiBunttonList();
        $this->data = $SpacepiBuntton->order('sort asc')->select();
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
    //电报
    public function telegraph()
    {
        $params = input();
        $Telegraph = new \app\model\Telegraph();
        $this->data = $Telegraph->order('sort asc')->select();
        foreach($this->data as $k=>$v)
        {
            $this->data[$k]['picture'] = 'http://' . $_SERVER['HTTP_HOST'] . str_replace("\\", "/", $this->data[$k]['picture']);
            $this->data[$k]['m_picture'] = 'http://' . $_SERVER['HTTP_HOST'] . str_replace("\\", "/", $this->data[$k]['m_picture']);
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
    //友情合作商分类
    public function partnersList()
    {
        $params = input();
        $PartnersList = new \app\model\PartnersList();
        $this->data = $PartnersList->order('sort asc')->select();
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
    //友情合作商链接
    public function Partners()
    {
        $params = input();
        $Partners = new \app\model\Partners();
        if(!empty($params['id']))
        {
            $this->where['p_id'] = $params['id'];
        }
        $this->data = $Partners->where($this->where)->order('sort asc')->select();
        foreach($this->data as $k=>$v)
        {
            $this->data[$k]['picture'] = 'http://' . $_SERVER['HTTP_HOST'] . str_replace("\\", "/", $this->data[$k]['picture']);
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
    //顶部导航菜单
    public function spacepiMenu()
    {
        $SpacepiMenu = new \app\model\SpacepiMenu();
        $this->data = $SpacepiMenu->where($this->where)->order('sort asc')->select();
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
    //重要合作商
    public function indexPartners()
    {
        $IndexPartners = new \app\model\IndexPartners();
        $this->data = $IndexPartners->where($this->where)->order('sort asc')->select();
        foreach($this->data as $k=>$v)
        {
            $this->data[$k]['picture'] = 'http://' . $_SERVER['HTTP_HOST'] . str_replace("\\", "/", $this->data[$k]['picture']);
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
    //底部友情链接
    public function bottomPartners()
    {
        $SpacepiBottomPartners = new \app\model\SpacepiBottomPartners();
        $this->data = $SpacepiBottomPartners->order('sort asc')->select();
        foreach($this->data as $k=>$v)
        {
            $this->data[$k]['picture'] = 'http://' . $_SERVER['HTTP_HOST'] . str_replace("\\", "/", $this->data[$k]['picture']);
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

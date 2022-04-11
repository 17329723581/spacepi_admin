<?
namespace app\model;//命名:app\模型

use think\Model;//调用继承模型

class PartnersList extends Model //AdminModel   友情合作商分类模型
{
    protected $table='spacepi_partners_list';//数据表名
    public $data = [];
    public $code = 0;
    public $message = '';
    public $where = [];
    public function getlist($param) // 获取列表
    {
        $param['page'] = empty($param['page'])?1:$param['page'];
        $param['rows'] = empty($param['rows'])?10:$param['rows'];
        $this->data = $this->where($this->where)->page($param['page'], $param['rows'])->order('sort sac')->select();
        $count = $this::where($this->where)->count();
        if (!empty($this->data)) //判断是否有数据
        {
            $this->code = 200;
            $this->message = '获取成功';
        } else {
            $this->code = 0;
            $this->message = '暂无数据';
        }
        return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message, 'count' => $count]); //返回JSON参数
    }
    public function add($param) //添加
    {
        $this->data = $this::insertGetId([
            'en_US' => $param['en_US'],
            'zh_TW' => $param['zh_TW'],
            'zh_CN' => $param['zh_CN'],
            'tr_TR' => $param['tr_TR'],
            'ko_KR' => $param['ko_KR'],
            'ja_JP' => $param['ja_JP'],
            'in'    => $param['in'],
            'th'    => $param['th'],
            'ar'    => $param['ar'],
            'sort' => $param['sort'],
            'create_date'=> time(),
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
    public function edit($param) //编辑
    {
        $this->data = $this->where('id', $param['id'])->update([
            'en_US' => $param['en_US'],
            'zh_TW' => $param['zh_TW'],
            'zh_CN' => $param['zh_CN'],
            'tr_TR' => $param['tr_TR'],
            'ko_KR' => $param['ko_KR'],
            'ja_JP' => $param['ja_JP'],
            'in'    => $param['in'],
            'th'    => $param['th'],
            'ar'    => $param['ar'],
            'sort' => $param['sort'],
        ]);
        if (!empty($this->data)) //判断状态是否成功或失败
        {
            $this->code = 200;
            $this->message = '编辑成功';
        } else {
            $this->code = 0;
            $this->message = '编辑失败！请不要重复提交';
        }
        return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数
    }
    public function del($param) //删除
    {
        $Partners = new \app\model\Partners();
        $find = $Partners->where('p_id',$param['id'])->find();
        $finds = $this->where('id',$param['id'])->value('zh_CN');
        if(!empty($find))
        {
            $this->code = 0;
            $this->message = '删除失败!请删除该'.$finds.'已经绑定信息';
        }else{
            $this->data = $this->where('id',$param['id'])->delete();
            if (!empty($this->data)) //判断状态是否成功或失败
            {
                $this->code = 200;
                $this->message = '删除成功';
            } else {
                $this->code = 0;
                $this->message = '删除失败!';
            }
        }
        return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数
    }
    public function getlists() //获取分类
    {
        $this->data = $this->order('sort sac')->select();
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
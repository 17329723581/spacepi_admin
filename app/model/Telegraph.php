<?
namespace app\model;//命名:app\模型

use think\Model;//调用继承模型

class Telegraph extends Model //AdminModel   电报模型
{
    protected $table='spacepi_telegraph';//数据表名
    public $data = [];
    public $code = 0;
    public $message = '';
    public $where = [];
    public function getlist($param) // 获取列表
    {
        $param['page'] = empty($param['page'])?1:$param['page'];
        $param['rows'] = empty($param['rows'])?10:$param['rows'];
        $this->data = $this->page($param['page'], $param['rows'])->order('sort sac')->select();
        $count = $this::count();
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
        $count = $this->count();
        if($count>=16)
        {
            $this->code = 0;
            $this->message = '添加失败!最多只能添加16条数据';
        }else{
            $this->data = $this::insert([
                'name'=>$param['name'],
                'picture' => $param['picture'],
                'm_picture' => $param['m_picture'],
                'link' => $param['link'],
                'sort' => $param['sort'],
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
    public function edit($param) //编辑
    {
        $this->data = $this->where('id', $param['id'])->update([
            'name'=>$param['name'],
            'picture' => $param['picture'],
            'm_picture' => $param['m_picture'],
            'link' => $param['link'],
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
        $this->data = $this->where('id',$param['id'])->delete();
        if (!empty($this->data)) //判断状态是否成功或失败
        {
            $this->code = 200;
            $this->message = '删除成功';
        } else {
            $this->code = 0;
            $this->message = '删除失败!';
        }
        return json(['data' => $this->data, 'code' => $this->code, 'message' => $this->message]); //返回JSON参数
    }
}
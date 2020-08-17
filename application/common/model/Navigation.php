<?php
namespace app\common\model;
use app\common\validate\Navigation as Verify;
use think\Db;
use \think\Model;


class Navigation extends BaseModel
{
    // 设置当前模型对应的完整数据表名称
    protected $autoWriteTimestamp = true;
    protected $validate;
    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->validate = new Verify();
    }


    /**
     * Notes:列表
     * @auther: xxf
     * Date: 2019/7/9
     * Time: 16:18
     * @param $size
     * @param $where
     * @return \think\Paginator
     */
    public function getList($size,$where){

            $res = $this
                ->field('id,title,picture,create_time')
                ->order('id','desc')
                ->where($where)
                ->paginate($size);
            return $res;
    }

    public function getInfoById($id){
        $res = $this->get($id);
        return $res;
    }





    /**
     * 更新文章内容
     * @param $input
     * @return array
     */
    public function edit($data){

        $id = $data['id'];
            $saveData = [
                'name' => $data['name'] ?? '',
                'list_order' => $data['list_order'] ?? 0,
                'parent_id' => $data['parent_id'] ?? 0,
                'status' => $data['status'] ?? 1,
                'href' =>  $data['href'] ?? '',
                'catalog_id' =>  $data['catalog_id'] ?? 0,
                'icon' => $data['icon'] ?? '',
                'type' => $data['type'] ?? 0,
            ];
            $tokenData = ['__token__' => isset($data['__token__']) ? $data['__token__'] : '',];
            $validateRes = $this->validate($this->validate, $saveData, $tokenData);
            if ($validateRes['tag']) {
                $saveTag = $this
                    ->where('id', $id)
                    ->update($saveData);
                $validateRes['tag'] = $saveTag;
                $validateRes['message'] = $saveTag ? '修改成功' : '数据无变动';
            }
        return $validateRes;
    }

    /**
     * 添加操作
     * @param $data
     * @return array
     */

    public function add($data){

        $addData = [
            'name' => $data['name'] ?? '',
            'list_order' => $data['list_order'] ?? 0,
            'parent_id' => $data['parent_id'] ?? 0,
            'status' => $data['status'] ?? 1,
            'href' =>  $data['href'] ?? '',
            'catalog_id' =>  $data['catalog_id'] ?? 0,
            'create_time' => time(),
            'icon' => $data['icon'] ?? '',
            'type' => $data['type'] ?? 0,
        ];
        $tokenData = ['__token__' => $data['__token__'] ?? ''];
        $validateRes = $this->validate($this->validate, $addData, $tokenData);
        if ($validateRes['tag']) {
            $tag = $this->insert($addData);
            $validateRes['tag'] = $tag;
            $validateRes['message'] = $tag ? '添加成功' : '添加失败';
        }
        return $validateRes;
    }


    public function deleteByIds($ids)
    {
        $res = $this
            ->where('id','in',$ids)
            ->delete();
        return $res;
    }


    /**
     * Notes:
     * @auther: xxf
     * Date: 2019/7/10
     * Time: 16:36
     * @return array
     */
    public function getMenuTree(){

        $list = $this->select();
        return $this->sort($list);
    }


    public function sort($data,$pid=0,$level=0){
        static $arr = array();
        foreach($data as $k=>$v){
            if($v['parent_id'] == $pid){
                $v['level'] = $level;
                $arr[] = $v;
                $this->sort($data,$v['id'],$level+1);
            }
        }
        return $arr;
    }

    /**
     * Notes:前端获取导航列表
     * @auther: xxf
     * Date: 2019/7/24
     * Time: 10:28
     * @param int $parent_id
     * @param null $limit
     * @return array|false|\PDOStatement|string|\think\Collection
     */
    public function getNavigationByPid($parent_id = 0, $limit = null)
    {
        if ($limit > 0) {
            $data = $this
                ->where('parent_id', $parent_id)
                ->order(['list_order' => 'desc'])
                ->limit($limit)
                ->select();
        } else {
            $data = $this
                ->where('parent_id', $parent_id)
                ->order(['list_order' => 'desc'])
                ->select();
        }

        foreach ($data as $k => &$v)
        {
            //内链
            if (!empty($v['catalog_id']))
                $v['href'] = url('index/article/getArticleList',['catalog_id'=>$v['catalog_id']]);
        }

        return $data;
    }



}
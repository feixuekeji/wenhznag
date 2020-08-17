<?php

namespace app\common\model;

use app\common\validate\Subscription;
use app\common\model\BaseModel;


class Subscriptions extends BaseModel
{
    protected $validate;
    protected $autoWriteTimestamp = true;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->validate = new Subscription();
    }


    /**
     * 根据ID获取详情
     * @param $id
     * @return array
     */
    public function getInfoByID($id)
    {
        $res = $this
            ->where('id = ' . $id)
            ->find();
        return $res;
    }


    /**
     * 后台获取数据列表
     * @param $curr_page
     * @param int $limit
     * @param null $search
     * @return array
     */
    public function getList($size = 10, $where = null)
    {
        $res = $this
            ->where($where)
            ->order(['list_order' => 'desc', 'id' => 'desc'])
            ->paginate($size);
        return $res;
    }


    /**
     * Notes:推荐列表
     * @auther: xxf
     * Date: 2019/7/24
     * Time: 16:53
     * @param int $size
     * @param null $where
     * @return array|false|\PDOStatement|string|\think\Collection
     */
    public function getTopList($size = 10, $where = null)
    {
        $res = $this
            ->where($where)
            ->where('status',1)
            ->orderRaw("RAND()")
            ->limit($size)
            ->select();
        return $res;
    }


    /**
     * 更新文章内容
     * @param $input
     * @return array
     */
    public function edit($data)
    {
        $id = $data['id'];
        $saveData = [
            'name' => $data['name'] ?? '',
            'account' => $data['account'] ?? '',
            'list_order' => $data['list_order'] ?? 0,
            'profile' => $data['profile'] ?? 0,
            'is_collection' => $data['is_collection'] ?? 0,
            'avatar' => $data['avatar'] ?? '',
            'status' => $data['status'] ?? 0,
        ];
        $tokenData = ['__token__' => $data['__token__'] ?? ''];
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

    public function add($data)
    {

        $addData = [
            'name' => $data['name'] ?? '',
            'account' => $data['account'] ?? '',
            'list_order' => $data['list_order'] ?? 0,
            'profile' => $data['profile'] ?? 0,
            'is_collection' => $data['is_collection'] ?? 0,
            'avatar' => $data['avatar'] ?? '',
            'status' => $data['status'] ?? 0,
        ];
        $tokenData = ['__token__' => isset($data['__token__']) ? $data['__token__'] : '',];
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
     * Notes:推荐
     * @auther: xxf
     * Date: 2019/8/5
     * Time: 16:05
     * @param $ids
     * @return int|string
     */
    public function recommendByIds($ids)
    {
        $res = $this
            ->where('id','in',$ids)
            ->update(['status'=>1]);
        return $res;
    }

    public function is_collectByIds($ids)
    {
        $res = $this
            ->where('id','in',$ids)
            ->update(['is_collection'=>1]);
        return $res;
    }



}

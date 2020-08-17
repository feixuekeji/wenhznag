<?php
namespace app\common\model;
use think\Model;
class Filter extends Model
{
    protected $autoWriteTimestamp = true;//自动时间戳

    public function getList($limit){
        $res = $this
            ->paginate($limit);
        return $res;
    }


    /**
     * Notes:批量删除
     * Date: 2019/7/8
     * Time: 15:59
     * @param $ids
     * @return int|string|static
     */
    public function deleteByIds($ids)
    {
        $res = $this
            ->where('id','in',$ids)
            ->delete();
        return $res;
    }


    public function add($keyword,$type)
    {
        $sameTag = $this->isExist($keyword);
        if ($sameTag) {
            $validateRes['tag'] = 0;
            $validateRes['message'] = '已添加';
        } else {
            $addData = [
                'keyword' => $keyword,
                'type' => $type,
                'create_time' => time(),

            ];
            $tag = $this->save($addData);
            $validateRes['tag'] = $tag;
            $validateRes['message'] = $tag ? '添加成功' : '添加失败';
        }
        return $validateRes;

    }



    /**
     * 判断当前数据库中是否有
     * @param $user_name
     * @param int $id
     * @return mixed
     */
    public function isExist($keyword){
        $tag = $this
            ->where('keyword',$keyword)
            ->count();
        return $tag;
    }

    public function getAll()
    {
        $res = $this->all();
        return $res;
    }


}

<?php
namespace app\common\model;
use think\Model;
class Favorite extends Model
{
    protected $autoWriteTimestamp = true;//自动时间戳

    /**
     * Notes:列表
     * @auther: xxf
     * Date: 2019/7/9
     * Time: 14:41
     * @param $limit
     * @param $where
     * @return \think\Paginator
     */
    public function getList($limit,$where){
        $res = $this
            ->alias('f')
            ->field('f.*,a.title,a.picture')
            ->leftJoin('article a','f.article_id = a.id')
            ->order('f.id','desc')
            ->where($where)
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


    public function add($user_id, $article_id)
    {
        $sameTag = $this->isExist($user_id, $article_id);
        if ($sameTag) {
            $validateRes['tag'] = 0;
            $validateRes['message'] = '已收藏';
        } else {
            $addData = [
                'user_id' => $user_id,
                'article_id' => $article_id,
                'create_time' => time(),

            ];
            $tag = $this->save($addData);
            $validateRes['tag'] = $tag;
            $validateRes['message'] = $tag ? '添加成功' : '添加失败';
        }
        return $validateRes;

    }


    public function cancel($user_id, $article_id)
    {
        $res = $this
            ->where(['user_id' => $user_id, 'article_id' => $article_id])
            ->delete();
        $validateRes['tag'] = $res;
        $validateRes['message'] = $res ? '成功' : '失败';
        return $validateRes;

    }


    /**
     * 判断当前数据库中是否有
     * @param $user_name
     * @param int $id
     * @return mixed
     */
    public function isExist($user_id,$article_id){
        $tag = $this
            ->where('user_id',$user_id)
            ->where('article_id',$article_id)
            ->count();
        return $tag;
    }


}

<?php
namespace app\common\model;
use think\Model;
class UserFollow extends Model
{


    /**
     * Notes:关注列表
     * @auther: xxf
     * Date: 2019/7/9
     * Time: 14:41
     * @param $limit
     * @param $where
     * @return \think\Paginator
     */
/*    public function getFollowList($limit,$where){
        $res = $this
            ->alias('f')
            ->field('f.*,u.openid,u.nickname,u.avatar')
            ->join('users u','f.follow_user_id = u.id')
            ->order('f.id','desc')
            ->where($where)
            ->paginate($limit);

        return $res;
    }*/

    public function getFollowList($limit,$where){
        global $_W;
        $my_user_id = $_W['user']['id'] ?? 0;
        $res = $this
            ->alias('f')
            ->field('f.*,u.openid,u.nickname,u.avatar,uf.id follow')
            ->leftJoin('users u','f.follow_user_id = u.id')
            ->leftJoin('user_follow uf','f.follow_user_id = uf.follow_user_id and uf.user_id = ' . $my_user_id)
            ->order('f.id','desc')
            ->where($where)
            ->paginate($limit);

        return $res;
    }

    /**
     * Notes:粉丝列表
     * @auther: xxf
     * Date: 2019/7/9
     * Time: 14:42
     * @param $limit
     * @param $where
     * @return \think\Paginator
     */
    public function getFansList($limit,$where){
        $res = $this
            ->alias('f')
            ->field('f.*,u.openid,u.nickname,u.avatar,uf.id fans')
            ->leftJoin('users u','f.user_id = u.id')
            ->leftJoin('user_follow uf','f.user_id = uf.follow_user_id')
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


    public function add($user_id, $follow_user_id)
    {
        $sameTag = $this->isExist($user_id, $follow_user_id);
        if ($sameTag) {
            $validateRes['tag'] = 0;
            $validateRes['message'] = '已关注';
        } else {
            $addData = [
                'user_id' => $user_id,
                'follow_user_id' => $follow_user_id,
                'create_time' => time(),
            ];
            $tag = $this->save($addData);
            $validateRes['tag'] = $tag;
            $validateRes['message'] = $tag ? '添加成功' : '添加失败';
        }
        return $validateRes;

    }

    /**
     * Notes:取消关注
     * @auther: xxf
     * Date: 2019/7/17
     * Time: 14:37
     * @param $user_id
     * @param $follow_user_id
     * @return mixed
     */
    public function cancelFollow($user_id, $follow_user_id)
    {
        $res = $this
            ->where(['user_id' => $user_id, 'follow_user_id' => $follow_user_id])
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
    public function isExist($user_id,$follow_user_id){
        $tag = $this
            ->where('user_id',$user_id)
            ->where('follow_user_id',$follow_user_id)
            ->count();
        return $tag;
    }


}

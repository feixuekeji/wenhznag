<?php
namespace app\common\model;
use app\common\Qiniu;
use app\common\validate\Article as Verify;
use QiNiuUpload\Upload;
use think\Db;
use \think\Model;


class Article extends BaseModel
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
     * Notes:用户发布列表
     * @auther: xxf
     * Date: 2019/7/9
     * Time: 16:18
     * @param $size
     * @param $where
     * @return \think\Paginator
     */
    public function getUserPublishList($size,$where){

            $res = $this
                ->field('id,title,picture,create_time')
                ->order('id','desc')
                ->where($where)
                ->paginate($size);
            return $res;
    }



    public function getSimilarList($title){
        $all_list = $this
            ->field('id,title')
            ->where('id','>',0)
            ->where('title','<>',$title)
            ->where('status',1)
            ->select()
            ->toArray();
        foreach ($all_list AS $k => $v)
        {
            $similar[$k]  = similar_text($v['title'], $title, $persent);
        }
        array_multisort($similar, SORT_DESC, $all_list);
        $output = array_slice($all_list, 0,6);
        return $output;
    }



    /**
     * 获取所要推荐的文章
     * @return array
     */
    public function getHotArticleList(){
        $res = $this
            ->field('a.id,a.title,a.abstract,a.picture,a.create_time,a.user_id,u.nickname')
            ->alias('a')
            ->leftJoin('users u','a.user_id = u.id')
            ->order('a.view','desc')
            ->limit(10)
            ->select();
        return $res;
    }



    /**增加阅读量
     * @param $id
     * @return int|true
     */
    public function addView($id){
        $res = $this
            ->where('id', $id)
            ->setInc('view', 1);
        return $res;
    }




    public function getPreAndNext($id,$catalog = 0)
    {

        if ($catalog)
            $where[] = array('catalog_id','=', $catalog);

        // 下一页查询条件

        $where[] = array('id','>', $id);

        $next_topic = $this->field('id,title')->order('id asc')->where($where)->find();


        // 上一篇查询条件
        array_pop($where);
        $where[] = array('id','<', $id);
        $prev_topic = $this->field('id,title')->order('id desc')->where($where)->find();
        $data['pre'] = $prev_topic;
        $data['next'] = $next_topic;
        return $data;
    }

    /**
     * 后台获取文章数据列表
     * @param $curr_page
     * @param int $limit
     * @param null $search
     * @return array
     */
    public function getArticleList($size = 10,$where = null){
        $res = $this
            ->field('a.*,u.user_name,c.name catalog_name,s.name wechat_name')
            ->alias('a')
            ->leftJoin('users u','a.user_id = u.id')
            ->leftJoin('catalog c','a.catalog_id = c.id')
            ->leftJoin('subscriptions s','a.wechat_id = s.id')
            ->where($where)
            ->order(['a.list_order'=>'desc','a.id'=>'desc'])
            ->paginate($size);
        return $res;
    }


    /**
     * 根据文章ID 获取文章内容
     * @param $id
     * @return array
     */
    public function getInfoByID($id){
        $res = $this
            ->where('id',$id)
            ->find();
        return $res;
    }

    public function getDetailByID($id){
        $res = $this
            ->field('a.*,u.nickname')
            ->alias('a')
            ->leftJoin('users u','a.user_id = u.id')
            ->where('a.id',$id)
            ->find();
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
                'title' => $data['title'],
                'catalog_id' =>  $data['catalog_id'],
                'wechat_id' =>  0,
                'list_order' => $data['list_order'],
                'content' => isset($data['content'])?$data['content']:'',
                'picture' => $data['picture'],
                'status' => $data['status'],
                'abstract' => $data['abstract'],
                'source_url' => $data['source_url'],
                'tag' => $data['tag'],
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
     * 进行新文章的添加操作
     * @param $data
     * @return array
     */

    public function addArticle($data){

        $addData = [
            'title' => $data['title'] ?? '',
            'user_id' => $data['user_id'] ?? 0,
            'catalog_id' =>  $data['catalog_id'] ?? 0,
            'wechat_id' =>  $data['wechat_id'] ?? 0,
            'list_order' => $data['list_order']  ?? 0,
            'content' => isset($data['content'])?$data['content']:'',
            'picture' => $data['picture'] ?? '',
            'is_hot' => $data['is_hot'] ?? 0,
            'status' => $data['status'] ?? 2,
            'abstract' => $data['abstract'] ?? '',
            'source_url' => $data['source_url'] ?? '',
            'tag' => $data['tag'],
            'create_time' => time(),
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

    /**
     * Notes:软删除
     * @auther: xxf
     * Date: 2019/8/13
     * Time: 14:57
     * @param $ids
     * @return int|string
     */
    public function deleteByIds($ids)
    {
        $res = $this
            ->where('id','in',$ids)
            ->update(['status'=>3]);
        return $res;
    }


    public function delByIds($ids)
    {
        $list = $this
            ->field('content')
            ->where('id','in',$ids)
            ->select();
        $images = [];
        foreach ($list as $k => $v)
        {
            $images = array_merge($images,getQiNiuImages($v['content']));
        }
        //删除七牛图片
        $qiniu = new Upload();
        $qiniu->deleteFiles($images);
        $res = $this
            ->where('id','in',$ids)
            ->delete();
        return $res;
    }



    public function recoveryByIds($ids)
    {
        $res = $this
            ->where('id','in',$ids)
            ->update(['status'=>2]);
        return $res;
    }

    /**
     * 前台获取文章数据列表
     * @param $curr_page
     * @param int $limit
     * @param null $search
     * @return array
     */
    public function getArticlesForPage($size = 10,$where =null){
        $res = $this
            ->field('a.*,u.nickname')
            ->alias('a')
            ->leftJoin('users u','a.user_id = u.id')
            ->where($where)
            ->where('a.status',1)
            ->order(['list_order'=>'desc','id'=>'desc'])
            ->paginate($size);
        return $res;
    }


    /**
     * Notes:列表页文章列表
     * @auther: xxf
     * Date: 2019/7/25
     * Time: 14:22
     * @param int $size
     * @param int $cate_id
     * @param null $keyword
     * @return \think\Paginator
     */
    public function getListArticleAndImage($size = 10,$where =null){
        $res = $this
            ->field('a.*,u.nickname')
            ->alias('a')
            ->leftJoin('users u','a.user_id = u.id')
            ->where($where)
            ->order(['list_order'=>'desc','id'=>'desc'])
            ->paginate($size);
        foreach ($res as $k => &$v)
        {
            $image_list = getImages($v['content']);
            if (!empty($image_list) && !empty($v['picture']))
                array_unshift($image_list,$v['picture']);//加入封面
            $v['image_list'] = $image_list;
        }
        return $res;
    }





    public function getLikeList($catalog_id,$size = 20){
        $res = $this
            ->field('id,title')
            ->where('catalog_id',$catalog_id)
            ->orderRaw("RAND()")//随机取
            ->paginate($size);
        return $res;
    }


    public function getArticlesByWechatId($size = 10,$wechat_id = 0){
        $where['status'] = 1;
        $where['wechat_id'] = $wechat_id;
        $res = $this
            ->field('id,title,picture,create_time')
            ->where($where)
            ->order(['list_order'=>'desc','id'=>'desc'])
            ->paginate($size);
        return $res;
    }


}
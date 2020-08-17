<?php

namespace app\index\controller;

use app\common\model\Favorite as M;
use think\Request;
use think\facade\Cache;

class Favorite extends Base
{
    private $model;


    public function __construct()
    {
        parent::__construct(false);
        $this->model = new M();

    }

    public function getFavoriteList(Request $request)
    {
        global $_W;
        $user_id = $request->param('user_id', 0);
        if (empty($user_id))
            $user_id = $_W['user']['id'];
        $user_id == $_W['user']['id'] && $data['is_me'] = 1;//查询自己
        $size = $request->param('size', 10);
        $where = array();
        $where[] = array('f.user_id', '=', $user_id);
        $data['list'] = $this->model->getList($size, $where);
        $data['user_id'] = $user_id;
        $data['seo']['title'] = "个人中心";
        return view('list', $data);
    }

    /**
     * Notes:添加关注
     * @auther: xxf
     * Date: 2019/7/17
     * Time: 14:38
     * @param Request $request
     */
    public function addFavorite(Request $request)
    {
        global $_W;
        $this->checkToken();
        $user_id = $_W['user']['id'];
        $article_id = $request->param('article_id', 0);
        $opRes = $this->model->add($user_id,$article_id);
        return ajaxResponse('',$opRes['tag'] ^ 1,$opRes['message']);
    }


    /**
     * Notes:取消关注
     * @auther: xxf
     * Date: 2019/7/17
     * Time: 14:38
     * @param Request $request
     */
    public function cancelFollow(Request $request)
    {
        global $_W;
        $this->checkToken();
        $user_id = $_W['user']['id'];
        $article_id = $request->param('article_id', 0);
        $opRes = $this->model->cancel($user_id,$article_id);
        return ajaxResponse('',$opRes['tag'] ^ 1,$opRes['message']);
    }




}

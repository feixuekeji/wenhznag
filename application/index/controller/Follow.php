<?php

namespace app\index\controller;

use app\common\model\UserFollow;
use think\Request;
use think\facade\Cache;

class Follow extends Base
{
    private $model;

    public function __construct()
    {
        parent::__construct(false);
        $this->model = new UserFollow();

    }

    /**
     * Notes:关注列表
     * @auther: xxf
     * Date: 2019/7/9
     * Time: 15:13
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View|void
     */
/*    public function getFollowList(Request $request)
    {
        global $_W;
        $user_id = $_W['user']['id'];
        if ($request->isAjax()) {
            $size = $request->param('size', 10);

            $where = array();
            $where[] = array('f.user_id', '=', $user_id);
            $list = $this->model->getFollowList($size, $where);
            return ajaxResponse($list, 0);
        } else {
            $data = [
                'user_id' => $user_id,
            ];
            return view('follow_list', $data);
        }

    }*/


    public function getFollowList(Request $request)
    {
        global $_W;
            $user_id = $request->param('user_id',0);
            if (empty($user_id))
                $user_id = $_W['user']['id'];
            $user_id == $_W['user']['id'] && $data['is_me'] = 1;//查询自己
            $size = $request->param('size', 10);
            $where = array();
            $where[] = array('f.user_id', '=', $user_id);
            $data['list'] = $this->model->getFollowList($size, $where);
            $data['user_id'] = $user_id;
            $data['seo']['title'] = "个人中心";


            return view('follow_list', $data);
    }

    /**
     * Notes:粉丝列表
     * @auther: xxf
     * Date: 2019/7/17
     * Time: 14:27
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View|void
     */
    public function getFansList(Request $request)
    {
        global $_W;
        $user_id = $request->param('user_id',0);
        if (empty($user_id))
            $user_id = $_W['user']['id'];
        $user_id == $_W['user']['id'] && $data['is_me'] = 1;//查询自己
        $size = $request->param('size', 10);
        $where = array();
        $where[] = array('f.follow_user_id', '=', $user_id);
        $data['list'] = $this->model->getFansList($size, $where);
        $data['user_id'] = $user_id;
        $data['seo']['title'] = "个人中心";
        return view('fans_list', $data);

    }

    /**
     * Notes:添加关注
     * @auther: xxf
     * Date: 2019/7/17
     * Time: 14:38
     * @param Request $request
     */
    public function toFollow(Request $request)
    {
        global $_W;
        $this->checkToken();
        $user_id = $_W['user']['id'];
        $follow_user_id = $request->param('follow_user_id', 0);
        $opRes = $this->model->add($user_id,$follow_user_id);
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
        $follow_user_id = $request->param('follow_user_id', 0);
        $opRes = $this->model->cancelFollow($user_id,$follow_user_id);
        return ajaxResponse('',$opRes['tag'] ^ 1,$opRes['message']);
    }




}

<?php

namespace app\index\controller;

use app\common\model\User;
use think\Request;

class User extends Base
{
    private $model;
    public function __construct()
    {
        parent::__construct(false);
        $this->model = new User();
    }

    /**
     * Notes:个人信息
     * @auther: xxf
     * Date: 2019/7/17
     * Time: 15:16
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function getMyInfo(Request $request)
    {
        global $_W;
        $this->checkToken();
        $user_id = $_W['user']['id'];
        $data['info'] = $this->model->get($user_id);
        $data['seo']['title'] = "个人中心";
        return view('set', $data);
    }

    public function editMyInfo(Request $request)
    {
        global $_W;
        $this->checkToken();
        $user_id = $_W['user']['id'];
        $data = $request->param();
        $opRes = $this->model->editMemberInfo($user_id,$data);
        return ajaxResponse('',$opRes['tag'] ^ 1,$opRes['message']);
    }

    public function getTopUser()
    {
        $list = $this->model->getMemberList(10);
        return ajaxResponse($list,0,'');
    }






}

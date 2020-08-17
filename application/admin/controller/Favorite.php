<?php

namespace app\admin\Controller;

use app\admin\controller\Base;
use app\common\model\Favorite as FavoriteModel;
use think\Request;


class Favorite extends Base
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new FavoriteModel();
    }


    /**
     * Notes:关注列表
     * @auther: xxf
     * Date: 2019/7/9
     * Time: 15:13
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View|void
     */
    public function getFavoriteList(Request $request)
    {
        $user_id = $request->param('user_id', 0);
        if ($request->isAjax()) {
            $size = $request->param('size', 10);

            $where = array();
            $where[] = array('f.user_id', '=', $user_id);
            $list = $this->model->getList($size, $where);
            return ajaxResponse($list, 0);
        } else {
            $data = [
                'user_id' => $user_id,
            ];
            return view('favorite_list', $data);
        }
    }

}

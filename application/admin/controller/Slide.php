<?php

namespace app\admin\Controller;

use app\admin\controller\Base;
use app\common\model\Slide AS M;
use think\Request;

class Slide extends Base
{
    private $model;
    public function __construct()
    {
        parent::__construct();
        $this->model = new M();
    }

    /**
     * 获取文章列表数据
     * @param Request $request
     * @return \think\response\View
     */
    public function index(Request $request)
    {

        return view('index');
    }

    /**
     * @param Request $request
     */
    public function getSlideList(Request $request)
    {
        if ($request->isPost()) {
            $size = $request->post('size', 10);

            $list = $this->model->getList($size);
            return ajaxResponse($list, 0, 'success');
        } else {
            return showMsg(0, 'sorry，请求不合法');
        }

    }

    /**
     * 添加
     * @param Request $request
     * @return \think\response\View|void
     */
    public function addSlide(Request $request)
    {
        if ($request->isPost()) {
            $input = $request->param();
            $opRes = $this->model->add($input);
            return showMsg($opRes['tag'], $opRes['message']);
        } else {
            return view('add');
        }
    }

    /**
     * 更新
     * @param Request $request
     * @param $id
     * @return \think\response\View|void
     */
    public function editSlide(Request $request, $id)
    {
        if ($request->isPost()) {
            $opRes = $this->model->edit($request->param());
            return showMsg($opRes['tag'], $opRes['message']);
        } else {
            $data['info'] = $this->model->getInfoByID($id);
            return view('edit', $data);
        }
    }

    public function deleteSlide(Request $request)
    {
        $ids = $request->param('ids', 0);
        if (is_array($ids)) {
            $res = $this->model->deleteByIds($ids);
            $res && ajaxResponse($res, 0, '删除成功');
        }
        return ajaxResponse('', 1, '出错');
    }
}

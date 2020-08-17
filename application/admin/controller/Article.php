<?php

namespace app\admin\Controller;


use app\admin\controller\Base;

use app\common\model\Article AS M;
use app\common\model\Catalog;
use think\Request;

class Article extends Base
{
    private $model;
    private $catalogModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = new M();
        $this->catalogModel = new Catalog();
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
    public function getArticleList(Request $request)
    {
        if ($request->isPost()) {
            $size = $request->post('size', 10);
            $status = $request->post('status', 0);
            $keyword = $request->post('keyword', '');
            $where = [];
            $status > 0 && $where[] = array('a.status', '=', $status);
            if (!empty($keyword))
                $where[] = array('a.title', 'like', '%' . $keyword . '%');
            $list = $this->model->getArticleList($size, $where);
            return ajaxResponse($list, 0, 'success');
        } else {
            return showMsg(0, 'sorry，请求不合法');
        }

    }

    /**
     * 添加文章
     * @param Request $request
     * @return \think\response\View|void
     */
    public function add(Request $request)
    {
        if ($request->isPost()) {
            $input = $request->param();
            $opRes = $this->model->addArticle($input);
            return showMsg($opRes['tag'], $opRes['message']);
        } else {
            $data['catalog_list'] = $this->catalogModel->getCatalogTree();
            return view('add', $data);
        }
    }

    /**
     * 更新文章数据
     * @param Request $request
     * @param $id 文章ID
     * @return \think\response\View|void
     */
    public function edit(Request $request, $id)
    {
        if ($request->isPost()) {
            $opRes = $this->model->edit($request->param());
            return showMsg($opRes['tag'], $opRes['message']);
        } else {
            $data['article'] = $this->model->getInfoByID($id);
            $data['catalog_list'] = $this->catalogModel->getCatalogTree();
            return view('edit', $data);
        }
    }

    public function deleteArticle(Request $request)
    {
        $ids = $request->param('ids', 0);
        if (is_array($ids)) {
            $res = $this->model->deleteByIds($ids);
            $res && ajaxResponse($res, 0, '删除成功');
        }
        return ajaxResponse('', 1, '出错');
    }

    /**
     * Notes:完全删除
     * @auther: xxf
     * Date: 2019/8/13
     * Time: 15:01
     * @param Request $request
     */
    public function realDelete(Request $request)
    {
        $ids = $request->param('ids', 0);
        if (is_array($ids)) {
            $res = $this->model->delByIds($ids);
            $res && ajaxResponse($res, 0, '删除成功');
        }
        return ajaxResponse('', 1, '出错');
    }


    public function recoveryArticle(Request $request)
    {
        $ids = $request->param('ids', 0);
        if (is_array($ids)) {
            $res = $this->model->recoveryByIds($ids);
            $res && ajaxResponse($res, 0, '恢复成功');
        }
        return ajaxResponse('', 1, '出错');
    }


    public function getUserPublishList(Request $request)
    {
        $user_id = $request->param('user_id', 0);
        if ($request->isAjax()) {
            $size = $request->param('size', 10);
            $where[] = array('user_id', '=', $user_id);
            $list = $this->model->getUserPublishList($size, $where);
            return ajaxResponse($list, 0);
        } else {
            $data = [
                'user_id' => $user_id,
            ];
            return view('publish_list', $data);
        }
    }
}

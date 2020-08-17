<?php
namespace app\admin\Controller;
use app\admin\controller\Base;
use app\common\model\Collect as M;
use app\common\model\Catalog;
use think\Request;
use think\facade\Env;
use think\facade\Log;

class Collect extends Base
{
    private $model ;
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
    public function getCollectList(Request $request)
    {
        if ($request->isPost()) {
            $size = $request->post('size', 10);
            $keyword = $request->post('keyword', '');
            $status = $request->post('status', 0);
            $where = [];
            if (!empty($keyword))
                $where[] = array('title|weixin_nickname', 'like', '%' . $keyword . '%');
            if ($status > 0)
                $where[] = array('status', '=', $status);
            $list = $this->model->getList($size, $where);
            return ajaxResponse($list, 0, 'success');
        }

    }







    /**
     * 更新文章数据
     * @param Request $request
     * @param $id 文章ID
     * @return \think\response\View|void
     */
    public function edit(Request $request,$id){
        if ($request->isPost()){
            $opRes = $this->model->updateArticleData( $request->param());
            return showMsg($opRes['tag'],$opRes['message']);
        }else{
            $article = $this->model->getInfoByID($id);
            $data =
                [
                    'article'=>$article,
                ];
            return view('edit',$data);
        }
    }

    public function deleteCollect(Request $request)
    {
        $ids = $request->param('ids', 0);
        if (is_array($ids)) {
            $res = $this->model->deleteByIds($ids);
            $res && ajaxResponse($res, 0, '删除成功');
        }
        return ajaxResponse('', 1, '出错');
    }

    public function realDelete(Request $request)
    {
        $ids = $request->param('ids', 0);
        if (is_array($ids)) {
            $res = $this->model->realDeleteByIds($ids);
            $res && ajaxResponse($res, 0, '删除成功');
        }
        return ajaxResponse('', 1, '出错');
    }

    public function recoveryCollect(Request $request)
    {
        $ids = $request->param('ids', 0);
        if (is_array($ids)) {
            $res = $this->model->recoveryByIds($ids);
            $res && ajaxResponse($res, 0, '恢复成功');
        }
        return ajaxResponse('', 1, '出错');
    }


    public function filterCollect(Request $request)
    {
        $ids = $request->param('ids', 0);
        if (is_array($ids)) {
            $res = $this->model->filterByIds($ids);
            $res && ajaxResponse($res, 0, '成功');
        }
        return ajaxResponse('', 1, '出错');
    }


    /**
     * 采集的文章加入正式文章列表
     * @param Request $request
     * @param $id 文章ID
     * @return \think\response\View|void
     */
    public function publish(Request $request)
    {
        $id = $request->param('id',0);
        $article = $this->model->getInfoToPublish($id);
        $data['article'] = $article;
        $data['catalog_list'] = $this->catalogModel->getCatalogTree();
        return view('publish',$data);
    }

    public function getNewestArticle()
    {
        ignore_user_abort(true);
        $res = $this->model->getNewestArticle();
        echo "采集完成,共采集：".$res;
    }


}

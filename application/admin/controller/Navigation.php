<?php

namespace app\admin\Controller;

use app\admin\controller\Base;
use app\common\model\Navigation As M;
use app\common\model\Catalog;
use think\Request;

class Navigation extends Base
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
    public function index(){
        return view('index');
    }

    /**
     * @param Request $request
     */
    public function getMenuList(Request $request){
        if ($request->isPost()){
            $list = $this->model->getMenuTree();
            return ajaxResponse($list,0,'success');
        }else{
            return showMsg(0,'sorry，请求不合法');
        }

    }
    /**
     * 添加
     * @param Request $request
     * @return \think\response\View|void
     */
    public function add(Request $request){
        if($request->isAjax()){
            $input = $request->param();
            $opRes = $this->model->add($input);
            return showMsg($opRes['tag'],$opRes['message']);
        }else{
            $data['menu_list'] = $this->model->getMenuTree();
            $data['catalog_list'] = $this->catalogModel->getCatalogTree();
            return view('add',$data);
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
            $opRes = $this->model->edit( $request->param());
            return showMsg($opRes['tag'],$opRes['message']);
        }else{
            $data['info'] = $this->model->getInfoById($id);
            $data['menu_list'] = $this->model->getMenuTree();
            $data['catalog_list'] = $this->catalogModel->getCatalogTree();
            return view('edit',$data);
        }
    }


    public function deleteMenu(Request $request)
    {
        $ids = $request->param('ids',0);
        if (is_array($ids))
        {
            $res = $this->model->deleteByIds($ids);
            $res && ajaxResponse($res,0,'删除成功');
        }
        return ajaxResponse('',1,'出错');
    }
}

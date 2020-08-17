<?php

namespace app\admin\Controller;

use app\admin\controller\Base;

use app\common\model\Catalog as M;
use think\Request;

class Catalog extends Base
{
    private $model ;
    public function __construct()
    {
        parent::__construct();
        $this->model = new M();
    }




    public function index(){
        return view('index');
    }

    /**
     * @param Request $request
     */
    public function getCatalogList(Request $request){

            $list = $this->model->getCatalogTree();
            return ajaxResponse($list,0,'success');
    }
    /**
     * 添加文章
     * @param Request $request
     * @return \think\response\View|void
     */
    public function add(Request $request){
        if($request->isPost()){
            $input = $request->param();
            $opRes = $this->model->addCatalog($input);
            return showMsg($opRes['tag'],$opRes['message']);
        }else{
            $data['catalog'] = $this->model->getCatalogTree();
            return view('add',$data);
        }
    }

    /**
     * 更新文章数据
     * @param Request $request
     * @param $id 文章ID
     * @return \think\response\View|void
     */
    public function edit(Request $request){
        if ($request->isPost()){
            $opRes = $this->model->updateCatalog( $request->param());
            return showMsg($opRes['tag'],$opRes['message']);
        }else{
            $data['info'] = $this->model->getInfoByID($request->param('id',0));
            $data['catalog'] = $this->model->getCatalogTree();
            return view('edit',$data);
        }
    }


    public function deleteCatalog(Request $request)
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

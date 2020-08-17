<?php

namespace app\admin\Controller;

use app\admin\controller\Base;
use app\common\model\TitleFilter as M;
use think\Request;


class TitleFilter extends Base
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new M();
    }

    public function index(){
        return view('index');
    }


    public function getFilterList(Request $request)
    {
        if ($request->isAjax()) {
            $size = $request->param('size', 10);

            $list = $this->model->getList($size);
            return ajaxResponse($list, 0);
        }
    }

    public function addFilter(Request $request){
        if($request->isPost()){
            $keyword = $request->param('keyword','');
            if (empty($keyword))
                return showMsg(0,'不能为空');
            $opRes = $this->model->add($keyword);
            return showMsg($opRes['tag'],$opRes['message']);
        }else{
            return view('add');
        }
    }

    public function deleteFilter(Request $request)
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

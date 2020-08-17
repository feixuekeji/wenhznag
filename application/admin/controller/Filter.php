<?php

namespace app\admin\Controller;

use app\admin\controller\Base;
use app\common\model\Filter as M;
use think\Request;


class Filter extends Base
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
            $type = $request->param('type',1);
            if (empty($keyword))
                return showMsg(0,'不能为空');
            $opRes = $this->model->add($keyword,$type);
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

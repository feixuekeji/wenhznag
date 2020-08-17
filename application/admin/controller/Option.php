<?php

namespace app\admin\Controller;

use app\admin\controller\Base;
use app\common\model\Option as M;
use think\Request;

/**
 * Notes:系统配置
 * User: xxf
 * Date: 2019/7/16
 * Time: 17:06
 * Class Option
 * @package app\admin\Controller
 */
class Option extends Base
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new M();
    }


    public function index(Request $request)
    {
        $data['list'] = $this->model->getList();
        return view('index', $data);
    }


    public function editOption(Request $request)
    {
        $data = $request->param('data');
        $opRes = $this->model->edit($data);
        return showMsg($opRes['tag'],$opRes['message']);

    }


}

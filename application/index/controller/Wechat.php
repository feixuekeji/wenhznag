<?php

namespace app\index\controller;

use app\common\model\Subscriptions as M;
use think\Request;
use app\common\model\Article;
use think\facade\Cache;
use WordAnalysis\Analysis;

/**
 * Notes:公众号控制器
 * User: xxf
 * Date: 2019/7/24
 * Time: 16:38
 * Class Wechat
 * @package app\index\controller
 */
class Wechat extends Base
{
    private $model;


    public function __construct()
    {
        parent::__construct(false);
        $this->model = new M();

    }

    public function index(Request $request)
    {

        $size = $request->param('size',10);
        $where = [];
        $data['seo']['title'] = "公众号列表";
        $data['list'] = $this->model->getList($size,$where);
        $data['top_list'] = $this->model->getTopList($size,$where);
        return view('index', $data);
    }


    /**
     * Notes:获取公众号发布的文章
     * @auther: xxf
     * Date: 2019/7/24
     * Time: 17:25
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function getArticle(Request $request)
    {
        $size = $request->param('size',10);
        $wechat_id = $request->param('wechat_id',0);
        $data['info'] = $info = $this->model->getInfoByID($wechat_id);
        empty($data['info']) && $this->error('公众号不存在');
        $article_model = new Article();
        $data['list'] = $article_model->getArticlesByWechatId($size,$wechat_id);
        $data['top_list'] = $this->model->getTopList($size);
        $data['seo']['title'] = $info->name ?? "";
        $data['seo']['keyword'] = Analysis::getKeywords($info->name);
        $data['seo']['description'] = $info->profile ?? "";
        return view('list', $data);
    }


}

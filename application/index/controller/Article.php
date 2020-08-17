<?php

namespace app\index\controller;

use app\common\model\Article as M;
use app\common\model\Catalog;
use think\facade\Cache;
use think\facade\Env;
use think\Request;
use WordAnalysis\Analysis;

class Article extends Base
{
    private $model;
    private $catalog_model;
    public function __construct()
    {
        parent::__construct(false);
        $this->model = new M();
        $this->catalog_model = new Catalog();
    }





    public function getUserArticleList(Request $request)
    {
        global $_W;
        $data['seo']['title'] = "个人中心";
        $user_id = $request->param('user_id',0);
        if (empty($user_id))
            $user_id = $_W['user']['id'];
        $user_id == $_W['user']['id'] && $data['is_me'] = 1;//查询自己

            $size = $request->param('size', 10);
            $where = [];
            $where[] = array('a.status', '=', 1);
            $where[] = array('a.user_id', '=', $user_id);
            $data['list'] = $this->model->getArticleList($size, $where);
            $data['user_id'] = $user_id;
            return view('user_article_list',$data);
    }

    public function addArticle(Request $request)
    {
        global $_W;
        $this->checkToken();
        $user_id = $_W['user']['id'];
        if ($request->isAjax())
        {
            $data = $request->param();
            $data['user_id'] = $user_id;
            $data['tag'] = implode(',',$data['tag']);
            $opRes = $this->model->addArticle($data);
            return ajaxResponse('',$opRes['tag'] ^ 1,$opRes['message']);
        }
        $data['seo']['title'] = "发布文章";
        $data['catalog_list'] = $this->catalog_model->getCatalogTree();
        $data['user_id'] = $user_id;
        return view('add',$data);

    }

    public function protocol()
    {
        return view('protocol');
    }

    public function detail(Request $request)
    {
        $id = $request->param('id',0);
        $this->model->addView(intval($id));
        $article_info = Cache::store('default')->get('article'.intval($id));
        if (!$article_info){
            $article_info = $this->model->getDetailByID(intval($id));
            if ($article_info)
                Cache::store('default')->set('article'.$id,$article_info->toArray(),3600);
        }
        if (empty($article_info))
            $this->error('文章不存在', 'index/index/index');
        $catalog_id = $article_info['catalog_id'];
        $data['seo']['title'] = $article_info['title'];
        //$data['seo']['keyword'] = $article_info['title'];
        $data['seo']['keyword'] = Analysis::getKeywords($article_info['title']);
        $data['seo']['description'] = $article_info['abstract'] ?? $article_info['title'];
        $data['info'] = $article_info;
        $data['preAndNext'] = $this->model->getPreAndNext($id, $catalog_id);
        $data['catalog_path'] = $this->catalog_model->getCatalogPath($catalog_id);
        $data['hot_list'] = $this->model->getHotArticleList();
        $data['like_list'] = $this->model->getLikeList($catalog_id);
        $data['catalog_list'] = $this->catalog_model->getCatalogByPid(0,20);


        return view('detail',$data);
    }

    /**
     * Notes:根据分类获取文章
     * @auther: xxf
     * Date: 2019/7/25
     * Time: 9:56
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function getArticleList(Request $request)
    {

        $size = $request->param('size',10);
        $catalog_id = $request->param('catalog_id',0);
        if ($catalog_id > 0)
            $where[] = array('a.catalog_id', '=', $catalog_id);
        $where[] = array('a.status', '=', 1);
        $catalog_info = $this->catalog_model->getInfoByID($catalog_id);
        $data['seo']['title'] = $catalog_info->seo_title ?? "文章列表";
        $data['seo']['keyword'] = $catalog_info->seo_keywords ?? '';
        $data['seo']['keyword'] = $catalog_info->seo_keywords ?? '';
        $data['seo']['description'] = $catalog_info->seo_description ?? '';
        $data['catalog_path'] = $this->catalog_model->getCatalogPath($catalog_id);
        $data['list'] = $this->model->getListArticleAndImage($size,$where);
        $data['like_list'] = $this->model->getLikeList($catalog_id);
        $data['catalog_list'] = $this->catalog_model->getCatalogByPid(0,20);
        return view('list',$data);

    }

    /**
     * Notes:搜索列表
     * @auther: xxf
     * Date: 2019/7/31
     * Time: 14:18
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function getSearchList(Request $request)
    {
        $size = $request->param('size',15);
        $keyword = $request->param('keyword','');
        $where = array();
        $where[] = array('a.title', 'like', '%'.$keyword.'%');
        $data['seo']['title'] = "搜索列表";
        $data['list'] = $this->model->getArticlesForPage($size,$where);
        $data['keyword'] = $keyword;
        return view('search',$data);
    }










}

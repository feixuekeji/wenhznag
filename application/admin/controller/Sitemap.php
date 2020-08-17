<?php

namespace app\admin\Controller;

use app\admin\controller\Base;
use app\common\model\Catalog;
use think\Request;
use unit\SiteMap as M;

/**
 * Notes:网站地图
 * User: xxf
 * Date: 2019/7/31
 * Time: 19:29
 * Class Sitemap
 * @package app\admin\Controller
 */
class Sitemap extends Base
{
    private $catalogModel;
    private $sitemap;
    public function __construct()
    {
        parent::__construct();
        $this->catalogModel = new Catalog();
        $this->sitemap = new M();
    }


    public function createMap(Request $request)
    {
        $host = 'http://'.$request->host();
        $catalog_list = $this->catalogModel->getCatalogByPid(0);
        foreach ($catalog_list as $k => $v)
        {
            $url = $host.url('index/article/getArticleList',['catalog_id'=>$v['id']]);
            $this->sitemap->AddItem($url,1);
        }
        $res = $this->sitemap->SaveToFile('sitemap.xml');
        if ($res)
            return "站点地图生成成功";
    }

}

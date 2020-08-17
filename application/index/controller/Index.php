<?php

namespace app\index\controller;

use app\common\model\Subscriptions;
use app\common\model\Article;
use app\common\model\Catalog;
use app\common\model\Keywords;
use app\common\model\Slide;
use think\facade\Log;
use think\Request;
use think\facade\Cache;

class Index extends Base
{
    private $articleModel;
    private $catalogModel;
    private $slideModel;
    private $wechatModel;
    public function __construct()
    {
        parent::__construct(false);
        $this->articleModel = new Article();
        $this->catalogModel = new Catalog();
        $this->slideModel = new Slide();
        $this->wechatModel = new Subscriptions();
    }

    /**
     *
     * @return \think\response\View
     */



    public function index()
    {
        $slide_list = $this->slideModel->getListByWhere(10,['type' => 1]);
        $wechat_list = $this->wechatModel->getTopList(9);
        $catalog_list = $this->catalogModel->getCatalogByPid(0);
        $list = array();
        foreach ($catalog_list as $k => $v)
        {
            $list[$k]['catalog_id'] = $v['id'];
            $list[$k]['catalog_name'] = $v['name'];
            if ($v['id']> 0)
                $where['a.catalog_id'] = array( '=', $v['id']);
            $list[$k]['list'] = $this->articleModel->getArticlesForPage(10,$where);
        }
        $data = array(
            'slide_list' => $slide_list,
            'wechat_list' => $wechat_list,
            'list' => $list,
        );
        return view('index',$data);
    }


    public function test()
    {
//        $str="as2223adfsf0s4df0sdfsdf";
//        echo preg_replace("/0/","",$str);//去掉0字符，此时相当于 replace的功能, preg_replace("/0/","A",$str); 这样就是将0变成A的意思了
//        echo preg_replace("/[0-9]/","",$str);//去掉所有数字
//        echo preg_replace("/[a-z]/","",$str); //这样是去掉所有小写字母
//        echo preg_replace("/[A-Z]/","",$str); //这样是去掉所有大写字母
//        echo preg_replace("/[a-z,A-Z]/","",$str); //这样是去掉所有字母
        $str="as2223adfsAAf0s4df0s中国人dD中南海DDfsdf";
        echo preg_replace("/[a-z,A-Z,0-9]/","",$str); //去掉所有字母和数字
        echo "</br>";
        $pattern[] = "/0/";
        $pattern[] = "#s中国人#";
        echo preg_replace($pattern,"",$str); //去掉所有字母和数字
        echo "</br>";
        echo $str;
    }

    public function ttt()
    {
        $a = rand(1, 2);

        echo $a;
    }





    public function test1()
    {
        $number = 'php判断字符串中是否包含某个关键字';
        $result = mb_substr($number,mb_strripos($number,"判断字符串11"));
        echo $result;
    }




}

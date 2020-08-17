<?php
namespace unit;
use app\common\model\Option;

/**神箭手相关API
 * Class Shenjianshou
 * @package app\api\Controller
 */
class Shenjianshou
{
    private $newestApiUrl;
    private $infoUrl;

    public function __construct()
    {
        $this->newestApiUrl = Option::get('api_newest_url')->option_value;//最新文章
        $this->infoUrl = Option::get('api_article_info_url')->option_value;//阅读量点击数
    }


    /**获取最新文章
     * @param $weixinId
     * @return mixed
     */
    public function getNewArticle($weixinId)
    {

        $url = $this->newestApiUrl;
        $weixinId=urlencode($weixinId);
        $url = $url . "&weixinId=" . $weixinId;
        $res = curl($url);
        $res = json_decode($res,true);
        return $res;

    }


    /**获取文章内容
     * @param $weixinId
     * @return mixed
     */
    public function getInfo($article_url)
    {

        $url = $this->infoUrl;
        $article_url=urlencode($article_url);
        $url = $url . "&url=" . $article_url;
        $res = curl($url);
        $res = json_decode($res,true);
        return $res;
    }



}

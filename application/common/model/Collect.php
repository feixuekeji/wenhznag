<?php

namespace app\common\model;

use QiNiuUpload\Upload;
use Think\Exception;
use think\facade\Log;
use think\Model;
use unit\Shenjianshou;

class Collect extends Model
{
    protected $autoWriteTimestamp = true;

    public function apiPost($param)
    {

        $sign = $param['__dataKey'] ?? '';
        $is_exist = $this->where('sign', $sign)->find();
        if (!empty($is_exist))
            return ["result" => 2, "data" => "已经发布过"];
        $wechat_model = new Subscriptions();
        $wechat_info = $wechat_model->whereOr(['account' => $param['weixin_name'], 'name' => $param['weixin_nickname']])->find();
        $wechat_id = $wechat_info['id'] ?? 0;
        //过滤关键字
        $content = $this->filter($param['article_content']);
        $status = $this->titleFilter($param['article_title']);
        $this->startTrans();
        try {
            //添加公众号
            if (empty($wechat_info)) {
                $wechat_data = array(
                    'name' => $param['weixin_nickname'],
                    'account' => $param['weixin_name'],
                    'profile' => $param['weixin_introduce'],
                    'is_collection' => 0,
                    'avatar' => $param['weixin_avatar'] ?? '',
                    'create_time' => time(),
                );
                $wechat_id = $wechat_model->insertGetId($wechat_data);
            }
            $data = array(
                'title' => $param['article_title'],
                'content' => $content,
                'desc' => $param['article_brief'],
                'url' => $param['weixin_tmp_url'],
                'weixin_id' => $param['weixin_name'],
                'weixin_nickname' => $param['weixin_nickname'],
                'weixin_introduce' => $param['weixin_introduce'],
                'weixin_avatar' => $param['weixin_avatar'],
                'picture' => $param['article_thumbnail'],
                'publish_time' => $param['article_publish_time'],
                'create_time' => time(),
                'sign' => $sign,
                'wechat_id' => $wechat_id,
                'status' => $status,

            );
            $res = $this->save($data);
            $this->commit();
            if ($res)
                return ["result" => 1, "data" => "发布成功"];
            else
                return ["result" => 2, "data" => "异常"];
        } catch (\Exception $e) {
            $this->rollback();
            return ["result" => 2, "data" => $e->getMessage()];
        }


    }


    /**
     * 根据文章ID 获取文章详情
     * @param $id
     * @return array
     */
    public function getInfoByID($id)
    {
        $res = $this
            ->where('id', $id)
            ->find();
        return $res;
    }

    public function getInfoToPublish($id)
    {
        $res = $this
            ->where('id', $id)
            ->find();
        if ($res)
            $res->content = $this->filter($res->content);
        return $res;
    }


    /**
     * 后台获取文章数据列表
     * @param $curr_page
     * @param int $limit
     * @param null $search
     * @return array
     */
    public function getList($size = 10, $where = null)
    {
        $res = $this
            ->field('*')
            ->withAttr('publish_time', function ($value, $data) {
                return date("Y-m-d H:i", $value);
            })
            ->where($where)
            ->order(['id' => 'desc'])
            ->paginate($size);
        return $res;
    }


    /**
     * 更新文章内容
     * @param $input
     * @return array
     */
    public function updateArticleData($input)
    {

        $id = $input['id'];

        $saveData = [
            'title' => $input['title'],
            'content' => isset($input['content']) ? $input['content'] : '',
        ];

        $saveTag = $this
            ->where('id', $id)
            ->update($saveData);
        $validateRes['tag'] = $saveTag;
        $validateRes['message'] = $saveTag ? '修改成功' : '数据无变动';

        return $validateRes;
    }

    public function deleteByIds($ids)
    {
        $res = $this
            ->where('id', 'in', $ids)
            ->update(['status' => 2]);
        return $res;
    }


    /**
     * Notes:连同七牛图片删除
     * @auther: xxf
     * Date: 2019/8/13
     * Time: 16:17
     * @param $ids
     * @return int
     */
    public function realDeleteByIds($ids)
    {
        $list = $this
            ->field('content')
            ->where('id', 'in', $ids)
            ->select();
        $images = [];
        foreach ($list as $k => $v) {
            $images = array_merge($images, getQiNiuImages($v['content']));
        }
        //删除七牛图片
        $qiniu = new Upload();
        $qiniu->deleteFiles($images);
        $res = $this
            ->where('id', 'in', $ids)
            ->delete();
        return $res;
    }

    public function recoveryByIds($ids)
    {
        $res = $this
            ->where('id', 'in', $ids)
            ->update(['status' => 1]);
        return $res;
    }


    public function filterByIds($ids)
    {
        $list = $this
            ->field('id,content')
            ->where('id', 'in', $ids)
            ->select();
        foreach ($list as $k => $v) {
            $content = $this->filter($v['content']);
            if ($content != $v['content'])
                $this->where('id', $v['id'])->update(['content' => $content]);
        }
        return true;
    }


    /**
     * Notes:文本过滤
     * @auther: xxf
     * Date: 2019/7/30
     * Time: 10:11
     * @param $content
     * @return mixed
     */
    public function filter($content)
    {
        $filter_model = new Filter();
        $filter_list = $filter_model->getAll();
        $result = $content;
        $pattern = [];
        try {
            foreach ($filter_list as $k => $v) {
                //按前中后过滤，之间用正则
                switch ($v['type']) {
                    case 1:
                        $result = get_behind_string($result, $v['keyword']);
                        break;

                    case 2:
                        //$pattern[] = $v['keyword'];
                        $result = str_replace($v['keyword'], '', $result);
                        break;

                    case 3:
                        //$result = get_front_string($result,$v['keyword']);
                        $string = mb_substr($result, 0, mb_strpos($result, $v['keyword']));
                        $result = empty($string) ? $result : $string;
                        break;
                }

            }
            //!empty($pattern) && $result = preg_replace($pattern, "", $result);
            return $result;

        } catch (\Exception $e) {
            return $result;
        }

    }

    /**
     * Notes:标题过滤
     * @auther: xxf
     * Date: 2019/8/5
     * Time: 18:59
     * @param $title
     * @return int
     */
    private function titleFilter($title)
    {
        $title_filter_model = new TitleFilter();
        $filter_list = $title_filter_model->getAll();
        foreach ($filter_list as $k => $v) {
            if (is_contain($title, $v['keyword']))
                return 2;
        }
        return 1;
    }

    /**
     * Notes:采集公众号最新文章
     * @auther: xxf
     * Date: 2019/7/31
     * Time: 18:46
     * @return int
     */
    public function getNewestArticle($wechat_ids = [])
    {
        try {
            /*
        * 获取所有文章链接，再根据链接获取文章详情
        */
            $wechat_model = new Subscriptions();
            $shenjianshou = new Shenjianshou();
            if (empty($wechat_ids))
                $wechat_list = $wechat_model->where(['is_collection' => 1])->select();
            else
                $wechat_list = $wechat_model->where('id', 'in', $wechat_ids)->select();
            $url_list = [];
            foreach ($wechat_list as $k => $v) {
                $info = $shenjianshou->getNewArticle($v['account']);
                if ($info['error_code'] == 0) {
                    $articleList = $info['data']['articles'];
                    foreach ($articleList AS $item => $value) {
                        if (!$this->isExist($value['article_title'])) {
                            $url_list[] = ['weixin_id' => $v['id'], 'article_url' => $value['article_url']];

                        }

                    }
                } else {
                    Log::info('最新文章API异常：', $info);

                }
            }
            $num = 0;
            foreach ($url_list as $k1 => $v1) {
                $num++;
                if ($num % 10 == 0)
                    ob_flush();
                $detail = $shenjianshou->getInfo($v1['article_url']);//获取文章详情
                if ($detail['error_code'] == 0)
                    $this->addByWechatId($v1, $detail['data']);
            }
            return $num;
        } catch (\Exception $e) {
            return $e->getMessage();
        }


    }


    //判断是否存在
    public function isExist($title)
    {

        $res = $this
            ->where('title', $title)
            ->find();
        if (!empty($res))
            return true;
        return false;


    }


    public function addByWechatId($wechat_id, $param)
    {
        //过滤关键字
        $content = $this->filter($param['article_content']);
        $status = $this->titleFilter($param['article_title']);
        $data = array(
            'title' => $param['article_title'],
            'content' => $content,
            'desc' => $param['article_desc'],
            'url' => $wechat_id['article_url'],
            'weixin_id' => $param['weixin_id'],
            'weixin_nickname' => $param['weixin_nickname'],
            'weixin_introduce' => '',
            'weixin_avatar' => '',
            'picture' => $param['article_cdn_url'],
            'publish_time' => $param['article_publish_time'],
            'create_time' => time(),
            'sign' => 0,
            'wechat_id' => $wechat_id['weixin_id'],
            'status' => $status,
        );
        $res = $this->insert($data);
    }


    public function test()
    {
        return 12344;
    }
}
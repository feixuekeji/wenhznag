<?php

namespace app\common\model;

use app\common\validate\Catalog as Verify;
use think\Db;
use \think\Model;


class Catalog extends BaseModel
{

    protected $validate;
    protected $autoWriteTimestamp = true;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->validate = new Verify();
    }

    /**
     * 获取所有
     * @return array
     */
    public function getCatalogList()
    {
        $data = $this
            ->order(['pid' => 'asc', 'list_order' => 'desc', 'id' => 'asc'])
            ->select()
            ->toArray();
        return $data;
    }

    public function getCatalogByPid($parent_id = 0, $limit = null)
    {
        if ($limit > 0) {
            $data = $this
                ->where('parent_id', $parent_id)
                ->order(['list_order' => 'desc'])
                ->limit($limit)
                ->select();
        } else {
            $data = $this
                ->where('parent_id', $parent_id)
                ->order(['list_order' => 'desc'])
                ->select();
        }

        return $data;
    }

    public function getOneLevelCatalog($parent_id = 0)
    {
        $data = $this
            ->where('parent_id', $parent_id)
            ->select();
        return $data;
    }


    /**
     * 根据ID 获取内容
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

    /**
     * 更新
     * @param $input
     * @return array
     */
    public function updateCatalog($input)
    {

        $id = $input['id'];


        if (empty($input['parent_id']))
            $path = '0-' . $id;
        else {
            $parentPath = $this->where('id', intval($input['parent_id']))->value('path');
            $path = "$parentPath-$id";
        }
        $saveData = [
            'name' => $input['name'] ?? '',
            'parent_id' => $input['parent_id'] ?? 0,
            'list_order' => $input['list_order'] ?? '',
            'seo_title' => $input['seo_title'] ?? '',
            'seo_keywords' => $input['seo_keywords'] ?? '',
            'seo_description' => $input['seo_description'] ?? '',
            'image' => $input['image'] ?? '',
            'status' => $input['status'] ?? 1,
            'path' => $path,

        ];
        $tokenData = ['__token__' => isset($input['__token__']) ? $input['__token__'] : '',];
        $validateRes = $this->validate($this->validate, $saveData, $tokenData);
        if ($validateRes['tag']) {
            $saveTag = $this
                ->where('id', $id)
                ->update($saveData);
            $validateRes['tag'] = $saveTag;
            $validateRes['message'] = $saveTag ? '修改成功' : '数据无变动';
        }
        return $validateRes;
    }

    /**
     * 分类添加
     * @param $data
     * @return array
     */

    public function addCatalog($input)
    {
        $this->startTrans();
        try {
            $addData = [
                'name' => $input['name'] ?? '',
                'parent_id' => $input['parent_id'] ?? 0,
                'list_order' => $input['list_order'] ?? '',
                'seo_title' => $input['seo_title'] ?? '',
                'seo_keywords' => $input['seo_keywords'] ?? '',
                'seo_description' => $input['seo_description'] ?? '',
                'image' => $input['image'] ?? '',
                'status' => $input['status'] ?? 1,

            ];
            $tokenData = ['__token__' => isset($input['__token__']) ? $input['__token__'] : '',];
            $validateRes = $this->validate($this->validate, $addData, $tokenData);
            if ($validateRes['tag']) {
                $tag = $this->save($addData);
                $id = $this->id;
                if (empty($input['parent_id'])) {

                    $this->where(['id' => $id])->update(['path' => '0-' . $id]);
                } else {
                    $parentPath = $this->where('id', intval($input['parent_id']))->value('path');
                    $this->where(['id' => $id])->update(['path' => "$parentPath-$id"]);

                }
                $validateRes['tag'] = $tag;
                $validateRes['message'] = $tag ? '添加成功' : '添加失败';
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            $validateRes['tag'] = 0;
            $validateRes['message'] = $e->getMessage();

        }
        return $validateRes;


    }


    public function deleteByIds($ids)
    {
        $res = $this
            ->where('id', 'in', $ids)
            ->delete();
        return $res;
    }

    /**树形列表
     * @return array
     */
    public function getCatalogTree()
    {
        $cateres = $this->select();
        return $this->sort($cateres);
    }

    public function sort($data, $pid = 0, $level = 0)
    {
        static $arr = array();
        foreach ($data as $k => $v) {
            if ($v['parent_id'] == $pid) {
                $v['level'] = $level;
                $arr[] = $v;
                $this->sort($data, $v['id'], $level + 1);
            }
        }
        return $arr;
    }


    /**
     * Notes:格式化分类
     * User: xxf
     * Date: 2019/3/14
     * Time: 17:04
     * @param $action
     * @param int $parent_id
     * @return array
     */
    private function menuFormat($action, $parent_id = 0)
    {
        $tmp = [];
        foreach ($action as $k => $v) {
            if ($v['pid'] == $parent_id) {
                $tmp[] = $v;
                unset($action[$k]);
            }
        }

        if (!empty($action)) {
            foreach ($tmp as $k => $v) {
                $children = $this->menuFormat($action, $v['id']);
                if (!empty($children)) {
                    $tmp[$k]['children'] = $children;
                }
            }
        }
        return $tmp;
    }


    /**
     * Notes:菜单列表
     * User: xxf
     * Date: 2019/3/14
     * Time: 17:03
     * @return array
     */
    public function getMenu()
    {

        $list = $this->getCatalogList();
        $menu = $this->menuFormat($list);
        return $menu;
    }

    public function getMenuList()
    {

        $list = $this->getCatalogByPid(0, 6);
        if ($list) {
            foreach ($list as $k => $v) {
                $children_list = $this->getCatalogByPid($v['id']);
                if ($children_list) {
                    $list[$k]['children'] = $children_list;
                }
            }
        }
        return $list;
    }


    /**
     * Notes:文章当前路径
     * @auther: xxf
     * Date: 2019/7/23
     * Time: 9:58
     * @param $id
     * @return array
     */
    public function getCatalogPath($id)
    {
        $path_info = $this->where('id',$id)->find();
        if (empty($path_info->path))
            return [];
        $arr = explode('-',$path_info->path);
        array_shift($arr);//去掉0
        $path_name = [];
        foreach ($arr as $k => $v)
        {
            $path_name[$k]['id'] = $v;
            $path_name[$k]['name'] = $this->get($v)->getData('name') ?? '';
        }
        return $path_name;

    }


}
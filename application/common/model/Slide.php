<?php
namespace app\common\model;
use think\Model;

/**
 * Notes:幻灯片模型
 * User: xxf
 * Date: 2019/7/15
 * Time: 15:55
 * Class Link
 * @package app\common\model
 */
class Slide extends Model
{

    /**
     * Notes:列表
     * @auther: xxf
     * Date: 2019/7/15
     * Time: 15:55
     * @param $limit
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($limit){
        $res = $this
            ->order('list_order','desc')
            ->paginate($limit);
        return $res;
    }


    /**
     * Notes:批量删除
     * Date: 2019/7/8
     * Time: 15:59
     * @param $ids
     * @return int|string|static
     */
    public function deleteByIds($ids)
    {
        $res = $this
            ->where('id','in',$ids)
            ->delete();
        return $res;
    }


    public function add($data)
    {

            $addData = [
                'list_order' => $data['list_order'] ?? 0,
                'url' => $data['url'] ?? '',
                'name' => $data['name'] ?? '',
                'image' => $data['image'] ?? '',
                'description' => $data['description'] ?? '',
                'type' => $data['type'] ?? 1,
            ];
            $tag = $this->save($addData);
            $validateRes['tag'] = $tag;
            $validateRes['message'] = $tag ? '添加成功' : '添加失败';
        return $validateRes;
    }


    public function edit($data)
    {
        $id = $data['id'] ?? 0;
        $Data = [
            'list_order' => $data['list_order'] ?? 0,
            'url' => $data['url'] ?? '',
            'name' => $data['name'] ?? '',
            'image' => $data['image'] ?? '',
            'description' => $data['description'] ?? '',
            'type' => $data['type'] ?? 1,
        ];
        $saveTag = $this
            ->where('id', $id)
            ->update($Data);
        $validateRes['tag'] = $saveTag;
        $validateRes['message'] = $saveTag ? '修改成功' : '数据无变动';
        return $validateRes;
    }


    public function getInfoByID($id){
        $res = $this
            ->where('id',$id)
            ->find();
        return $res;
    }


    public function getListByWhere($size,$where = null)
    {
        $res = $this
            ->where($where)
            ->order('list_order','desc')
            ->paginate($size);
        return $res;
    }


}

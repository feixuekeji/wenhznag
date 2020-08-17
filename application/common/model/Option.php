<?php
namespace app\common\model;
use think\Model;
class Option extends Model
{
    //设置主键:TP5.1不会自动获取主键,必须主动设置
    protected $pk = 'option_name';

    /**
     * Notes:列表
     * @auther: xxf
     * Date: 2019/7/9
     * Time: 14:41
     * @param $limit
     * @param $where
     * @return \think\Paginator
     */
    public function getList(){
        $res = $this
            ->select();
        return $res;
    }

    /**
     * Notes:获取配置信息
     * @auther: xxf
     * Date: 2019/7/16
     * Time: 17:29
     * @param $key
     * @return array|\Illuminate\Cache\CacheManager|mixed
     */
    public static function cmf_get_option($key)
    {
        if (!is_string($key) || empty($key)) {
            return [];
        }
        $optionValue = cache('options_' . $key);

        if (empty($optionValue)) {
            $optionValue = self::where('option_name', $key)->value('option_value');
            if (!empty($optionValue)) {
                cache('options_' . $key, $optionValue);
            }
        }
        return $optionValue;
    }

    public function edit($data)
    {
        $count = 0;
        foreach ($data as $k =>$v) {

            $res = $this->where('option_name',$k)->update(['option_value'=>$v]);
            $res && $count++;
        }

        $validateRes['tag'] = $count > 0;
        $validateRes['message'] = $count > 0 ? '成功' : '失败';
        return $validateRes;
    }


}

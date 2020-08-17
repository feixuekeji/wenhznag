<?php
/**
 * Created by PhpStorm.
 * User: moTzxx
 * Date: 2018/10/25
 * Time: 9:55
 */
namespace app\common\validate;
use \think\Validate;
class Navigation extends Validate
{
    protected $rule = [
        'name'         =>  'require|max:100',
        'list_order'    =>  'require|number',
        'parent_id'    =>  'require|number',

        '__token__'     =>  'require|token',
    ];
    protected $message  =   [
        'name.max'     =>  '标题不能超过100个字符',
        'name.require' =>   '标题不能为空',
        'list_order'    =>  '排序权重为整数',
        'parent_id'    =>  '分类不能为空',
        '__token__'     =>  'Token非法操作或失效',
    ];

    protected $scene = [
        'default'  =>  ['name','list_order','parent_id'],
        'token'    =>  ['__token__'],
    ];
}
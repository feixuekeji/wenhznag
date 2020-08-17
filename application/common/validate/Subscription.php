<?php
/**
 * Created by PhpStorm.
 * User: moTzxx
 * Date: 2018/10/25
 * Time: 9:55
 */
namespace app\common\validate;
use \think\Validate;
class Subscription extends Validate
{
    protected $rule = [
        'name'         =>  'require|max:30',
        'account'    =>  'require',
        'list_order'    =>  'number',
        'profile'    =>  'require',
        '__token__'     =>  'require|token',
    ];
    protected $message  =   [
        'name.max'     =>  '标题不能超过30个字符',
        'name.require' =>   '标题不能为空',
        'account'    =>  '微信公众号不能为空',
        'list_order'    =>  '排序不能为空',
        'profile'    =>  '简介不能为空',
        '__token__'     =>  'Token非法操作或失效',
    ];

    protected $scene = [
        'default'  =>  ['name','account','list_order','profile'],
        'token'    =>  ['__token__'],
    ];
}
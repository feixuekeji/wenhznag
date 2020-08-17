<?php
/**
 * Created by PhpStorm.
 * User: moTzxx
 * Date: 2018/11/20
 * Time: 17:18
 */

namespace app\common\validate;


use think\Validate;

class User extends Validate
{

    protected $rule = [
        'user_name'    =>  'require|max:100',
        'avatar'      =>  'require',
        'role_id'      =>  'number',
        '__token__'    =>  'require|token',

    ];
    protected $message  =   [
        'user_name.require'  =>  '管理员不能为空',
        'user_name.max'      =>  '管理员名称不能超过100个字符',
        'avatar'            =>  '图片不能为空',
        'role_id'            =>  '角色不能为空',
        '__token__'          =>  'Token非法操作或失效',
    ];
    /**
     * 定义情景
     * @var array
     */
    protected $scene = [
        'default'  =>  ['user_name','avatar','role_id'],
        'token'    =>  ['__token__'],
        'cms_admin'=>  ['user_name','avatar'],
        'user'=>  ['nickname','avatar']
    ];
}
<?php

namespace app\admin\controller;

use app\common\model\User;
use think\Controller;
use think\Db;
use think\facade\Session;
use think\Request;

/**
 * 此类主要用于后台控制类的初始化操作
 * Class Base
 * @package app\common\controller
 */
class Base extends Controller
{
    /**
     * 初始化处理数据
     * Base constructor.
     */
    public function __construct()
    {
        $this->initAuth();
    }

    /**
     * 进行权限控制
     */
    public function initAuth(){
        $authFlag = false;
        $hasCmsAID = Session::has('AID');
        if (!$hasCmsAID){
            $message = "您还未登录!";
        }else{
            $AID = Session::get('AID');
            // 判断当前用户是否具有此操作权限
            $checkAuth = $this->checkCmsAdminAuth($AID);
            $authFlag = $checkAuth;
            $message = $checkAuth?"权限正常":"没有权限";
        }

        /**
         * debug
         */
        $authFlag = 1;
        if (!$authFlag) {return showMsg($authFlag,$message);};
    }

    /**
     * 检查权限
     * @param int $adminID
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkCmsAdminAuth($adminID = 0){
        $action = strtolower(request()->action());
        //$request_url = strtolower($_SERVER["PATH_INFO"]);
        //$authUrl = explode($action,$request_url)[0].$action;
        $authUrl = '/'.request()->module().'/'.request()->controller().'/'.request()->action();
        //对待检测的URL 忽略大小写
        $adminModel = new User();
        $checkTag = $adminModel->checkAdminAuth($adminID,$authUrl);
        return $checkTag;
    }

}

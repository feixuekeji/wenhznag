<?php
namespace app\index\controller;

use app\common\model\Link;
use app\common\model\Navigation;
use app\common\model\Option;
use app\common\model\Setting;
use think\Controller;
use think\facade\Cache;
use think\facade\Session;
use app\common\model\Wechats;
use app\common\model\User;
use Token\Token;
use think\facade\Hook;
////////////////////////////////////////////////////////////////////
//                          _ooOoo_                               //
//                         o8888888o                              //
//                         88" . "88                              //
//                         (| ^_^ |)                              //
//                         O\  =  /O                              //
//                      ____/`---'\____                           //
//                    .'  \\|     |//  `.                         //
//                   /  \\|||  :  |||//  \                        //
//                  /  _||||| -:- |||||-  \                       //
//                  |   | \\\  -  /// |   |                       //
//                  | \_|  ''\---/''  |   |                       //
//                  \  .-\__  `-`  ___/-. /                       //
//                ___`. .'  /--.--\  `. . ___                     //
//            \  \ `-.   \_ __\ /__ _/   .-` /  /                 //
//      ========`-.____`-.___\_____/___.-`____.-'========         //
//                           `=---='                              //
//      ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^        //
//         佛祖保佑       永无BUG       永不修改                   //
////////////////////////////////////////////////////////////////////

class Base extends Controller
{
    /**
     * $force = false 不强制校验token,需要授权时调用checkToken
     * Base constructor.
     * @param bool $force
     */

    public function __construct($force = true)
    {
        global $_W;
        Hook::listen('response_send');//跨域
        $_W['user'] = '';//用户信息
        $this->checkToken($force);
        $this->getHeader();
        $this->getFooter();

    }


    /**
     * Notes:授权检查,方法中需要强制校验时调用，或判断后进行调用
     * Date: 2019/5/16
     * Time: 14:15
     */
    public function checkToken($force = true)
    {
        global $_W;
        $user_id = Session::get('user_id');
        $_W['user'] = User::get($user_id);//用户信息
        if ($force && empty($_W['user'])) {

            //request()->isAjax() && sendResponse($res, 401, 'Unauthorized');
            $this->error('login', 'login/index');

        }

    }


    public function getHeader()
    {
        if (request()->isGet() && empty(Cache::store('default')->get('menu_list')))
        {
            $navigation = new Navigation();
            $menu_list = $navigation->getNavigationByPid(0);
            $logo = Option::get('logo')->option_value;
            Cache::store('default')->set('logo',$logo,7200);
            Cache::store('default')->set('menu_list',$menu_list->toArray(),7200);
            Cache::store('default')->set('seo_title',Option::get('seo_title')->option_value,7200);
            Cache::store('default')->set('seo_keyword',Option::get('seo_keyword')->option_value,7200);
            Cache::store('default')->set('seo_description',Option::get('seo_description')->option_value,7200);
        }
    }


    public function getFooter()
    {
        if (request()->isGet() && empty(Cache::store('default')->get('link_list')))
        {
            $link_model = new Link();
            $link_list = $link_model->getList(10);
            Cache::store('default')->set('link_list',$link_list->toArray(),7200);
            Cache::store('default')->set('statistical_code',Option::get('statistical_code')->option_value,7200);
            Cache::store('default')->set('icp_record',Option::get('icp_record')->option_value,7200);
            Cache::store('default')->set('footer_tip',Option::get('footer_tip')->option_value,7200);

        }


    }



}

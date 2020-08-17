<?php

namespace app\index\controller;

use app\common\model\Option;
use think\Controller;
use think\facade\Cache;
use think\Request;
use EasyWeChat\Factory;
use think\facade\Session;
use Token\Token;
use think\facade\Env;


class Login extends Controller
{
    private $wechat_model;
    private $member_model;
    private $config;
    private $base_config;

    /**微信登录
     * Login constructor.
     * @param Request $request
     */
    public function __construct()
    {

    }




    public function index()
    {
        $uid = make_uid();
        //$ticket = Cache::store('default')->get('login_ticket');
        $socket = Option::get('socket_server')->option_value;
        $ticket = $this->loginCode($uid);
        Cache::store('default')->set('login'.$uid,1,600);
        $data = array(
            'uid' => $uid,
            'ticket' => $ticket,
            'socket' => $socket,
        );
        return view('index',$data);
    }



    public function loginCode($uid)
    {
        $config = [
            'app_id' => Option::get('app_id')->option_value,
            'secret' => Option::get('app_secret')->option_value,
            'token' => Option::get('app_token')->option_value,
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' => Env::get('runtime_path').'/wechat.log',
            ],
            'oauth' => [
                'scopes'   => ['snsapi_userinfo'],
                'callback' => '/index/login/oauth_callback',
            ],
        ];
        $app = Factory::officialAccount($config);
        $result = $app->qrcode->temporary($uid, 600);

        $url = $app->qrcode->url($result['ticket']);
        return $url;

    }

    public function login(Request $request)
    {
        $token = $request->param('token', 0);
        if (!empty($token)) {
            $jwtToken = new Token();
            $checkToken = $jwtToken->checkToken($token);
            $data = (array)$checkToken['data']['data'];
            $uid = $data['uid'] ?? 0;
            $user_id = $data['user_id'] ?? 0;
            Session::set('user_id', $user_id);
            $this->error('登陆成功', 'index/index/index');
        }
    }



}

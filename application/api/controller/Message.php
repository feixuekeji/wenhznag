<?php

namespace app\api\controller;

use app\common\model\Option;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use think\Controller;
use think\facade\Env;
use think\Request;
use EasyWeChat\Factory;
use think\facade\Session;
use message\MessageHandler;

/**微信消息处理
 * Class Message
 * @package app\api\controller
 */
class Message extends Controller
{

    public function __construct(Request $request)
    {
        global $_W;
        $_W['config'] = [
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

        //微信首次接入验证
        if (!empty($_GET['echostr']) && $this->checkSignature($_W['config']['token'])) {
            header('content-type:text');
            echo $_GET['echostr'];
            exit;
        }
    }


    public function index(Request $request)
    {
        global $_W;
        $app = Factory::officialAccount($_W['config']);

        $app->server->push(function ($message) {
            // $message['FromUserName'] // 用户的 openid
            // $message['MsgType'] // 消息类型：event, text....

            $handler = new MessageHandler($message);
            switch ($message['MsgType']) {
                case 'event':
                    //return '收到事件消息';
                    return $handler->eventHandler($message['FromUserName']);
                    break;
                case 'text':
                    //return '收到文字消息';
                    return $handler->textHandler($message['FromUserName']);
                    break;
                case 'image':
                    return '收到图片消息';
                    break;
                case 'voice':
                    return '收到语音消息';
                    break;
                case 'video':
                    return '收到视频消息';
                    break;
                case 'location':
                    //return '收到坐标消息';
                    return $handler->test();
                    break;
                case 'link':
                    return '收到链接消息';
                    break;
                case 'file':
                    return '收到文件消息';
                // ... 其它消息
                default:
                    return '收到其它消息';
                    break;
            }
        });

        $response = $app->server->serve();
        $response->send();
        //return $response;

    }

    /*
     * 接入验签
     */
    private function checkSignature($token)
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }



}



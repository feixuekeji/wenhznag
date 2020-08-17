<?php

namespace message;

use app\common\model\User;
use EasyWeChat\Kernel\Messages\Link;
use EasyWeChat\Kernel\Messages\Message;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use think\Exception;
use think\facade\Cache;
use think\facade\Log;
use EasyWeChat\Factory;
use Token\Token;
/**微信消息
 * Class MessageHandler
 * @package message
 */
class MessageHandler
{
    /*
    * 消息对象
    */

    private $message;
    private $user_model;

    public function __construct($message)
    {
        $this->message = $message;
        $this->user_model = new User();
    }

    /*
     * 事件响应函数
     */
    public function eventHandler()
    {
        // $message['FromUserName'] // 用户的 openid
        // $message['MsgType'] // 消息类型：event, text....
        global $_W;
        switch ($this->message['Event']) {
            //关注事件
            case 'subscribe':
                if (!empty($this->message['EventKey'])) {
                    $uid = substr($this->message['EventKey'],8);
                    $res = $this->loginEvent($uid);
                    return $res;
                }
                return '欢迎关注';
                break;
            //取消关注事件
            case 'unsubscribe':
                return $this->unSubscribe();
                break;
            //点击事件
            case 'CLICK':
                return '点击';
                break;
            //扫描事件
            case 'SCAN':
                $res = $this->loginEvent($this->message['EventKey']);
                return $res;
                //return '取关';
                break;
            default:
                return '收到其它消息';
                break;
        }
    }



    public function LoginEvent($uid)
    {
        global $_W;
        //注册
        $openid = $this->message['FromUserName'];
        $user_id = $this->addMember($openid);

        $jwtToken = new Token();
        $tokenData = array(
            'user_id' => $user_id,
        );
        $token = $jwtToken->createToken($tokenData, 86400)['token'];
        $time_out = Cache::store('default')->get('login'.$uid);
        if (empty($time_out))
            return "二维码过期，请重新登陆";
        $data = array(
            'uid' => $uid,
            'token' => $token
        );
        $res = $this->sendSocket($data);
        if($res)
            return "登录成功";
        else
            return '登录异常';



    }



    private function addMember($openid)
    {
        global $_W;
        $user_info = $this->user_model->getInfoByOpenId($openid);
        if (empty($user_info))
        {
            $app = Factory::officialAccount($_W['config']);
            $user_detail = $app->user->get($openid);
            $data = array(
                'nickname' => $user_detail['nickname'] ?? '',
                'openid' => $openid,
                'gender' => $user_detail['sex'] ?? 0,
                'avatar' => $user_detail['headimgurl'] ?? '',
            );
            $user_id = $this->user_model->addUser($data);
        }else{
            $user_id = $user_info->id;
        }
        return $user_id;

    }



    /**
     * Notes:取消关注事件
     * Date: 2019/6/19
     * Time: 14:11
     * @return bool
     */
    private function unSubscribe()
    {
        global $_W;
        $member_model = new Members();
        if ($_W['user']['id'] > 0)
        $member_model->unSubscribe($_W['user']['id']);
        return true;

    }

    public function sendSocket($data)
    {
        try{
            // 建立socket连接到内部推送端口
            $client = stream_socket_client('tcp://127.0.0.1:5678', $errno, $errmsg, 1);
            // 推送的数据，包含uid字段，表示是给这个uid推送
            //$data = array('uid'=>'201907181703404867264713', 'percent'=>'88%');
            // 发送数据，注意5678端口是Text协议的端口，Text协议需要在数据末尾加上换行符
            fwrite($client, json_encode($data)."\n");

            // 读取推送结果
            $res = fread($client, 8192);
            fclose($client);
            $res = json_decode($res,true);
            if($res['error'] == 0){
                return true;
            }else{
                return false;
            }

        }catch (\Exception $e)
        {
            return $e->getMessage();
        }

    }





}

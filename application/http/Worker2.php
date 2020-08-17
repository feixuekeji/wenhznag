<?php
namespace app\http;

use app\common\model\Collect;
use think\worker\Server;
use Workerman\Lib\Timer;
use Workerman\Worker as W;

class Worker2 extends Server
{
    protected $socket = 'websocket://0.0.0.0:5432';
    protected $option = [
        'count'=> 4,
    ];

    /**
     * 每个进程启动
     * @param $worker
     */


    public function onWorkerStart($worker)
    {


        // 心跳间隔55秒
        define('HEARTBEAT_TIME', 55);
        Timer::add(1, function()use($worker){
            $time_now = time();
            foreach($worker->connections as $connection) {
                // 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
                if (empty($connection->lastMessageTime)) {
                    $connection->lastMessageTime = $time_now;
                    continue;
                }
                // 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
                if ($time_now - $connection->lastMessageTime > HEARTBEAT_TIME) {
                    $connection->close();
                }
            }
        });
    }

    public function onMessage($connection,$data)
    {
        global $worker;
        // 判断当前客户端是否已经验证,即是否设置了uid
        if(!isset($connection->uid))
        {
            // 没验证的话把第一个包当做uid（这里为了方便演示，没做真正的验证）
            $connection->uid = ip2long($connection->getRemoteIp()).time().rand(1,9999);
            /* 保存uid到connection的映射，这样可以方便的通过uid查找connection，
             * 实现针对特定uid推送数据
             */
            $worker->uidConnections[$connection->uid] = $connection;
            $connection->send('login success, your uid is ' . $connection->uid);
        }
        $ids = json_decode($data,true)['ids'] ?? 0;
        if ($ids)
        {
            $collect_model = new Collect();
            $res = $collect_model->getNewestArticle($ids);
            $res = json_encode(['code' => 0,'msg' =>$res]);
            $connection->send($res);
        }

        // 给connection临时设置一个lastMessageTime属性，用来记录上次收到消息的时间
        $connection->lastMessageTime = time();
        //$connection->send('receive success');
        echo $data;
        echo "\n";
    }

    public function onConnect($connection)
    {

    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        global $worker;
        if(isset($connection->uid))
        {
            // 连接断开时删除映射
            unset($worker->uidConnections[$connection->uid]);
        }
    }
    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }


    // 针对uid推送数据
    public function sendMessageByUid($uid, $message)
    {
        global $worker;
        if(isset($worker->uidConnections[$uid]))
        {
            $connection = $worker->uidConnections[$uid];
            $connection->send($message);
            return true;
        }
        return false;
    }


}
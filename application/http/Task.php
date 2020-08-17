<?php
namespace app\http;

use app\common\model\Collect;
use think\worker\Server;
use Workerman\Lib\Timer;
use Workerman\Worker as W;

class Task extends Server
{
    protected $socket = 'Text://0.0.0.0:12345';
    protected $option = [
        'count'=> 100,
    ];

    /**
     * 每个进程启动
     * @param $worker
     */


    public function onWorkerStart($worker)
    {


        $collect_model = new \app\common\model\Collect();
        $res = $collect_model->test();
        echo $res;
    }

    public function onMessage($connection,$data)
    {
        echo $data;

        // 假设发来的是json数据
        $task_data = json_decode($data, true);
        // 根据task_data处理相应的任务逻辑.... 得到结果，这里省略....
        $task_result = 1;
        // 发送结果
        $connection->send(json_encode($task_result));
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
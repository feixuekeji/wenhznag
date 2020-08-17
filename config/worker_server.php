<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Env;
use Workerman\Lib\Timer;
use Workerman\Worker;

// +----------------------------------------------------------------------
// | Workerman设置 仅对 php think worker:server 指令有效
// +----------------------------------------------------------------------
return [
    // 扩展自身需要的配置
    'protocol'       => 'websocket', // 协议 支持 tcp udp unix http websocket text
    'host'           => '0.0.0.0', // 监听地址
    'port'           => 2345, // 监听端口
    'socket'         => '', // 完整监听地址
    'context'        => [], // socket 上下文选项
    //'worker_class'   => 'app\http\Worker', // 自定义Workerman服务类名 支持数组定义多个服务
    'worker_class'   => 'app\http\Worker2', // 自定义Workerman服务类名 支持数组定义多个服务
    //'worker_class'   => 'app\http\Task', // 自定义Workerman服务类名 支持数组定义多个服务
    //'worker_class'	=>	['app\http\Worker', 'app\http\Worker2'],

    // 支持workerman的所有配置参数
    'name'           => 'thinkphp',
    'count'          => 4,
    'daemonize'      => false,
    'pidFile'        => Env::get('runtime_path') . 'worker.pid',

    // 支持事件回调
    // onWorkerStart
    'onWorkerStart'  => function ($worker) {

        // 开启一个内部端口，方便内部系统推送数据，Text协议格式 文本+换行符
        $inner_text_worker = new Worker('Text://0.0.0.0:5678');
        $inner_text_worker->onMessage = function($connection, $buffer)
        {
            global $worker;
            // $data数组格式，里面有uid，表示向那个uid的页面推送数据
            $data = json_decode($buffer, true);
            $uid = $data['uid'];
            // 通过workerman，向uid的页面推送数据
            $ret = sendMessageByUid($uid, $buffer);
            // 返回推送结果
            $connection->send($ret ? 'ok' : 'fail');
        };
        $inner_text_worker->listen();

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

    },
    // onWorkerReload
    'onWorkerReload' => function ($worker) {

    },
    // onConnect
    'onConnect'      => function ($connection) {

    },
    // onMessage
    'onMessage'      => function ($connection, $data){
        global $worker;
        // 判断当前客户端是否已经验证,即是否设置了uid
        if(!isset($connection->uid))
        {
            // 没验证的话把第一个包当做uid（这里为了方便演示，没做真正的验证）
            $connection->uid = $data;
            /* 保存uid到connection的映射，这样可以方便的通过uid查找connection，
             * 实现针对特定uid推送数据
             */
            $worker->uidConnections[$connection->uid] = $connection;
            return $connection->send('login success, your uid is ' . $connection->uid);
        }
        // 给connection临时设置一个lastMessageTime属性，用来记录上次收到消息的时间
        $connection->lastMessageTime = time();
        //$connection->send('receive success');
        echo $data;

    },
    // onClose
    'onClose'        => function ($connection) {

    },
    // onError
    'onError'        => function ($connection, $code, $msg) {
        echo "error [ $code ] $msg\n";
    },
];

<?php

/**
 * curl获取数据
 * @param $url
 * @return mixed
 */
function get_url($url)
{
    $ifpost = 0;//是否post请求
    $datafields = '';//post数据
    $cookiefile = '';//cookie文件
    $cookie = '';//cookie变量
    $v = false;
    //构造随机ip
    $ip_long = array(
        array('607649792', '608174079'), //36.56.0.0-36.63.255.255
        array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
        array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
        array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
        array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
        array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
        array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
        array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
        array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
        array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255
    );
    $rand_key = mt_rand(0, 9);
    $ip= long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
//模拟http请求header头
    $header = array("Connection: Keep-Alive","Accept: text/html, application/xhtml+xml, */*", "Pragma: no-cache", "Accept-Language: zh-Hans-CN,zh-Hans;q=0.8,en-US;q=0.5,en;q=0.3","User-Agent: Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; WOW64; Trident/6.0)",'CLIENT-IP:'.$ip,'X-FORWARDED-FOR:'.$ip);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, $v);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $ifpost && curl_setopt($ch, CURLOPT_POST, $ifpost);
    $ifpost && curl_setopt($ch, CURLOPT_POSTFIELDS, $datafields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $cookie && curl_setopt($ch, CURLOPT_COOKIE, $cookie);//发送cookie变量
    $cookiefile && curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);//发送cookie文件
    $cookiefile && curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);//写入cookie到文件
    curl_setopt($ch,CURLOPT_TIMEOUT,60); //允许执行的最长秒数
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $ok = curl_exec($ch);
    curl_close($ch);
    unset($ch);
    return $ok;
}

/**
 * json f返回数据
 * @param $status
 * @param string $message
 * @param array $data
 */
function showMsg($status,$message = '',$data = array()){
    $result = array(
        'status' => $status,
        'message' =>$message,
        'data' =>$data
    );
    exit(json_encode($result));
}


/**
 * Notes:返回带状态码信息
 * User: xxf
 * Date: 2019/3/18
 * Time: 16:29
 * @param array $data
 * @param int $code
 * @param string $message
 */
function sendResponse($data = [],$code = 200,$message = 'ok')
{
    $HTTP_VERSION = "HTTP/1.1";
    //输出结果
    header($HTTP_VERSION . " " . $code . " " . $message);
    header("Content-Type: application/json");
    exit(json_encode($data));
}


function ajaxResponse($data = [],$code = 0,$msg = 'success')
{
    $result = array(
        'code' => $code,
        'msg' =>$msg,
        'data' =>$data
    );

    exit(json_encode($result));
}

/**
 * Notes:生成UID
 * @auther: xxf
 * Date: 2019/7/17
 * Time: 17:28
 * @return string
 */
function make_uid()
{
    @date_default_timezone_set("PRC");
    //号码主体（YYYYMMDDHHIISSNNNNNNNN）
    $order_id_main = date('YmdHis') . rand(10000000,99999999);
    $order_id_len = strlen($order_id_main);
    $order_id_sum = 0;
    for($i=0; $i<$order_id_len; $i++){
        $order_id_sum += (int)(substr($order_id_main,$i,1));
    }
    //唯一号码（YYYYMMDDHHIISSNNNNNNNNCC）
    $uid = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
    return $uid;
}

/**
 * Notes:获取文本中所以图片
 * @auther: xxf
 * Date: 2019/7/25
 * Time: 13:55
 * @param $content
 * @param int $order 第几张，0全部
 * @return array
 */
function getImages($content,$order=0){
    //匹配有后缀图片
/*    $pattern="/<img.*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png|\.jpeg|\.?]))[\'|\"].*?[\/]?>/";*/
    //匹配无后缀图片
//    $pattern="/<img.*?src=[\'|\"](.*?)[\'|\"]/";
    $pattern="/<img.*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/";
    preg_match_all($pattern,$content,$match);
    if(!empty($match[1])){
        if($order == 0){
            return $match[1];
        }
        if(!empty($match[1][$order-1])){
            return $match[1][$order-1];
        }
    }
    return [];
}


function getQiNiuImages($content){
    //匹配有后缀图片
    /*    $pattern="/<img.*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png|\.jpeg|\.?]))[\'|\"].*?[\/]?>/";*/
    //匹配无后缀图片
//    $pattern="/<img.*?src=[\'|\"](.*?)[\'|\"]/";
    $pattern="/<img.*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/";
    preg_match_all($pattern,$content,$match);
    $images = [];
    if(!empty($match[1])){
        foreach ($match[1] as $k => $v)
        {
            $images[] = substr($v,strripos($v,'/')+1);
        }

            return $images;
    }
    return [];
}

function curl($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Encoding:gzip'));
    curl_setopt($ch, CURLOPT_ENCODING, "gzip");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_TIMEOUT,60); //允许执行的最长秒数
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

/**
 * Notes:是否含有关键字
 * @auther: xxf
 * Date: 2019/8/5
 * Time: 18:43
 * @param $string
 * @param $keyword
 * @return bool
 */
function is_contain($string,$keyword)
{
    if(strpos($string,$keyword) !== false)
        return true;
    else
        return false;
}

/**
 * Notes:获取特定字符后面的字符串
 * @auther: xxf
 * Date: 2019/8/5
 * Time: 21:07
 * @param $string
 * @param $keyword
 * @return bool|string
 */
function get_behind_string($string,$keyword)
{
    $result = mb_substr($string,mb_strripos($string,$keyword));
    return $result;
}

/**
 * Notes:获取特定字符前面的字符串
 * @auther: xxf
 * Date: 2019/8/5
 * Time: 21:07
 * @param $string
 * @param $keyword
 * @return bool|string
 */
function get_front_string($string,$keyword)
{
    try {
        $result = mb_substr($string, 0, mb_strpos($string, $keyword));
        if (empty($result))
            $result = $string;
    } catch (Exception $e)
    {
        return $e->getMessage();
    }
    return $result;
}



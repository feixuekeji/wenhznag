<?php
namespace unit;
use app\common\model\Option;
use think\facade\Env;
use think\Request;
use Config;


/**
 * Notes:微信公众号类
 * User: xxf
 * Date: 2019/7/17
 * Time: 17:09
 * Class Wechat
 * @package unit
 */
class Wechat
{
    public $app;
    public function __construct($wechatId) {

        $config = [
            'app_id' => Option::get('APP_ID')->option_value,
            'secret' => Option::get('APP_ID')->option_value,
            'token' => Option::get('APP_ID')->option_value,
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' => Env::get('runtime_path').'/wechat.log',
            ],
            'oauth' => [
                'scopes'   => ['snsapi_userinfo'],
                'callback' => $_SERVER['SCRIPT_NAME'].'/index/login/oauth_callback',
            ],
        ];
        $app = Factory::officialAccount($config);
        $this->app =  $app;
    }

/*    public function img_file(Request $request)
    {
        $status = 0;
        $data = [];
        if ($request->Method()== 'POST') {
            $file = $request->file('file');
            // 移动到框架应用根目录/upload/ 目录下
            $info = $file->move('upload');
            if ($info){
                $fileUrl = '/upload/'.$info->getSaveName();
                $status = 1;
                $data['url'] = $fileUrl;
                $message = '上传成功';
            }else{
                $message = "上传失败 ".$file->getError();
            }
        } else {
            $message = "参数错误";
        }
        return showMsg($status, $message,$data);
    }*/


    /**文章配图上传七牛
     * @param Request $request
     */

    public function img_file(Request $request)
    {
        $status = 0;
        $data = [];
        $file= request()->file('file');
        if ($file) {

            $filePath = $file->getRealPath();
            $token = $this->auth->uploadToken($this->bucket);// 生成上传 Token

            $key = md5(time().rand(10000,99999)).'.png';

            // 初始化 UploadManager 对象并进行文件的上传
            $uploadMgr = new UploadManager();

            // 调用 UploadManager 的 putFile 方法进行文件的上传
            list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
            if ($err === null) {
                $data['url'] = $this->config['domain'].$ret['key'];
                $status = 1;
                $message = '上传成功';
            }
            else{
                $message = "上传失败 ".$file->getError();
            }

        } else{
            $message = "参数错误 ".$file->getError();
        }
        return showMsg($status, $message,$data);

    }




    /**
     *
     * 七牛云存储图片
     *
     */
    public function qiniu_upload($filePath){
        $config = Config::get('UPLOAD_Qiniu_CONFIG');
        $accessKey = $config['accessKey'];
        $secretKey = $config['secretKey'];
        $auth = new Auth($accessKey, $secretKey);

        $bucket = $config['bucket'];// 要上传的空间
        $token = $auth->uploadToken($bucket);// 生成上传 Token

        // 要上传文件的本地路径
        $data = file_get_contents($filePath);


        $key = md5(time().rand(10000,99999)).'.png';

        // 初始化 UploadManager 对象并进行文件的上传
        $uploadMgr = new UploadManager();

        // 调用 UploadManager 的 putFile 方法进行文件的上传
        /*list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        if ($err === null) {
            $data['url'] = $config['domain'].$ret['key'];
        }*/


        // 调用 UploadManager 的 put 方法进行文件的上传
        list($ret, $err) = $uploadMgr->put($token, $key, $data);
        if ($err === null) {
            $url = $config['domain'].$ret['key'];
            return $url;
        }


    }


    /**
     *
     * 七牛云存储图片网络资源
     *
     */
    public function qiniuFetch($url){
        $bucketManager = new BucketManager($this->auth);
        $key = md5(time().rand(10000,99999)).'.png';

        // 指定抓取的文件保存名称
        list($ret, $err) = $bucketManager->fetch($url, $this->bucket, $key);
        if ($err === null) {
            $url = $this->config['domain'].$ret['key'];
            return $url;
        }


    }





    /**
     * 删除图片
     * @param $delFileName 要删除的图片文件
     * @return bool
     */
    public function delFileByName($delFileName)
    {
        // 判断是否是图片
        $isImage = preg_match('/.*(\.png|\.jpg|\.jpeg|\.gif)$/', $delFileName);
        if(!$isImage){
            return false;
        }

        $config1 = new \Qiniu\Config();




        // 管理资源
        $bucketManager = new BucketManager($this->auth, $config1);

        // 删除文件操作
        $res = $bucketManager->delete($this->bucket, $delFileName);

        if (is_null($res)) {
            // 为null成功
            return true;
        }

        return false;

    }


    //文件上传处理
    public function uploadFile()
    {
        $file= request()->file('file');
        if ($file) {

            $filePath = $file->getRealPath();
            $url = $this->qiniu_upload($filePath);


            $arr = [
                'errno'=>0,
                "data" => array("$url"),
            ];
        } else {
            $arr = [
                //'msg' => '上传文件失败！',
                'msg' => $file->getError(),
                'type' => 0,
            ];
        }
        echo json_encode($arr);
        exit();
    }



}

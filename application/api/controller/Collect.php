<?php
namespace app\api\Controller;
use app\common\model\Collect as M;
use app\common\model\Catalog;
use app\common\model\Option;
use think\Request;
use think\facade\Log;
use think\Controller;

class Collect extends Controller
{
    private $model ;
    private $catalogModel;
    public function __construct()
    {
        parent::__construct();
        $this->model = new M();
        $this->catalogModel = new Catalog();
    }

    public function post(Request $request)
    {
        // 与发布项的发布密码一致
        $password = Option::get('publish_password')->option_value;
        if (empty($_POST['__sign']) || md5($password."shenjianshou.cn") != trim($_POST['__sign'])) {
            echo json_encode([
                "result" => 2,
                "reason" => "发布失败, 错误原因: 发布密码验证失败!"
            ]);
            exit;
        }
        //Log::info('post：' . json_encode($request->param()));
        $res = $this->model->apiPost($request->param());
        echo json_encode($res);
        exit;
    }

    public function startCollect()
    {
        ignore_user_abort(true);
        set_time_limit(0);
        $res = $this->model->getNewestArticle();
        echo "采集完成,共采集：".$res;
    }

}

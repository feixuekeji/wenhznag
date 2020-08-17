<?php
namespace app\admin\Controller;
use app\admin\controller\Base;
use app\common\model\Subscriptions;
use think\Request;
use app\common\model\Collect;
use app\common\model\Option;

class Subscription extends Base
{
    private $model ;
    public function __construct()
    {
        parent::__construct();
        $this->model = new Subscriptions();
    }

    /**
     * 获取公众号列表数据
     * @param Request $request
     * @return \think\response\View
     */
    public function index(){
        $data['socket'] = Option::get('collect_socket')->option_value;
        return view('index',$data);
    }




    /**
     * @param Request $request
     */
    public function getWechatList(Request $request){
        if ($request->isPost()){
            $size = $request->post('size',10);
            $keyword = $request->post('keyword','');
            $where = [];
            if (!empty($keyword))
                $where[] = array('name|account', 'like', '%' . $keyword . '%');
            $list = $this->model->getList($size,$where);
            return ajaxResponse($list,0,'success');
        }else{
            return showMsg(0,'sorry，请求不合法');
        }

    }
    /**
     * 添加
     * @param Request $request
     * @return \think\response\View|void
     */
    public function addWechat(Request $request){
        if($request->isPost()){
            $input = $request->param();
            $opRes = $this->model->add($input);
            return showMsg($opRes['tag'],$opRes['message']);
        }else{
            return view('add');
        }
    }

    /**
     * 更新数据
     * @param Request $request
     * @param $id
     * @return \think\response\View|void
     */
    public function editWechat(Request $request,$id){
        if ($request->isPost()){
            $opRes = $this->model->edit( $request->param());
            return showMsg($opRes['tag'],$opRes['message']);
        }else{
            $data['info'] = $this->model->getInfoByID($id);
            return view('edit',$data);
        }
    }


    public function deleteWechat(Request $request)
    {
        $ids = $request->param('ids',0);
        if (is_array($ids))
        {
            $res = $this->model->deleteByIds($ids);
            $res && ajaxResponse($res,0,'删除成功');
        }
        return ajaxResponse('',1,'出错');
    }

    /**
     * Notes:推荐
     * @auther: xxf
     * Date: 2019/8/5
     * Time: 16:08
     * @param Request $request
     */
    public function recommend(Request $request)
    {
        $ids = $request->param('ids',0);
        if (is_array($ids))
        {
            $res = $this->model->recommendByIds($ids);
            $res && ajaxResponse($res,0,'成功');
        }
        return ajaxResponse('',1,'出错');
    }

    /**
     * Notes:开启采集
     * @auther: xxf
     * Date: 2019/8/5
     * Time: 16:08
     * @param Request $request
     */
    public function is_collect(Request $request)
    {
        $ids = $request->param('ids',0);
        if (is_array($ids))
        {
            $res = $this->model->is_collectByIds($ids);
            $res && ajaxResponse($res,0,'成功');
        }
        return ajaxResponse('',1,'出错');
    }

    public function to_collect(Request $request)
    {
        $ids = $request->param('ids',0);
        if (is_array($ids))
        {
            $collect_model = new Collect();
            $res = $collect_model->getNewestArticle($ids);
            $res >= 0 && ajaxResponse($res,0,'采集条数：'.$res);
        }
        return ajaxResponse('',1,'异常');
    }

}

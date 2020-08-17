<?php
namespace app\admin\controller;
use app\common\model\NavMenus;
use app\common\model\Admins;
use think\facade\Session;
use think\Request;


class Index{
    private $menuModel;
    private $adminModel;
    protected $AID;
    public function __construct()
    {
        $this->menuModel = new NavMenus();
        $this->adminModel = new \app\common\model\User();
        $this->AID = Session::get('AID');
        if (!$this->AID){
            return redirect('admin/login/index');
        }
    }

    /**
     * 后台首页
     * @return \think\response\View
     */
    public function index(){
        //获取 登录的管理员有效期ID
        $menuList = $this->menuModel->getNavMenusShow($this->AID);
        if (!$this->AID || !$menuList){
            // 页面跳转至登录页
            return redirect('admin/login/index');
        }else{
            $adminInfo = $this->adminModel->getAdminData($this->AID);
            $data = [
                'menus' => $menuList,
                'admin' => $adminInfo,
            ];
            return view('index',$data);
        }
    }

    /**
     * 首页显示 可自定义呗
     * @return \think\response\View
     */
    public function home(){
        return view('home');
    }

    /**
     * 展示管理员个人信息 可自行修改
     * @param Request $request
     * @param $id
     * @return \think\response\View|void
     */
    public function admin(Request $request,$id){
        $adminModel = new Admins();
        if ($request->isGet()){
            $adminData = $adminModel->getAdminData($id);
            return view('admin',[
                'admin' => $adminData,
            ]);
        }else{
            //当前用户对个人账号的修改
            $input = $request->post();
            $opRes = $adminModel->editCurrAdmin($id,$input,$this->cmsAID);
            return showMsg($opRes['tag'],$opRes['message']);
        }
    }
}

<?php

namespace app\admin\Controller;

use app\admin\controller\Base;
use app\common\model\AdminRoles;
use app\common\model\User;
use app\common\model\NavMenus;
use think\Request;
use think\facade\Cache;

class Admin extends Base
{
    private $model;
    private $ar_model;
    private $menuModel;
    private $page_limit;
    public function __construct()
    {
        parent::__construct();
        $this->model = new User();
        $this->ar_model = new AdminRoles();
        $this->menuModel = new NavMenus();
        $this->page_limit = config('app.CMS_PAGE_SIZE');
    }

    /**
     * 管理员数据列表
     * @param Request $request
     * @return \think\response\View
     */
    public function index(Request $request){

        return view('index');
    }

    /**
     * 添加新用户
     * @param Request $request
     * @return \think\response\View|void
     */
    public function add(Request $request){
        $adminRoles = $this->ar_model->getNormalRoles();
        if ($request->isPost()){
            $input = $request->post();
            $opRes = $tag = $this->model->addAdmin($input);
            return showMsg($opRes['tag'],$opRes['message']);
        }else{
            return view('add',[
                'adminRoles'=>$adminRoles
            ]);
        }
    }

    /**
     * @param Request $request
     * @param $id 标识ID
     * @return \think\response\View|void
     */
    public function edit(Request $request,$id){

        if ($request->isPost()){
            $input = $request->param();
            $opRes = $this->model->editAdmin($id,$input);
            return showMsg($opRes['tag'] ,$opRes['message']);
        }else{
            $adminRoles = $this->ar_model->getNormalRoles();
            $adminData = $this->model->getAdminData($id);
            return view('edit',[
                'admin' => $adminData,
                'adminRoles' => $adminRoles
            ]);
        }
    }

    public function deleteAdmin(Request $request)
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
     * 分页获取数据
     * @param Request $request
     */
    public function ajaxOpForPage(Request $request){

            $size = $request->post('size',10);
            $keyword = $request->post('keyword','');
            $where = array();
            if (!empty($keyword))
                $where[] = array('a.user_name', 'like', '%' . $keyword . '%');
            $list = $this->model->getAdminsForPage($size,$where);
            return ajaxResponse($list,0);

    }



    /**
     * 读取角色列表
     * @return \think\response\View
     */
    public function role(){
        $adminRoles = $this->ar_model->getAllRoles();
        return view('role',[
            'roles' => $adminRoles
        ]);

    }

    /**
     * 角色添加功能
     * @param Request $request
     * @return \think\response\View|void
     */
    public function addRole(Request $request){
        if ($request->isPost()){
            $input = $request->post();
            $opRes = $this->ar_model->addRole($input);
            return showMsg($opRes['tag'],$opRes['message']);
        }else{
            // 获取所有可以分配的权限菜单
            $viewMenus = $this->menuModel->getNavMenus();
            return view('add_role',[
                'menus'=>$viewMenus,
            ]);
        }
    }

    /**
     * 更新 角色数据
     * @param Request $request
     * @param $id
     * @return \think\response\View|void
     */
    public function editRole(Request $request,$id){
        $roleData = $this->ar_model->getRoleData($id);
        if ($request->isPost()){
            $input = $request->param();
            $opRes = $this->ar_model->editRole($id,$input);
            return showMsg($opRes['tag'],$opRes['message']);
        }else{
            //获取所有可以分配的权限菜单
            $viewMenus = $this->menuModel->getNavMenus();
            $arrMenuSelf = explode('|',$roleData['nav_menu_ids']);
            return view('edit_role',[
                'role' => $roleData,
                'menus' => $viewMenus,
                'menuSelf' => $arrMenuSelf,
            ]);
        }
    }


    /**
     * Notes:清空缓存
     * User: xxf
     * Date: 2019/3/18
     * Time: 15:14
     * @return string
     */
    public function clear()
    {
        $res = Cache::store('default')->clear();
        if ($res)
            return "已清空";
    }


    public function getMemberList(Request $request)
    {
        if($request->isAjax())
        {
            $size = $request->post('size',10);
            $keyword = $request->post('keyword','');
            $where = array();
            $where[] = array('u.status', '=', 1);
            if (!empty($keyword))
                $where[] = array('u.user_name|u.nickname', 'like', '%' . $keyword . '%');
            $list = $this->model->getMemberList($size,$where);
            return ajaxResponse($list,0);
        }else {
            return view('member_list');
        }

    }


    public function getMemberRecycleList(Request $request)
    {
        if($request->isAjax())
        {
            $size = $request->post('size',10);
            $keyword = $request->post('keyword','');
            $where = array();
            $where[] = array('u.status', '=', 0);
            if (!empty($keyword))
                $where[] = array('u.user_name|u.nickname', 'like', '%' . $keyword . '%');
            $list = $this->model->getMemberList($size,$where);
            return ajaxResponse($list,0);
        }else {
            return view('recycle_list');
        }

    }

    public function deleteMember(Request $request)
    {
        $ids = $request->param('ids',0);
        if (is_array($ids))
        {
            $res = $this->model->deleteByIds($ids);
            $res && ajaxResponse($res,0,'删除成功');
        }
        return ajaxResponse('',1,'出错');
    }

    public function recoveryMember(Request $request)
    {
        $ids = $request->param('ids',0);
        if (is_array($ids))
        {
            $res = $this->model->recoveryByIds($ids);
            $res && ajaxResponse($res,0,'恢复成功');
        }
        return ajaxResponse('',1,'出错');
    }



}

<?php

namespace app\common\model;

use app\common\validate\User;
use think\Db;
use think\Model;
use think\Session;

class Users extends BaseModel
{
    protected $validate;
    protected $autoWriteTimestamp = true;//自动时间戳

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->validate = new User();
    }

    /**
     * 分页获取管理员数据
     * @param $curr_page
     * @param $limit
     * @return array
     */
    public function getAdminsForPage($limit, $where)
    {
        $res = $this
            ->alias('a')
            ->field('a.*,ar.user_name role_name')
            ->join('admin_roles ar', 'a.role_id = ar.id')
            ->order('a.id', 'desc')
            ->where("a.status", 1)
            ->where("a.type", 1)
            ->where($where)
            ->paginate($limit);

        return $res;
    }

    /**
     * Notes:批量删除
     * Date: 2019/7/8
     * Time: 15:59
     * @param $ids
     * @return int|string|static
     */
    public function deleteByIds($ids)
    {
        $res = $this
            ->where('id', 'in', $ids)
            ->update(['status' => 0]);
        return $res;
    }


    public function recoveryByIds($ids)
    {
        $res = $this
            ->where('id', 'in', $ids)
            ->update(['status' => 1]);
        return $res;
    }

    /**
     * 根据ID 获取管理员数据
     * @param $id
     * @return array
     */
    public function getAdminData($id)
    {
        $res = $this
            ->alias('a')
            ->field('a.id,a.user_name,a.avatar,a.role_id,a.create_time,
                     a.status,ar.user_name role_name')
            ->join('admin_roles ar', 'ar.id = a.role_id')
            ->where('a.id', $id)
            ->find()
            ->toArray();
        return $res;
    }

    /**
     * 添加后台管理员
     * @param $input
     * @return int|void
     */
    public function addAdmin($input)
    {
        $user_name = $input['user_name'] ?? '';
        $sameTag = $this->chkSameUserName($user_name);
        if ($sameTag) {
            $validateRes['tag'] = 0;
            $validateRes['message'] = '此昵称已被占用，请换一个！';
        } else {
            $addData = [
                'user_name' => $user_name,
                'avatar' => isset($input['avatar']) ? $input['avatar'] : '',
                'password' => md5(base64_encode($input['password'])),
                'create_time' => date("Y-m-d H:i:s", time()),
                'role_id' => intval($input['role_id']),
                'status' => intval($input['status']),
            ];
            $tokenData = ['__token__' => isset($input['__token__']) ? $input['__token__'] : '',];
            $validateRes = $this->validate($this->validate, $addData, $tokenData);
            if ($validateRes['tag']) {
                $tag = $this->insert($addData);
                $validateRes['tag'] = $tag;
                $validateRes['message'] = $tag ? '管理员添加成功' : '添加失败';
            }
        }
        return $validateRes;

    }

    /**
     * 当前在线管理员 对个人信息的修改
     * @param $id
     * @param $input
     * @param $cmsAID
     * @return array
     */
    public function editCurrAdmin($id, $input, $cmsAID)
    {
        $saveData = [
            'user_name' => $input['user_name'],
            'avatar' => $input['avatar'],
            'content' => $input['content'],
        ];
        $tokenData = ['__token__' => isset($input['__token__']) ? $input['__token__'] : '',];
        $validateRes = $this->validate($this->validate, $saveData, $tokenData, 'cms_admin');
        if ($validateRes['tag']) {
            if ($input['password']) {
                // 如果输入了新密码
                $saveData['password'] = md5(base64_encode($input['password']));
            }
            if ($cmsAID && ($cmsAID != $id)) {
                $validateRes['tag'] = 0;
                $validateRes['message'] = "您没有权限进行修改";
            } else {
                $tag = $this
                    ->where('id', $id)
                    ->update($saveData);
                $validateRes['tag'] = $tag;
                $validateRes['message'] = $tag ? '信息修改成功' : '数据无变动，修改失败';
            }
        }
        return $validateRes;
    }

    /**
     * 根据ID 修改管理员数据
     * @param $id
     * @param $input
     * @return void|static
     */
    public function editAdmin($id, $input)
    {

        $sameTag = $this->chkSameUserName($input['user_name'], $id);
        if ($sameTag) {
            $validateRes['tag'] = 0;
            $validateRes['message'] = '此昵称已被占用，请换一个！';
        } else {
            $saveData = [
                'user_name' => $input['user_name'],
                'avatar' => $input['avatar'],
                'role_id' => $input['role_id'],
                'status' => $input['status'],
            ];
            $tokenData = ['__token__' => isset($input['__token__']) ? $input['__token__'] : '',];
            $validateRes = $this->validate($this->validate, $saveData, $tokenData);
            if ($validateRes['tag']) {
                if ($input['password']) {
                    //如果输入了新密码
                    $saveData['password'] = md5(base64_encode($input['password']));
                }
                $tag = $this
                    ->where('id', $id)
                    ->update($saveData);
                $validateRes['tag'] = $tag;
                $validateRes['message'] = $tag ? '管理员修改成功' : '数据无变动，修改失败';
            }
        }
        return $validateRes;
    }

    /**
     * 判断当前数据库中是否有重名的管理员
     * @param $user_name
     * @param int $id
     * @return mixed
     */
    public function chkSameUserName($user_name, $id = 0)
    {
        $tag = $this
            ->field('user_name')
            ->where('user_name', $user_name)
            ->where('id', '<>', $id)
            ->count();
        return $tag;
    }

    /**
     * 获取当前管理员权限下的 导航菜单
     * @param int $id
     * @return mixed
     */
    public function getAdminNavMenus($id = 1)
    {
        $nav_menu_ids = $this
            ->alias('a')
            ->join('admin_roles ar', 'ar.id = a.role_id')
            ->where([['a.id', '=', $id], ['a.status', '=', 1]])
            ->value('nav_menu_ids');
        return $nav_menu_ids;
    }

    /**
     * 管理员登录 反馈
     * @param $input
     * @return bool|mixed
     */
    public function adminLogin($input)
    {
        $flag = false;
        $message = "登录成功";
        $userName = isset($input['user_name']) ? $input['user_name'] : '';
        $pwd = isset($input['password']) ? $input['password'] : '';
        $verifyCode = isset($input['login_verifyCode']) ? $input['login_verifyCode'] : '';
        //首先判断验证码是否可用
        if (!captcha_check($verifyCode)) {
            $message = "验证码填写有误或已过期";
        } else {
            $res = $this
                ->field('password,id')
                ->where('user_name', $userName)
                ->where('status', 1)
                ->where('type', 1)
                ->find();
            if ($res) {
                if ($res->password == md5(base64_encode($pwd))) {
                    $flag = $res->id;
                } else {
                    $message = "登录失败，请检查您的信息";
                }
            } else {
                $message = "该用户名不存在";
            }
        }

        return [
            'tag' => $flag,
            'message' => $message
        ];
    }

    /**
     * 检查 管理员是否对此URL有管理权限
     * @param int $adminID
     * @param string $authUrl
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkAdminAuth($adminID = 0, $authUrl = '')
    {
        $checkTag = false;
        $nav_menu_ids = $this->getAdminNavMenus($adminID);
        if (is_string($nav_menu_ids)) {
            $arrMenus = explode("|", $nav_menu_ids);
            foreach ($arrMenus as $key => $menu_id) {
                if ($menu_id) {
                    $checkTag = $this->checkAuthUrlForMenuID($menu_id, $authUrl);
                    if ($checkTag) {
                        break;
                    } else {
                        //此时判断其的 下级权限中是否满足 当前访问的权限
                        $childMenus = Db::name('nav_menus')
                            ->field("n2.id")
                            ->alias('n1')
                            ->join("nav_menus n2", "n1.id = n2.parent_id")
                            ->where(
                                [
                                    ["n2.parent_id", '=', $menu_id],
                                    ['n2.status', '=', 1],
                                    ['n2.type', '=', 1]
                                ])
                            ->select();
                        foreach ($childMenus as $key2 => $child_menu) {
                            $checkTag = $this->checkAuthUrlForMenuID($child_menu['id'], $authUrl);
                            if ($checkTag) {
                                break;
                            } else {
                                continue;
                            }
                        }
                        if ($checkTag) {
                            break;
                        }
                    }
                }
            }
        }
        return $checkTag;
    }

    /**
     * 忽略 因操作系统不同对链接字符串大小写的敏感
     * @param int $menu_id
     * @param $authUrl
     * @return bool
     */
    public function checkAuthUrlForMenuID($menu_id = 0, $authUrl)
    {
        $checkTag = false;
        $menuAction = Db::name('nav_menus')
            ->where([["id", '=', $menu_id], ['status', '=', 1]])
            ->value('action');
        if ("/" . strtolower($menuAction) == strtolower($authUrl)) {
            $checkTag = true;
        }
        return $checkTag;
    }


    /**
     * Notes:用户列表
     * @auther: xxf
     * Date: 2019/7/8
     * Time: 17:12
     * @param $limit
     * @param null $where
     * @return \think\Paginator
     */
    public function getMemberList($limit, $where = null)
    {
        $field = "u.*,
 (SELECT COUNT(id) FROM article WHERE user_id = u.id) total_publish,
 (SELECT COUNT(id) FROM user_follow WHERE user_id = u.id) total_follow,
 (SELECT COUNT(id) FROM user_follow WHERE follow_user_id = u.id) total_fans,
 (SELECT COUNT(id) FROM favorite WHERE user_id = u.id) total_favorite";
        $res = $this
            ->alias('u')
            ->fieldRaw($field)
            ->order('u.id', 'desc')
            ->where($where)
            ->where("u.type", 2)
            ->where($where)
            ->paginate($limit);
        return $res;
    }

    /**
     * Notes:批量恢复到白名单
     * @auther: xxf
     * Date: 2019/7/8
     * Time: 17:17
     * @param $ids
     * @return int|string|static
     */
    public function recoveryMemberByIds($ids)
    {
        $res = $this
            ->where('id', 'in', $ids)
            ->update(['status' => 1]);
        return $res;
    }

    /**
     * Notes:修改个人信息
     * @auther: xxf
     * Date: 2019/7/17
     * Time: 15:05
     * @param $id
     * @param $data
     * @return array
     */
    public function editMemberInfo($id,$data)
    {
        $saveData = [
            'nickname' => $data['nickname'] ?? '',
            'avatar' => $data['avatar'] ?? '',
            'gender' => $data['gender'] ?? 0,
            'mobile' => $data['mobile'] ?? '',
        ];
        $tokenData = ['__token__' => isset($data['__token__']) ? $data['__token__'] : '',];
        $validateRes = $this->validate($this->validate, $saveData, $tokenData,'user');
        if ($validateRes['tag']) {
            $tag = $this
                ->where('id', $id)
                ->update($saveData);
            $validateRes['tag'] = $tag;
            $validateRes['message'] = $tag ? '修改成功' : '数据无变动，修改失败';
        }
    return $validateRes;
    }


    /**
     * Notes:微信登录注册
     * @auther: xxf
     * Date: 2019/7/18
     * Time: 11:53
     * @param $data
     * @return int|string
     */
    public function addUser($data)
    {
        $saveData = [
            'nickname' => $data['nickname'] ?? '',
            'avatar' => $data['avatar'] ?? '',
            'gender' => $data['gender'] ?? 0,
            'openid' => $data['openid'] ?? '',
        ];
        $id = $this->insertGetId($saveData);
        return $id;
    }


public function getInfoByOpenId($openid)
{
    $res = $this
        ->where('openid',$openid)
        ->find();
    return $res;
}




}

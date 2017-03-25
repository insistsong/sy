<?php
namespace app\admin\controller;
use think\Controller;

use app\admin\model\User;
use think\Session;
class Index extends Controller
{
    public function index()
    {
    	return $this->fetch('index/index');
    }

    public function login()
    {
        $user = $_POST['username'];
        $pwd = md5($_POST['password']);
        $user = User::getByUser_name($user);



        Session::set('name', $user['user_name']);
        Session::set('email', $user['email']);


        if (isset($user)) {
            if ($user['password'] === $pwd && $user['god'] === 1) {

                return json(['status' => 1, 'msg' => '登陆成功']);
                
            } else {
                return ['status' => 2, 'msg' => '用户名或密码错误'];
            }
            
        } else {
            return ['status' => 2, 'msg' => '用户名或密码错误'];
        }
    }

    

    public function home()
    {
    	return $this->fetch('index/home');
    }


    public function exit()
    {
        Session::clear();

        $this->redirect('admin/index/index');

    }


}

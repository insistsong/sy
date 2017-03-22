<?php
namespace app\admin\controller;
use think\Controller;
class Index extends Controller
{
    public function index()
    {
    	return view();
    }

    public function login()
    {
    	
    	$user = $_POST['username'];
    	$pwd = md5($_POST['password']);
    	
    	if ($user == 'song') {
    		if ($pwd == md5('xiangnan')) {
    			return view('index/home');
    		} else {
    			return $this->error('用户名密码错误');
    		}

    	} else {
    		return $this->error('用户名错误');
    	}
    }

    public function home()
    {
    	return view('index/home');
    }



}

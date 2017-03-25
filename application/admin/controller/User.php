<?php
namespace app\admin\controller;

use think\Controller;
use think\View;
use think\Session;

use app\admin\model\User as UserModel;


class User extends Controller 
{
	//用户列表
	public function userList()
	{
		//分页功能

	    $list = UserModel::paginate(3);

	    $page = $list->render();
		$this->assign('list',$list);
		$this->assign('page', $page);
		return $this->fetch();
	}

	//显示要修改的用户信息
	public function userAlter($id='')
	{
		$user = UserModel::get($id);
		$this->assign('user', $user);
		return $this->fetch();
	}
	



	//禁止登录用户
	public function forbid()
	{
		$user = UserModel::where('allowlogin',0);

	    $list = $user->paginate(2);
	    $page = $list->render();
		$this->assign('list',$list);
		$this->assign('page', $page);
		return $this->fetch();


	}

	//更改用户信息
	public function update()
	{

		$list = $_POST;
		$user = new UserModel;

		$user->save([
			'user_name' => $list['name'],
			'qq' => $list['QQ'],
			'birthday' => $list['birthday'],
			'true_name' => $list['true_name'],
			'phone' => $list['phone'],
			'address' => $list['address'], 
			'sex' => $list['sex'],
			'age' => $list['age'],
			'description' => $list['description'],
			'allowlogin' => $list['allowlogin'], 
			],['user_id' => $list['user_id']]);

	        


	}

	//修改用户状态为禁止登录
	public function change()
	{	



		$user = new UserModel;
		$user->save([
				'allowlogin' => 0
			],['user_id' => $_POST['user_id']]);
	}

	//修改用户状态为允许刷新
	public function allow()
	{
		$user = new UserModel;
		$user->save([
				'allowlogin' => 1
			],['user_id' => $_POST['user_id']]);
	}
	

}



<?php
namespace app\admin\controller;

use think\Controller;
use think\View;
class User extends Controller 
{
	public function userList()
	{
		// 如果（指定操作）调用：

		return $this->fetch('userList');	

		// return view();
	}
}


// class User extends controller 
// {
// 	public function userList()
// 	{
// 		return view();
// 	}
// }


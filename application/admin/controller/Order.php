<?php
namespace app\admin\controller;

use think\Controller;

use think\View;

class order extends Controller
{
	public function order()
	{
		// 如果（指定操作）调用：

		return $this->fetch('order');	

		// return view();
	}
}
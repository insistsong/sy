<?php
namespace app\admin\controller;
use think\Controller;

class Community extends Controller
{
	public function pic()
	{
		return $this->fetch('pic');
	}





	public function video()
	{
		return $this->fetch();
	}





	public function article()
	{
		return $this->fetch();
	}
}

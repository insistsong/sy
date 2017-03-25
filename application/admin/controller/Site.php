<?php
namespace app\admin\controller;

use think\Controller;

use think\Session;

class site extends Controller
{
	public function billboard()
	{
		return $this->fetch();
	}

	public function links()
	{
		return $this->fetch();
	}

	public function siteInfo()
	{
		return $this->fetch();
	}

}
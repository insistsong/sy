<?php
namespace app\admin\controller;

use think\Controller;

class Forbid extends Controller
{
	public function forbid()
	{
		return $this->fetch();
	}
}
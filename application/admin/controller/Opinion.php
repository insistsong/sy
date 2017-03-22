<?php
namespace app\admin\controller;

use think\Controller;

class Opinion extends Controller
{
	public function solve()
	{
		return $this->fetch();
	}

	public function unsolved()
	{
		return $this->fetch();
	}

}
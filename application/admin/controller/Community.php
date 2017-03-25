<?php
namespace app\admin\controller;
use think\Controller;
use think\Session;
use think\View;

use app\admin\model\Picture;
use app\admin\model\User;


class Community extends Controller
{

	//管理图片
	public function pic(Picture $pic)
	{
		$data = $pic->selectInfo();
		$page = $data->render();
		$data = $data->toArray();
		foreach($data['data'] as $key=>$value)
		{	
			//显示每页要展示的缩略图
			$data['data'][$key]['picture'] = 'http://sy.mengtiancai.com/uploads/picture/'.json_decode($value['picture'])[0];

			//通过user表的查询获得用户名称;
			$data['data'][$key]['user_id'] = $pic->select($data['data'][$key]['user_id']);

		}

		$this->assign('page', $page);
		$this->assign('data', $data['data']);

		return $this->fetch();
	}


	public function picinfo()
	{

		$info = Picture::get($_GET);
		dump($info);
		return $this->fetch();
	}


	//管理视频
	public function video()
	{
		return $this->fetch();
	}




	//管理
	public function article()
	{
		return $this->fetch();
	}
}

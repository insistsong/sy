<?php
namespace app\index\controller;

use app\index\model\User;
use think\View;
use app\index\model\Article;
use app\index\model\Picture;
use app\index\model\Video;

use think\Controller;
use think\Db;
class Index extends Controller
{
	/**
	 * 这是首页
	 */ 
	public function test()
	{
		$this->assign('title','首页 - 食域');

		return $this->fetch();
	}
	public function index()
	{
		//获得首页的图片信息，并处理
		$pic = Picture::indexPic();
		foreach($pic as $key=>$value)
		{
			$pic[$key] = $value->toArray();
		}
		foreach ($pic as $key=>$value)
		{
			//dump($pic[$key]);
			//取出picture字段的内容
			 $json = $pic[$key]['picture'];
			// dump($json);
			$picture = json_decode($json);
			//dump($picture);
			foreach ($picture as $k=>$va)
			{
				$picture[$k] = 'http://sy.mengtiancai.com/uploads/picture/'.$va;
			}
				
			$pic[$key]['picture'] = $picture;
		}

		

		//获得首页的文章页信息，并处理
		$article = Article::indexArt();
		foreach($article as $key=>$value)
		{
			$article[$key] = $value->toArray();
		}
		$user = User::indexUser();
		foreach($user as $key=>$value)
		{
			$user[$key] = $value->toArray();
		}
		
		//dump($article);
		// //获得首页的视频页信息，并处理
		$video = Video::indexVideo();
		foreach($video as $key=>$value)
		{
			$video[$key] = $value->toArray();
		}
		//dump($video);
		//die();
		$this->assign('title','首页 - 食域');
		$this->assign('pic',$pic);
		$this->assign('article',$article);
		$this->assign('video',$video);
		$this->assign('user',$user);
		return $this->fetch();
	}

}

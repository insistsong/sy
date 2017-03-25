<?php
namespace app\index\controller;
use think\Session;
use think\Controller;
use think\Request;
use think\Validate;
use think\Db;
use think\Image;


use app\index\model\Picture;
use app\index\model\Video;

use app\index\model\Article;


use app\index\controller\Auth;

use app\index\model\User as UserModel;
class User extends Auth
{

	protected $is_check_login = ['*'];
	
	public function upload(UserModel $user)
	{
		$id = Session::get('id');
		$data = $user->profile($id);
		$pic =$data['collect_pic'];
		if ($pic == null){
			$pic = 0;
		}else{
			 $pic = json_decode($pic);
			 foreach($pic as $key=>$value)
			 {
			 	if ($value == '')
			 	{
			 		unset($pic[$key]);
			 	}
			 }
			 $pic = count($pic);
		}
		$art =$data['collect_article'];
		if ($art == null){
			$art = 0;
		}else{
			 $art = json_decode($art);
			 foreach($art as $key=>$value)
			 {
			 	if ($value == '')
			 	{
			 		unset($art[$key]);
			 	}
			 }
			 $art = count($art);
		}

		
		$video =$data['collect_video'];
		if ($video == null){
			$video = 0;
		}else{
			 $video = json_decode($video);
			 foreach($video as $key=>$value)
			 {
			 	if ($value == '')
			 	{
			 		unset($video[$key]);
			 	}
			 }
			 $video = count($video);
		}
		
		
		$collect = $pic + $video + $art;
		$data['collect'] = $collect;
		$this->assign('info',$data);
		$this->assign('title','美食上传 - 食域');
		return $this->fetch();
	}
	//处理上传图片
	public function doPic(Picture $picture)
	{
	// 获取表单上传文件

		// $files = request()->file('image');
		$img = [];
		$params = request()->param();
		$id = Session::get('id');
		$params['user_id'] = $id; 
		//dump($params);
		$files = request()->file('picture');
		if ($files == null)
		{
			return '1';//1表示没有选中图片
		}

		 //dump($files);
		
			foreach($files as $file){
			// 移动到框架应用根目录/public/uploads/ 目录下
			$info = $file->move(ROOT_PATH . 'public' . DS . 'uploads/picture');
				if($info){
				
				$image = \think\Image::open(ROOT_PATH . 'public' . DS . 'uploads/picture/'.$info->getSavename());
				// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.png
				$image->thumb(300, 300,\think\Image::THUMB_FIXED)->save(ROOT_PATH . 'public' . DS . 'uploads/picture/'.$info->getSavename());
				$img[] =$info->getSavename();
				}else{
				// 上传失败获取错误信息
				echo $file->getError();
				}
			}
		$params['picture'] = json_encode($img);
		$data = $picture->doPic($params);
		if ($data)
		{
			$this->success('发表成功',url("index/picture/picInfo")."?id=$data");
		}

	}
	//处理上传图文
	public function doArticle(Article $article)
	{
		$params = request()->param();
		$id = Session::get('id');
		$params['user_id'] = $id; 

		$pic = request()->file('artPic');
		//dump($pic);
		if ($pic !== null)
		{
			$info = $pic->move(ROOT_PATH . 'public' . DS . 'uploads/article');
			if($info){
					$pic =  $info->getSaveName();
					$params['picture'] = 'http://sy.mengtiancai.com/uploads/article/'.$pic;
				}else{
					return  json($file->getError());
				}
		}else{
			return '没有上传封面';//no picture 
		}
		
		$data = $article->doArticle($params);
		if ($data)
		{
			$this->success('文章上传成功',url("index/article/artInfo")."?id=$data");
		}else{
			$this->error('服务器开小差,文章上传失败，请返回重试');
		}
	}
	//处理上传视频
	public function doVideo(Video $video)
	{
		$params = request()->param();
		$id = Session::get('id');
		$params['user_id'] = $id; 
		//dump($params);
		$pic = request()->file('picture');
		$videoInfo = request()->file('video');
		if ($pic !== null)
		{
			$info = $pic->move(ROOT_PATH . 'public' . DS . 'uploads/video');
			if($info){
					$pic =  $info->getSaveName();
					$params['picture'] ='http://sy.mengtiancai.com/uploads/video/'.$pic;
				}else{
					return  json($file->getError());
				}
		}else{
			return '1';//no picture 
		}

		if ($video !== null)
		{
			$info = $videoInfo->move(ROOT_PATH . 'public' . DS . 'uploads/video');
			if($info){
					$pic = $info->getSaveName();
					$params['video'] ='http://sy.mengtiancai.com/uploads/video/'.$pic;
				}else{
					return  json($file->getError());
				}
		} else{
			return '2';//no video
		}

		//使用video模型来进行更新数据
		$data = $video->doVideo($params);
		if ($data)
		{
			$this->success('视频上传成功',url("index/video/videoInfo")."?id=$data");
		}else{
			$this->error('服务器开小差,视频上传失败，请返回重试');
		}


		//dump($files);
	}
	public function profile(UserModel $user)
	{
		//获取资料
		$id = Session::get('id');
		$data = $user->profile($id);
		$pic = Db::table('sy_picture')->where('user_id',$id)->select();
		$picNum = count($pic);
		$video = Db::table('sy_video')->where('user_id',$id)->select();
		$videoNum = count($video);
		$art = Db::table('sy_article')->where('user_id',$id)->select();
		$artNum = count($art);
		$push = $picNum + $videoNum + $artNum;


		//获取收藏的视频图片文章信息等
		$collectArt = $this->collectArt();
		$collectPic = $this->collectPic();
		$collectVideo = $this->collectVideo();
		$collectArtNum = count($collectArt);
		$collectPicNum = count($collectPic);
		$collectVideoNum = count($collectVideo);
		$collectNum = $collectArtNum + $collectPicNum + $collectVideoNum;
		$this->assign('collectNum',$collectNum);
		//dump($collectNum);



		//获取粉丝信息
		$demo = $this->countFans();
		$fans = count($demo);
		$this->assign('fans',$fans);


		//获取关注人信息
		$demo = $this->countFollow();
		//dump($demo);
		$follows = count($demo);
		$this->assign('follow',$follows);



		//获得好友信息
		$friends = $this->countFriends();
		//计算好友数量
		if ($friends == '')
		{
			$friend = 0;
		}else{
			$friend = count($friends);
		}
		$demo = [];
		//取出好友信息
		if ($friend !== 0){
			if (is_array($friends))
			{
				foreach ($friends as $value)
				{
					$demo[] = Db::table('sy_user')->where('user_id',$value)->find();
				}
			}else{
				$demo[] = Db::table('sy_user')->where('user_id',$friends)->find();
			}
			
		}
		$this->assign('friend',$friend);
		$this->assign('info',$data);
		$this->assign('push',$push);
		$this->assign('title','个人资料 - 食域');
		return $this->fetch();
	}
	public function savePro(UserModel $user)
	{
		$id = Session::get('id');
		
		$param = request()->param();
		$params = [];
		foreach ($param as $key=>$value)
		{
			if ($value !== '' )
			{
				$params[$key] = $value;
			}
		}
		$params['id'] = $id; 
		$file = request()->file('picture');
		if ($file !== null){
			$info = $file->move(ROOT_PATH . 'public' . DS . 'uploads/head');
			if($info){
					
					$pic =  $info->getSaveName();
					$image = \think\Image::open(ROOT_PATH . 'public' . DS . 'uploads/head/'.$info->getSavename());
					// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.png
					$image->thumb(300, 300,\think\Image::THUMB_FIXED)->save(ROOT_PATH . 'public' . DS . 'uploads/head/'.$info->getSavename());
					// 输出 42a79759f284b767dfcb2a0197904287.jpg
					//echo $info->getFilename();
					$params['picture'] ='http://sy.mengtiancai.com/uploads/head/'.$pic;
				}else{
				// 上传失败获取错误信息
					return  json($file->getError());
				}
		}
		// 获取表单上传文件 例如上传了001.jpg
		// 移动到框架应用根目录/public/uploads/ 目录下
		
		
		//dump($params);
		$data = $user->savePro($params);
		if ($data)
		{
			$this->success('资料设置成功，将返回首页继续旅程',url('index/index/index'));
		}else{
			$this->error('资料设置失败，请返回重试');
		}

	}
	public function selfInfo(UserModel $user)
	{

		//$id = request()->param('id');
		$id = Session::get('id');
		//获取图片信息
		$pic = Picture::selfPic($id);
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
			// dump($pic[$key]);
			// die();
		}
		//获取个人信息
		$data = $user->profile($id);
		$pic1 = Db::table('sy_picture')->where('user_id',$id)->select();
		$picNum = count($pic1);
		$video = Db::table('sy_video')->where('user_id',$id)->select();
		$videoNum = count($video);
		$art = Db::table('sy_article')->where('user_id',$id)->select();
		$artNum = count($art);
		$push = $picNum + $videoNum + $artNum;
		//获取发表的文章视频图片信息
		$art = Db::table('sy_article')->where('user_id',$id)->select();
		foreach($art as $key=>$value)
		{
			$art[$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
		}
		$video = Db::table('sy_video')->where('user_id',$id)->select();
		foreach($video as $key=>$value)
		{
			$video[$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
		}


		//获取收藏的视频图片文章信息等
		$collectArt = $this->collectArt();

		$collectPic = $this->collectPic();
		foreach ($collectPic as $k=>$v)
		{
			$c_pic = json_decode($v['picture']);
			foreach($c_pic as $key=>$value)
			{
				$c_pic[$key] = 'http://sy.mengtiancai.com/uploads/picture/'.$value;
			}
			$collectPic[$k]['picture'] = $c_pic;
		}
		$collectVideo = $this->collectVideo();
		$collectArtNum = count($collectArt);
		$collectPicNum = count($collectPic);
		$collectVideoNum = count($collectVideo);
		$collectNum = $collectArtNum + $collectPicNum + $collectVideoNum;
		$this->assign('collectVideo',$collectVideo);
		$this->assign('collectPicture',$collectPic);
		$this->assign('collectArticle',$collectArt);
		$this->assign('collectNum',$collectNum);
		//dump($collectNum);



		//获取粉丝信息
		$demo = $this->countFans();
		$fans = count($demo);
		$this->assign('fansInfo',$demo);
		$this->assign('fans',$fans);


		//获取关注人信息
		$demo = $this->countFollow();
		//dump($demo);
		$follows = count($demo);
		$this->assign('followInfo',$demo);
		$this->assign('follow',$follows);



		//获得好友信息
		$friends = $this->countFriends();
		//计算好友数量
		if ($friends == '')
		{
			$friend = 0;
		}else{
			$friend = count($friends);
		}
		$demo = [];
		//取出好友信息
		if ($friend !== 0){
			if (is_array($friends))
			{
				foreach ($friends as $value)
				{
					$demo[] = Db::table('sy_user')->where('user_id',$value)->find();
				}
			}else{
				$demo[] = Db::table('sy_user')->where('user_id',$friends)->find();
			}
			
		}
		$this->assign('friendInfo',$demo);
		$this->assign('friend',$friend);
		//传值
		
		$this->assign('info',$data);
		$this->assign('push',$push);
		$this->assign('title','个人作品 - 食域');
		$this->assign('pic',$pic);
		$this->assign('art',$art);
		$this->assign('video',$video);
		return $this->fetch();
	}
	public function guanAdd()
	{
		$request = Request::instance();
		//关注人
		$id = Session::get('id');
		//被关注人
		$uid = $request->param('uid');
		//处理关注人列表信息
		$data = Db::table('sy_user')->where('user_id',$id)->find();
		//follow字段信息
		$follow = $data['follow'];		
		if ($follow == null)
		{
			$follow= json_encode($uid);
		}else{
			$follow = json_decode($follow);
			 if (is_array($follow) && (!in_array($uid,$follow))){
			 	array_push($follow,$uid);
				$follow = json_encode($follow);
			 }else if ($follow !== $uid){
			 	$array = [$follow,$uid];
			 	$follow = json_encode($array);
			 }		
		}
		//处理被关注人信息
		$data = Db::table('sy_user')->where('user_id',$uid)->find();
		$fans = $data['fans'];		
		if ($fans == null)
		{
			$fans= json_encode($id);
		}else{
			$fans = json_decode($fans);
			 if (is_array($fans) && (!in_array($id,$fans))){
			 	array_push($fans,$id);
				$fans = json_encode($fans);
			 }else if ($fans !== $id){
			 	$array = [$fans,$id];
			 	$fans = json_encode($array);
			 }		
		}
		//更新关注人的follow字段
		$data2 = Db::table('sy_user')->where('user_id', $id)->update(['follow'=>$follow]);

		//更新了被关注人的fans字段
		$data1 = Db::table('sy_user')->where('user_id', $uid)->update(['fans'=>$fans]);
		if ($data1 && $data2){
			return 1;//代表关注成功
		}else{
			return 0;//代表关注失败，需要重试
		}
	}
	//取消关注
	public function guanDel()
	{
		$request = Request::instance();
		//主动取消关注人
		$id = Session::get('id');
		//被取消关注人
		$uid = $request->param('uid');

	
		//处理主动关注人列表信息
		$data = Db::table('sy_user')->where('user_id',$id)->find();

		$follow = $data['follow'];
		$follow = json_decode($follow);

		if ($follow == $uid)
		{
			$follow = '';
		}else{
			$key = array_search($uid,$follow);
			unset($follow[$key]);
		}

		$follow = json_encode($follow);
		// dump($follow);
		//处理被取消关注人的信息
		$data = Db::table('sy_user')->where('user_id',$uid)->find();

		$fans = $data['fans'];
		$fans = json_decode($fans);
		if ($fans == $id)
		{	
			$fans = '';
		}else{
			$key = array_search($id,$fans);
	
			unset($fans[$key]);
		}

		$fans = json_encode($fans);
		//处理被取消人的fans字段
		$data1 = Db::table('sy_user')->where('user_id', $uid)->update(['fans'=>$fans]);
		//处理主动取消关注人的follow字段
		$data2 = Db::table('sy_user')->where('user_id', $id)->update(['follow'=>$follow]);
		if ($data1 && $data2){
			return 1;//成功取消关注
		}else{
			return 0;//取消关注失败，重试
		}
	}
	//粉丝
	public function countFans()
	{
		$demo = [];
		$id = Session::get('id');
		$data = Db::table('sy_user')->where('user_id',$id)->find();
		$fans = $data['fans'];
		$fan = json_decode($fans);
		if ($fan !== null){
			if (!is_array($fan)){
				if ($fan !== ''){
					$fans = $fan;
				}
			}else{
				foreach($fan as $key=>$value)
				{
					if ($value == '')
					{
						unset($fan[$key]);
					}
				}
				$fans = $fan;
			}

			
			if (count($fans) == 0){
				$fans = 0;
			}else{
				if (count($fans) == 1 && !is_array($fans))
				{
					$demo[] = Db::table('sy_user')->where('user_id',$fans)->find();	
				}else{
					foreach ($fans as $value)
					{
						$demo[] = Db::table('sy_user')->where('user_id',$value)->find();
					}
				}
				
			}
			
		}else{
			$fans = 0;
		}
		return $demo;
	}
	public function fans()
	{
		$id = Session::get('id');
		$data = Db::table('sy_user')->where('user_id',$id)->find();
		$demo = $this->countFans();
		$fans = count($demo);
		$this->assign('demo',$demo);
		$this->assign('fans',$fans);
		$this->assign('title',$data['user_name'].' - 食域');
		return $this->fetch();
	}
	//关注
	public function follow()
	{
		$id = Session::get('id');
		$data = Db::table('sy_user')->where('user_id',$id)->find();
		$demo = $this->countFans();
		$follows = count($demo);
		$this->assign('demo',$demo);
		$this->assign('follow',$follows);
		$this->assign('title',$data['user_name'].' - 食域');
		return $this->fetch();
	}
	//统计关注人信息
	public function countFollow()
	{
		$demo = [];
		$id = Session::get('id');
		$data = Db::table('sy_user')->where('user_id',$id)->find();
		$follow= $data['follow'];
		$follow = json_decode($follow);
		if ($follow !== null){
			if (!is_array($follow)){
				if ($follow !== ''){
					$follows = $follow;
				}
			}else{
				foreach($follow as $key=>$value)
				{
					if ($value == '')
					{
						unset($follow[$key]);
					}
				}
				$follows = $follow;
			}
			
			if (count($follows) == 0){
				$follows = 0;

			}else{
				if (count($follows) == 1 && !is_array($follows))
				{

					$demo[] = Db::table('sy_user')->where('user_id',$follows)->find();	
					
				}else{
					foreach ($follows as $value)
					{
						$demo[] = Db::table('sy_user')->where('user_id',$value)->find();
					}
				}
				
			}
	
		}else{
			$follows = 0;
		}

		return $demo;
	}
	//互粉
	public function friend()
	{
		$id = Session::get('id');
		$data = Db::table('sy_user')->where('user_id',$id)->find();
		$friends = $this->countFriends();
		// dump($friends);
		//计算好友数量
		if ($friends == '')
		{
			$friend = 0;
		}else{
			$friend = count($friends);
		}
		$demo = [];
		//取出好友信息
		if ($friend !== 0){
			if (is_array($friends))
			{
				foreach ($friends as $value)
				{
					$demo[] = Db::table('sy_user')->where('user_id',$value)->find();
				}
			}else{
				$demo[] = Db::table('sy_user')->where('user_id',$friends)->find();
			}
			
		}
		$this->assign('demo',$demo);
		$this->assign('friend',$friend);
		$this->assign('title','好友--'.$data['user_name'].' - 食域');
		return $this->fetch();
	}
	//计算好友数量
	public function countFriends()
	{
		$demo = [];
		$id = Session::get('id');
		$data = Db::table('sy_user')->where('user_id',$id)->find();
		$follow= $data['follow'];
		$follow = json_decode($follow);
		if ($follow !== null){
			if (!is_array($follow)){
				if ($follow !== ''){
					$follows = $follow;
				}
			}else{
				foreach($follow as $key=>$value)
				{
					if ($value == '')
					{
						unset($follow[$key]);
					}
				}
				$follows = $follow;
			}
		}else{
			$follows = 0;
		}

		$fans = $data['fans'];
		$fan = json_decode($fans);
		if ($fan !== null){
			if (!is_array($fan)){
				if ($fan !== ''){
					$fans = $fan;
				}
			}else{
				foreach($fan as $key=>$value)
				{
					if ($value == '')
					{
						unset($fan[$key]);
					}
				}
				$fans = $fan;
			}
		}else{
			$fans = 0;
		}
		$friend = 0;
		if (($fans!==0) && ($follows !==0))
		{
			if (count($fans) == 1)
			{
					if (($fans == $follows) || (in_array($fans,$follows)))
					{
						
						$friends = $fans;
						$friend = 1;
					}else{
						$friends ='';
					}
			}else if((count($follows) == 1) && (!is_array($follows))){
				
				if ((in_array($follows,$fans)))
				{
					
					$friends = $follows;
					$friend = 1;
				}else{
					$friends ='';
				}
			}else{
				
				$friend = array_intersect($fans,$follows);
				$friends = $friend;
				
				$friend = count($friend);

			}
		}else{
			
			$friends ='';
		}
		return $friends;
	}
	//统计收藏
	public function collect()
	{
		//统计收藏照片数量
		$id = Session::get('id');
		$data = Db::table('sy_user')->where('user_id',$id)->find();
		$pic = $data['collect_pic'];
		$pic = json_decode($pic);
		if ($pic !== null){
			if (!is_array($pic)){
				if ($pic !== ''){
					$pics[] = $pic;
				}
			}else{
				foreach($pic as $key=>$value)
				{
					if ($value == '')
					{
						unset($pic[$key]);
					}
				}
				$pics = $pic;
			}
		}else{
			$pics =[];
		}
		
		if (!empty($pics))
		{
			foreach ($pics as $key => $value) {
				$collectPic[] = Picture::collectPic($value)->toArray();
			}
		}else{
			$collectPic= [];
		}
		
		//dump($collectPic);
	
	

		//统计收藏文章数量
		$art = $data['collect_article'];
		$art = json_decode($art);
		if ($art !== null){
			if (!is_array($art)){
				if ($art !== ''){
					$arts[] = $art;
				}
			}else{
				foreach($art as $key=>$value)
				{
					if ($value == '')
					{
						unset($art[$key]);
					}
				}
				$arts = $art;
			}
		}else{
			$arts =[];
		}
		if (!empty($arts))
		{
			foreach ($arts as $key => $value) {
				$collectArt[] = Article::collectArt($value)->toArray();
			}
		}else{
			$collectArt= [];
		}
		//dump($collectArt);
		//统计视频数量
		$video = $data['collect_video'];
		$video = json_decode($video);
		if ($video !== null){
			if (!is_array($video)){
				if ($video !== ''){
					$videos[] = $video;
				}
			}else{
				foreach($video as $key=>$value)
				{
					if ($value == '')
					{
						unset($video[$key]);
					}
				}
				$videos = $video;
			}
		}else{
			$videos =[];
		}
		if (!empty($videos))
		{
			foreach ($videos as $key => $value) {
				$collectVideo[] = Video::collectVideo($value)->toArray();
			}
		}else{
			$collectVideo= [];
		}

		$this->assign('title','收藏 - 食域');
		$this->assign('collectVideo',$collectVideo);
		$this->assign('collectPicture',$collectPic);
		$this->assign('collectArticle',$collectArt);
		return $this->fetch();
	}

	//分开统计收藏数量，json数据转化的时候出现无法解决错误，现弃用
	public function collectPic()
	{
		$id = Session::get('id');
		$data = Db::table('sy_user')->where('user_id',$id)->find();
		$pic = $data['collect_pic'];
		$pic = json_decode($pic);
		if ($pic !== null){
			if (!is_array($pic)){
				if ($pic !== ''){
					$pics[] = $pic;
				}
			}else{
				foreach($pic as $key=>$value)
				{
					if ($value == '')
					{
						unset($pic[$key]);
					}
				}
				$pics = $pic;
			}
		}else{
			$pics =[];
		}
		
		if (!empty($pics))
		{
			foreach ($pics as $key => $value) {
				$collectPic[] = Picture::collectPic($value)->toArray();
			}
		}else{
			$collectPic= [];
		}
		return $collectPic;
	}
	public function collectArt()
	{

		$id = Session::get('id');
		$data = Db::table('sy_user')->where('user_id',$id)->find();
		$art = $data['collect_article'];
		$art = json_decode($art);
		if ($art !== null){
			if (!is_array($art)){
				if ($art !== ''){
					$arts[] = $art;
				}
			}else{
				foreach($art as $key=>$value)
				{
					if ($value == '')
					{
						unset($art[$key]);
					}
				}
				$arts = $art;
			}
		}else{
			$arts =[];
		}
		if (!empty($arts))
		{
			foreach ($arts as $key => $value) {
				$collectArt[] = Article::collectArt($value)->toArray();
			}
		}else{
			$collectArt= [];
		}
		return $collectArt;

	}
	public function collectVideo()
	{

		$id = Session::get('id');
		$data = Db::table('sy_user')->where('user_id',$id)->find();
		$video = $data['collect_video'];
		$video = json_decode($video);
		if ($video !== null){
			if (!is_array($video)){
				if ($video !== ''){
					$videos[] = $video;
				}
			}else{
				foreach($video as $key=>$value)
				{
					if ($value == '')
					{
						unset($video[$key]);
					}
				}
				$videos = $video;
			}
		}else{
			$videos =[];
		}
		if (!empty($videos))
		{
			foreach ($videos as $key => $value) {
				$collectVideo[] = Video::collectVideo($value)->toArray();
			}
		}else{
			$collectVideo= [];
		}
		return $collectVideo;
	}
	

}

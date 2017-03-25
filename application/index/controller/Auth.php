<?php
namespace app\index\controller;

use think\Session;
use think\Controller;
use think\Request;
use think\Db;
use think\Validate;
use app\index\model\User;
use app\index\model\Picture;
class Auth extends Controller
{

	protected $is_check_login = [''];

	public function _initialize()
	{
		if(!$this->checkLogin() && (in_array(Request::instance()->action(), $this->is_check_login) || $this->is_check_login[0] == '*'))
		{
			$this->error('您还没有登录请先登录', url('index/auth/login'));
		}
	}

	public function checkLogin()
	{
		return session('?user');
	}


	public function login()
	{
		$this->assign('title', '登录页面 - 食域');
		return $this->fetch();
	}

	public function doLogin(User $user)
	{
		
		$ip = Request::instance()->ip();
		$data = [
			'email' => input('post.email'),
			'password' => input('post.password')
		];
		

		$info =$user->doLogin($data);
		if (array_key_exists('user',$info)){
			Session::set('user',$info['user']);
			Session::set('id',$info['id']);
			
		}  
		return json($info);
		
	}

	public function test()
	{
		//echo md5('shiqu52');
		// $data = [
		// 	'username' => 'post.username',
		// 	'email' => 'post.email',
		// 	'password' => 'post.password',
		// 	'ip' => '1.1.2.3'
		// 	];
		// $user->doLogin($data);
		$this->assign('title',1111);
		return $this->fetch();
	}

	public function logout()
	{
		session(null);

		$this->success('退出成功！',url('index/index/index'));
	}


	public function register()
	{
		$this->assign('title', '注册页面 - 食域');

		return $this->fetch();
	}

	public function doRegister(User $user)
	{
		//定义验证规则
		if (Request::instance()->ip() == '0.0.0.0'){
			$ip = '127.0.0.1';
		}else{
			$ip = Request::instance()->ip();
		}
		$validate = new Validate([
			'username' => 'require|length:6,18',
			'email' => 'email',
			'password' => 'require|length:6,18'
			]);
			$data = [
			'username' => input('post.username'),
			'email' => input('post.email'),
			'password' => input('post.password'),
			'ip' => $ip
			];
			if (!$validate->check($data)) {
				$error = $validate->getError();
				if (strpos($error,'mail')){
					return json(['status' => 0, 'msg' => 'email设置不符合规范']);
				}
				if (strpos($error,'sername')){
					return json(['status' => 1, 'msg' => '用户名设置不符合规范，应为6-18位之间']);
				}
				if (strpos($error,'assword')){
					return json(['status' => 2, 'msg' => '密码设置不符合规范，应为6-18位之间']);
				}
				
			}else{
				//$data['password'] = md5($data['password']);
				$result = $user->doRegister($data);
				if (array_key_exists('user',$result)){
						Session::set('user',$result['user']);
						Session::set('id',$result['id']);

						
					}  
				return json($result);
				 //if ($result['status'] == 4){
				 // 	$this->turn();
				 // 	//$this->success('注册成功，以后每天登陆加两积分哦~~~~~',url('index/index/index'));
				 // 	//return json(['status' => 4, 'msg' => '注册成功,以后每天登陆加两积分哦~~~~~', 'redirect_url' => url('index/index/index') ]);
				 // }else{
				 // 	return json($result);
				 // }
				// if($result){
				// 	return json(['status' => 1, 'msg' => '注册成功', 'redirect_url' => url('index/index/index') ]);
				// }else{
				// 	return json(['status' => 0, 'msg' => '注册失败，请刷新页面重试 ！']);

				// }
			}
	}
	public function turn()
	{
		$this->success('注册成功,以后每天登陆加两积分哦~~~~~',url('index/index/index'));
	}
	//展示图片详情
	public function picInfo()
	{
		$this->assign('title','图片详情 - 食域');
		return $this->fetch();
	}

	//展示视频详情
	public function videoInfo()
	{

	}
	//展示用户信息
	public function userInfo(User $user)
	{
		//id是需要展示信息的用户id
		$request = Request::instance();
		$id = $request->param('id');
		//uid判断是否是游客
		$uid = Session::get('id');

		//考虑做个判定是否是本人
		if ($uid == $id){
			$true = 1;
		}else{
			$true =0;
		}
		$data = $user->profile($id);
		//判断是否是粉丝
		$fan = $data['fans'];
	
		if (($uid == null) || ($fan == null))
		{
			$fans = 0;
			
		}else{
			$fan = json_decode($fan);
				
			if ((!is_array($fan)) && $fan == $uid){
				$fans = 1;
			
			}else if(is_array($fan) && in_array($uid,$fan)){
				$fans = 1;
				
			}else{
				$fans = 0;
				
			}
		}
		
		$pic = Db::table('sy_picture')->where('user_id',$id)->select();
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
			$pic[$key]['create_time'] = date("Y-m-d H:i:s",$value['create_time']);
			// dump($pic[$key]);
			// die();
		}
		$picNum = count($pic);
		$video = Db::table('sy_video')->where('user_id',$id)->select();
		foreach($video as $key=>$value)
		{
			$video[$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
		}
		$videoNum = count($video);
		$art = Db::table('sy_article')->where('user_id',$id)->select();
		foreach($art as $key=>$value)
		{
			$art[$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
		}
		$artNum = count($art);
		$push = $picNum + $videoNum + $artNum;
		$this->assign('info',$data);
		$this->assign('push',$push);
		$this->assign('true',$true);
		$this->assign('fans',$fans);
		$this->assign('pic',$pic);
		$this->assign('art',$art);
		$this->assign('video',$video);
		$this->assign('title',$data['user_name']."资料 - 食域");
		return $this->fetch();
	}
	public function contact()
	{
		$this->assign('title','联系我们 - 食域');
		return $this->fetch();
	}

}




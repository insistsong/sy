<?php

namespace app\index\model;

use think\Session;
use think\Model;
use traits\model\SoftDelete;
class User extends Model
{
	//public $girl = '1111111';
	use SoftDelete;
	protected $deletetime = 'delete_time';
	protected $autoWriteTimestamp = true;
	public function getCreateTimeAttr($value)
	{
		return date('Y-m-d H:i:s',$value);
	}
	public function getStatusAttr($value)
	{
		$status = [0 =>'删除',1 =>'正常', 2=>'待审核', 3=>'禁用'];
		return $status[$value];
	}
	public function doLogin($data)
	{
		$email = $data['email'];
		$password = md5($data['password']);

		$res = self::where('email',$email)->find();
		//dump($res);
		// die();
		if ($res !== null){
			if ($res['password'] == $password){
				return  ['status' => 1, 'msg' => '登陆成功','user' =>$res['user_name'],'id'=>$res['user_id']];
				//Session::set('user',$res['username']);
				
			}else{
				return  ['status' => 2, 'msg' => '密码错误'];
			}
		}else{
			return  ['status' => 3, 'msg' => '用户不存在'];
		}
	}	
	public function doRegister($data)
	{
		$ip = $data['ip'];
		$email = $data['email'];
		$username = $data['username'];
		$password = md5($data['password']);
		$user = self::where('email',$email)->find();
		$time = time();
		//dump($user);
		//die();
		//echo md5(123);
		//var_dump($user);
		if ($user !== null)
		{
			return  ['status' => 3, 'msg' => '用户已存在'];//3为有此用户
		}else{
			$data = ['email'=>$email,'user_name'=>$username,'password'=>$password,'ip'=>$ip,'create_time'=>$time];
			$pass =  self::create($data);
			if ($pass)
			{
				$id = $pass->user_id;
				
				//return true;
				return  ['status' => 4, 'msg' => '验证成功','user' =>$username,'id'=>$id];//4为啥也没错验证通过
				//Session::set('user',$username);
			}else{
				return  ['status' => 5, 'msg' => '注册失败请重试'];//4为数据没有插入成功
			}
			
		}
	}

	public function profile($id)
	{
		return self::where('user_id',$id)->find();
	}
	public function savePro($params)
	{
		$id = $params['id'];
		unset($params['id']);
		$data = User::where('user_id', $id)->update($params);
		return $data;
	}
	//获得首页的用户信息
	static function indexUser()
	{
		$data = self::limit(3)->select();
		return $data;
	}

}
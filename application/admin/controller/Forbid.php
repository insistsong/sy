<?php
namespace app\admin\controller;

use think\Controller;
use think\Session;
use think\View;
use app\admin\model\Ip;
use think\Db;
class Forbid extends Controller
{



	// 添加禁止访问的ip
	public function add(Ip $data)
	{


		$ip = $_POST['ip'];
		$result = $_POST['result'];
		$data = $data->add($ip, $result);
		return $data;
	}
		
		

	//禁止访问IP
	public function  forbid(Ip $ip)
	{	

		$ip = $ip->index();
		$page = $ip->render();
		$this->assign('list', $ip);
		$this->assign('page', $page);
		return $this->fetch();
	}



	public function exitForbid($ip)
	{

		
		Db::table('sy_ip')
			->where('ip_id', $ip) ->update(['delete_time' => NULL]);
	}

	public function change()
	{
		Ip::destroy($_POST);
	}
}
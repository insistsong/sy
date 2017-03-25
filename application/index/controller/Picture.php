<?php
namespace app\index\controller;

use think\Session;
use think\Controller;
use think\Request;
use think\Validate;
use think\Paginator;
use think\Db;

use app\index\model\Picture as PicModel;
use app\index\model\Pic_comment as PicComment;

class Picture extends Controller
{
	//展现图片详情
	public function index(PicModel $pic)
	{
		$list = $pic->index();

		$pic = $list->toArray();
		//dump($pic);
		//die();
		$pic = $pic['data'];
		//dump($pic);
		//die();
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
		//提取最新的6条数据
		$new = Db::table('sy_picture')->order('pic_id','desc')->limit(6)->select();
		foreach ($new as $key=>$value)
		{
			//dump($pic[$key]);
			//取出picture字段的内容
			 $json = $new[$key]['picture'];
			// dump($json);
			$picture = json_decode($json);
			//dump($picture);
			foreach ($picture as $k=>$va)
			{
				$picture[$k] = 'http://sy.mengtiancai.com/uploads/picture/'.$va;
			}
				
			$new[$key]['picture'] = $picture;
			// dump($pic[$key]);
			// die();
		}
		// 把分页数据赋值给模板变量list
		$this->assign('pic', $pic);
		$this->assign('new', $new);
		$this->assign('list', $list);
		//$this->assign('data',$data);
		$this->assign('title','图片 - 食域');

		return $this->fetch();
	}

	
		//展示图片详情
	public function picInfo(PicModel $picture)
	{
		$request = Request::instance();
		$id = $request->param('id');

		$data = $picture->picInfo($id);
		$uid = $data->user_id;

		//取出点赞的ID进行分析
		$zid = $data->zan_id;

		$zid = json_decode($zid);
		$user = Session::get('id');
		if ($zid == null){
			$zan = 0;
		}
		if (($zid == $user)){
			$zan = 1;
		}else{
			$zan = 0;
		}
		if (is_array($zid) && in_array($user,$zid))
		{
			$zan = 1;
		}
		//取出收藏的id进行分析
		$cid = $data->collect_id;
		$cid = json_decode($cid);
		if ($cid == null){
			$collect = 0;
		}
		if (($cid == $user)){
			$collect = 1;
		}else{
			$collect = 0;
		}
		if (is_array($cid) && in_array($user,$cid))
		{
			$collect = 1;
		}

		//dump($uid);
		$user = Db::table('sy_user')->where('user_id',$uid)->find();
		//dump($user);
		$pic = json_decode($data['picture']);
		foreach($pic as $key=>$value)
		{
			$pic[$key] = 'http://sy.mengtiancai.com/uploads/picture/'.$value;
		}
		$data['picture'] = $pic;
		$count = count($pic);

		//查询评论信息
		// $comment= PicComment::comment($id);
		$comment = Db::table('sy_pic_comment')->where('pic_id',$id)->order('pc_id','asc')->select();
		$commentNum = count($comment);
		Db::table('sy_picture')->where('pic_id',$id)->update(['comment_num'=>$commentNum]);
		if($commentNum !== 0){
			foreach ($comment as $key => $value) {
				$comment[$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
				$uid = $comment[$key]['user_id'];
				$user = Db::table('sy_user')->where('user_id',$uid)->find();
				$comment[$key]['picture'] = $user['picture'];
				$comment[$key]['username'] = $user['user_name'];
			}
		}
		if($data){
			$this->assign('count',$count);
			$this->assign('commentNum',$commentNum);
			$this->assign('comment',$comment);
			$this->assign('zan',$zan);
			$this->assign('collect',$collect);
			$this->assign('title',$data['title'].' - 食域');
			$this->assign('data',$data);
			$this->assign('user',$user);
			return $this->fetch();
		}else{
			$this->error('服务器崩溃了~找不到您要的文件啦~~再看点别的吧~·',url('index/index/index'));
		}
	}
	//点赞增加
	public function zanAdd()
	{
		$request = Request::instance();
		$id = $request->param('id');
		$data = Db::table('sy_picture')->where('pic_id',$id)->find();
		$zan = $data['zan'];
		$num = $zan+1;
		$uid = Session::get('id');
		$zan = $data['zan_id'];
		if ($zan == null)
		{
			$zid = json_encode($uid);
		}else{
			$zan = json_decode($zan);
			 if (is_array($zan) && (!in_array($uid,$zan))){
			 	array_push($zan,$uid);
				$zid = json_encode($zan);
			 }else if ($zan !== $uid){
			 	$array = [$zan,$uid];
			 	$zid = json_encode($array);
			 }else if ($zan == $uid){
			 	$zid = json_encode($zan);
			 }			
		}
		
	
		Db::table('sy_picture')->where('pic_id', $id)->update(['zan' => $num,'zan_id'=>$zid]);
		return $num;
	}
	//取消点赞
	public function zanDel()
	{
		$request = Request::instance();
		$id = $request->param('id');
		$uid = Session::get('id');

		$data = Db::table('sy_picture')->where('pic_id',$id)->find();
		$zan = $data['zan_id'];
		$zid = json_decode($zan);
		if ($zid == $uid)
		{
			$zid = '';
		}else{
			$key = array_search($uid,$zid);
			unset($zid[$key]);
		}
		$zid = json_encode($zid);
		$zan = $data['zan'];
		$num = $zan-1;
		Db::table('sy_picture')->where('pic_id', $id)->update(['zan' => $num,'zan_id'=>$zid]);
		return $num;
	}
	//阅读量计算
	public function lookPic()
	{
		$request = Request::instance();
		$id = $request->param('id');
		$data = Db::table('sy_picture')->where('pic_id',$id)->find();
		$look = $data['look'];
		$num = $look + 1;
		Db::table('sy_picture')->where('pic_id', $id)->update(['look' => $num]);
		return $num;
	}
	//点击收藏
	public function collectAdd()
	{
		$request = Request::instance();
		//图片的id
		$id = $request->param('id');
		//更新图片表
		$data = Db::table('sy_picture')->where('pic_id',$id)->find();
		$coll = $data['collect'];
		$num = $coll+1;
		//此时用户的id
		$uid = Session::get('id');
		$collect = $data['collect_id'];
		if ($collect == null)
		{
			$cid = json_encode($uid);
		}else{
			$collect = json_decode($collect);
			 if (is_array($collect) && (!in_array($uid,$collect))){
			 	array_push($collect,$uid);
				$cid = json_encode($collect);
			 }else if ($collect !== $uid){
			 	$array = [$collect,$uid];
			 	$cid = json_encode($array);
			 }else if ($collect == $uid){
			 	$cid = json_encode($collect);
			 }			
		}
		//更新用户表
		$data = Db::table('sy_user')->where('user_id',$uid)->find();
		$collect = $data['collect_pic'];
		if ($collect == null)
		{
			$collect_pic = json_encode($id);
		}else{
			$collect = json_decode($collect);
			 if (is_array($collect) && (!in_array($id,$collect))){
			 	array_push($collect,$id);
				$collect_pic = json_encode($collect);
			 }else if ($collect !== $id){
			 	$array = [$collect,$id];
			 	$collect_pic = json_encode($array);
			 }else if ($collect == $id){
			 	$collect_pic = json_encode($collect);
			 }			
		}
		Db::table('sy_picture')->where('pic_id', $id)->update(['collect' => $num,'collect_id'=>$cid]);
		Db::table('sy_user')->where('user_id', $uid)->update(['collect_pic'=>$collect_pic]);
		return $num;
	}
	//取消收藏
	public function collectDel()
	{
		$request = Request::instance();
		$id = $request->param('id');
		$uid = Session::get('id');
		//更新图片表
		$data = Db::table('sy_picture')->where('pic_id',$id)->find();
		$collect = $data['collect_id'];
		$cid = json_decode($collect);
		if ($cid == $uid)
		{
			$cid = '';
		}else{
			$key = array_search($uid,$cid);
			unset($cid[$key]);
		}
		$cid = json_encode($cid);
		$coll = $data['collect'];
		$num = $coll-1;
		//更新用户表
		$data = Db::table('sy_user')->where('user_id',$uid)->find();
		$collect_pic = $data['collect_pic'];
		$user_cid = json_decode($collect_pic);
		if ($user_cid == $id)
		{
			$user_cid = '';
		}else{
			$key = array_search($id,$user_cid);
			unset($user_cid[$key]);
		}
		$collect_pic = json_encode($user_cid);
		
		Db::table('sy_picture')->where('pic_id', $id)->update(['collect' => $num,'collect_id'=>$cid]);
		Db::table('sy_user')->where('user_id', $uid)->update(['collect_pic'=>$collect_pic]);
		return $num;
	}
	public function comment(PicComment $PicComment)
	{
		$request = Request::instance();
		$id = $request->param('id');
		$comment = $request->param('content');
		$uid = Session::get('id');
		$params = [
			'user_id' =>$uid,
			'pic_id'  =>$id,
			'comment' =>$comment,
			'reply' => 0,
		];
		$data = $PicComment->saveComment($params);
		$user = Db::table('sy_user')->where('user_id',$uid)->find();
		Db::table('sy_picture')->where('pic_id', $id)->setInc('comment_num');
		$data['picture'] = $user['picture'];
		$data['username'] = $user['user_name'];
		return $data;
	}
	
}
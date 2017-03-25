<?php
namespace app\index\controller;

use think\Session;
use think\Controller;
use think\Request;
use think\Validate;
use think\Paginator;

use think\Db;

use app\index\model\Article as ArtModel;
use app\index\model\Art_comment as ArtComment;
class Article extends Controller
{
	//展示所有文章
	public function index(ArtModel $article)
	{
		$list = $article->index();

		// 把分页数据赋值给模板变量list
		$this->assign('list', $list);
		//$this->assign('data',$data);
		$this->assign('title','图文 - 食域');

		return $this->fetch();
	}
	//展示文章详情
	public function artInfo(ArtModel $article)
	{
		$request = Request::instance();
		$id = $request->param('id');

		$data = $article->artInfo($id);
		$uid = $data->user_id;
		//dump($uid);
		
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
		$user = Db::table('sy_user')->where('user_id',$uid)->find();
		//查询评论信息
		$comment = Db::table('sy_art_comment')->where('article_id',$id)->order('article_id','asc')->select();
		$commentNum = count($comment);
		Db::table('sy_article')->where('article_id',$id)->update(['comment_num'=>$commentNum]);
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
		$data = Db::table('sy_article')->where('article_id',$id)->find();
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
		
	
		Db::table('sy_article')->where('article_id', $id)->update(['zan' => $num,'zan_id'=>$zid]);
		return $num;
	}
	//取消点赞
	public function zanDel()
	{
		$request = Request::instance();
		$id = $request->param('id');
		$uid = Session::get('id');

		$data = Db::table('sy_article')->where('article_id',$id)->find();
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
		Db::table('sy_article')->where('article_id', $id)->update(['zan' => $num,'zan_id'=>$zid]);
		return $num;
	}
	//阅读量计算
	public function lookArt()
	{
		$request = Request::instance();
		$id = $request->param('id');
		$data = Db::table('sy_article')->where('article_id',$id)->find();
		$look = $data['look'];
		$num = $look + 1;
		Db::table('sy_article')->where('article_id', $id)->update(['look' => $num]);
		return $num;
	}
	//点击收藏
	public function collectAdd()
	{
		$request = Request::instance();
		//图片的id
		$id = $request->param('id');
		//更新图片表
		$data = Db::table('sy_article')->where('article_id',$id)->find();
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
		$collect = $data['collect_article'];
		if ($collect == null)
		{
			$collect_article = json_encode($id);
		}else{
			$collect = json_decode($collect);
			 if (is_array($collect) && (!in_array($id,$collect))){
			 	array_push($collect,$id);
				$collect_article = json_encode($collect);
			 }else if ($collect !== $id){
			 	$array = [$collect,$id];
			 	$collect_article = json_encode($array);
			 }else if ($collect == $id){
			 	$collect_article = json_encode($collect);
			 }			
		}
		Db::table('sy_article')->where('article_id', $id)->update(['collect' => $num,'collect_id'=>$cid]);
		Db::table('sy_user')->where('user_id', $uid)->update(['collect_article'=>$collect_article]);
		return $num;
	}
	//取消收藏
	public function collectDel()
	{
		$request = Request::instance();
		$id = $request->param('id');
		$uid = Session::get('id');
		//更新图片表
		$data = Db::table('sy_article')->where('article_id',$id)->find();
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
		$collect_article = $data['collect_article'];
		$user_cid = json_decode($collect_article);
		if ($user_cid == $id)
		{
			$user_cid = '';
		}else{
			$key = array_search($id,$user_cid);
			unset($user_cid[$key]);
		}
		$collect_article = json_encode($user_cid);
		
		Db::table('sy_article')->where('article_id', $id)->update(['collect' => $num,'collect_id'=>$cid]);
		Db::table('sy_user')->where('user_id', $uid)->update(['collect_article'=>$collect_article]);
		return $num;
	}
	public function comment(ArtComment $ArtComment)
	{
		$request = Request::instance();
		$id = $request->param('id');
		$comment = $request->param('content');
		$uid = Session::get('id');
		$params = [
			'user_id' =>$uid,
			'article_id'  =>$id,
			'comment' =>$comment,
			'reply' => 0,
		];
		$data = $ArtComment->saveComment($params);
		$user = Db::table('sy_user')->where('user_id',$uid)->find();
		Db::table('sy_article')->where('article_id', $id)->setInc('comment_num');
		$data['picture'] = $user['picture'];
		$data['username'] = $user['user_name'];
		return $data;
	}


	
}
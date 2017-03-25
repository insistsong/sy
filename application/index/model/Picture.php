<?php

namespace app\index\model;

use think\Paginator;
use think\Model;
use traits\model\SoftDelete;
class Picture extends Model
{
	//public $girl = '1111111';
	use SoftDelete;
	protected $deletetime = 'delete_time';
	protected $autoWriteTimestamp = true;
	public function getCreateTimeAttr($value)
	{
		return date('Y-m-d H:i:s',$value);
	}
	public function getTagAttr($value)
	{
		$tag = [1 =>'中餐', 2 =>'西餐', 3=>'饮品', 4=>'小吃', 5 =>'爆炒',6 =>'面食', 7=>'汤类', 8=>'油炸',9=>'糕点', 10=>'水果',11=>'烧烤',12=> '烤肉',13 =>'火锅',14=>'野菜',15=>'自助餐',16=>'面包',17=>'清真',18=>'快餐',19=>'料理',20=>'东南亚菜'];
		return $tag[$value];
	}
	//图片页的信息
	public function index()
	{
		$data = self::paginate(6);
		return $data;
	}
	//获取首页的照片信息
	static function indexPic()
	{
		$data = self::limit(4)->order('pic_id','asc')->select();
		return $data;

	}
	//处理图片信息
	public function doPic($params)
	{
		$data = self::create($params);
		if ($data)
		{
			return $data->pic_id;
		}
	}
	//图片具体信息
	public function picInfo($id)
	{
		$data = self::where('pic_id',$id)->find();
		return $data;
	}
	//个人页的发表图片信息展示
	static function selfPic($id)
	{
		$data = self::where('user_id',$id)->select();
		return $data;
	}
	static function collectPic($id)
	{
		$data = self::where('pic_id',$id)->find();
		return $data;
	}
}
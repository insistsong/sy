<?php
namespace app\index\model;

use think\Model;
use traits\model\SoftDelete;
use think\Paginator;

class Article extends Model
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
		$tag = [1 =>'中餐',2 =>'西餐', 3=>'饮品', 4=>'小吃', 5 =>'爆炒',6 =>'面食', 7=>'汤类', 8=>'油炸',9=>'糕点',10=>'水果',11=>'烧烤',12=>'烤肉',13=>'火锅',14=>'野菜',15=>'自助餐',16=>'面包',17=>'清真',18=>'快餐',19=>'料理',20=>'东南亚菜'];
		return $tag[$value];
	}
	//文章页显示
	public function index()
	{
		$data = self::paginate(4);
		return $data;
	}
	//首页的文章页显示
	static function indexArt()
	{
		$data = self::limit(4)->order('article_id','asc')->select();
		return $data;

	}
	public function doArticle($params)
	{
		$data = self::create($params);
		if ($data)
		{
			return $data->article_id;
		}
	}
	public function artInfo($id)
	{
		$data = self::where('article_id',$id)->find();
		return $data;
	}
	// public function index()
	// {
	// 	$data = self::select();
	// 	return $data;
	// }
	static function collectArt($id)
	{
		$data = self::where('article_id',$id)->find();
		return $data;
	}
	
}
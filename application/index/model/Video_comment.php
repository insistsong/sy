<?php
namespace app\index\model;

use think\Model;
use traits\model\SoftDelete;
use think\Paginator;

class Video_comment extends Model
{
	use SoftDelete;
	protected $deletetime = 'delete_time';
	protected $autoWriteTimestamp = true;
	public function getCreateTimeAttr($value)
	{
		return date('Y-m-d H:i:s',$value);
	}
	public function saveComment($params)
	{
		$data = self::create($params);
		return $data;
	}
	static function selectComment($id)
	{
		$data = self::where('video_id',$id)->select();
		return $data;
	}
}
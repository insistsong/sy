<?php
namespace app\admin\controller;

use think\Controller;

use app\admin\model\Goods as GoodsModel;

use think\Session;


class Goods extends Controller
{	
	//商品上架
	public function putaway()
	{
		return $this->fetch();
	}

	public function add()
	{	
		
		// $request = new Request;
		$file = request()->file('file');

		if (empty($file)) {
			$this->error('请选择上传文件');
		}
		// 移动到框架应用根目录/public/uploads/ 目录下
		$info = $file->move(ROOT_PATH . 'public' . DS . 'uploads/goods');

        if ($info) {
        	$info = 'sy.mengtiancai.com/uploads/goods/' . $info->getSaveName();
        	return $info;
            // $this->success('文件上传成功：' . $info->getRealPath());
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
	}

	public function upload()
	{

			$info = $this->add();
			$putaway = new GoodsModel;
	    	$putaway->name 				= $_POST['name'];
	    	$putaway->description 		= $_POST['description'];
	    	$putaway->specifications	= $_POST['specifications'];
	    	$putaway->money 			= $_POST['money'];
	    	$putaway->tag 				= $_POST['tag'];
	    	$putaway->num 				= $_POST['num'];
	    	$putaway->delivery_money 	= $_POST['delivery_money'];
	    	$putaway->sale 				= $_POST['sale'];
	    	$putaway->pic 				= $info;
	        if ($putaway->save()) {
	        	return $this->success('用户[ ' . $putaway->name . ':' . $putaway->goods_id . ' ]新增成功');

	        } else {
	            return $user->getError();
	        }
	}


	


	//商品管理 (库存, 状态, 下架...)

	public function goodsManage($id)
	{
		dump($id);
		return $this->fetch();
	}
}
<?php
namespace app\model;
use think\Model;
use think\Log;

class News extends Model
{
	/*
	 * 获取多图文消息数组
	 */
	public function getNews($newsid){
		
		return $this->where('newsid',$newsid)->order('order','asc')->select();
		
	}
}
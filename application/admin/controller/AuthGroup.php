<?php
namespace app\admin\controller;
use think\Controller;
use think\Log;

class AuthGroup extends controller
{
	/*
	 * 获取用户列表
	 */
    public function userlist()
    {
    	
		return $this->fetch();
    }
    
    /*
	 * 获取角色列表
	 */
    public function rolelist()
    {
		return $this->fetch();
    }

	/*
	 * 获取规则列表
	 */
    public function rulelist()
    {
		return $this->fetch();
    }
	/**
	 * 递归方法创建无极限分类DEMO
	 */
	public function	getList($pid = 0,&$result = array(),$spac = 0){
		//$navs = new Navs();
		$spac = $spac + 2;//定义“-”的重复次数
		$res = Navs::where(['pid' => $pid])->select();//根据父ID查询数据
		//$res = json_decode(json_encode($res),true);
		foreach($res as $val){//循环取得查询到的数据
			$val['catename'] = '|'.str_repeat('-',$spac).$val['catename'];  //拼接结果
			$result[] = $val;  //组装数组
			$this->getlist($val['id'],$result,$spac);  //调用自身函数用ID查询子数据
		}
		$this->assign('navlist',$result);
		return $this->fetch();
	}
	
	public function	getNav($id = 12,&$result = array()){
		$res = Navs::where(['id' => $id])->find();//根据ID查询数据
		if($res){
			//var_dump($res);die;
			$res = $res->toArray();
			$result[] = $res;
			$this->getNav($res['pid'],$result);
		}
		asort($result);
		$this->assign('nav',$result);
		return $this->fetch();
	}
	
	public function getarr(){
		//return "aa";
		$arr = [1,2,3,4,5,6,7,8];
		$ress = [];
		foreach($arr as $id){
			$ress[] = $this->getNavs($id);
		}
		print_r($ress);die;
	}
	
	public function	getNavs($id = 12,&$result = array()){
		$res = Navs::where(['id' => $id])->find();//根据ID查询数据
		if($res){
			//var_dump($res);die;
			$res = $res->toArray();
			$result[] = $res;
			$this->getNav($res['pid'],$result);
		}
		return $result;
	}
}
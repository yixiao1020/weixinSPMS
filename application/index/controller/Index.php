<?php
namespace app\index\controller;
use think\Controller;
use think\Log;
use think\Session;
use app\weixinapi\model\weixinSDK;
use app\model\Navs;

class Index extends controller
{
    public function index()
    {
		return $this->fetch();
    }
    
    public function share(){
    	$weixinSDK 			= new weixinSDK();
    	$jsapi_ticket 		= $weixinSDK->getJsapiTicket()['ticket'];
    	$appId				= weixinSDK::$AppID;
    	$timestamp			= time();
    	$nonceStr			= $weixinSDK->getRandCode(16);
    	$url				= "http://xb.214love.cn/index/index/share";
    	$signature			= "jsapi_ticket=$jsapi_ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
    	$signature			= sha1($signature);
    	$getQRcode			= $this->getQRcode();
    	$this->assign(['appId'=>$appId,'timestamp'=>$timestamp,'nonceStr'=>$nonceStr,'signature'=>$signature,'getQRcode'=>$getQRcode]);
    	//return $getQRcode;
    	return $this->fetch();
    	
    }

	public function getQRcode(){
		$weixinSDK 		= new weixinSDK();
		$accessToken	= $weixinSDK->getToken()['accessToken'];
		$url			= "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$accessToken";
		$postArr		= [
							"expire_seconds" 	=> 604800,
							"action_name"		=> "QR_STR_SCENE",
							"action_info"		=> ["scene" => ["scene_str" => "xuxian"]],
						  ];
		$postJosn 		= json_encode($postArr);
		$res			= $weixinSDK->curl($url,'post',$postJosn);
		$QRinfo			= json_decode($res,true);
		$url 			= $QRinfo['url'];
		//var_dump($QRinfo);die;
		//$ticket 		= urlencode($QRinfo['ticket']);
		//$QRurl			= "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=$ticket";
		//$qrcode			= $weixinSDK->curl($url);
		//var_dump( $qrcode);
		return $url;
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
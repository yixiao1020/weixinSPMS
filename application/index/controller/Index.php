<?php
namespace app\index\controller;
use think\Controller;
use think\Log;
use think\Session;
use app\weixinapi\model\weixinSDK;

class Index extends controller
{
    public function index()
    {
		return $this->fetch();
    }
    
    public function share(){
    	$weixinSDK 			= new weixinSDK();
    	$jsapi_ticket 		= $weixinSDK->getJsapiTicket()['ticket'];
    	$appId				= $weixinSDK->weixinparameter()['appid'];
    	$timestamp			= time();
    	$nonceStr			= $weixinSDK->getRandCode(16);
    	$url				= "http://xb.214love.cn/index/index/share";
    	$signature			= "jsapi_ticket=$jsapi_ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
    	$signature			= sha1($signature);
    	$this->assign(['appId'=>$appId,'timestamp'=>$timestamp,'nonceStr'=>$nonceStr,'signature'=>$signature]);
    	//return $signature;
    	return $this->fetch();
    	
    }

}
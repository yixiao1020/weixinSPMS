<?php
namespace app\index\controller;
use think\Controller;
use think\Log;

class Index extends controller
{
    public function index()
    {
		return "index";
    }
    
    public function ss()
    {
		return "ssxs";
    }
 //用户首次配置开发环境  
    public function api(){
	    //1）将token、timestamp、nonce三个参数进行字典序排序
	    $signature		= $_GET["signature"];		//微信加密签名，signature结合了开发者填写的token参数和请求中的timestamp参数、nonce参数。
	    $timestamp		= $_GET["timestamp"];		//时间戳
	    $nonce			= $_GET["nonce"];			//随机数
	    $echostr		= empty($_GET["echostr"]) ? '':$_GET["echostr"];			//随机字符串
	    $token			= "weixin";					//用作生成签名（该Token会和接口URL中包含的Token进行比对，从而验证安全性）
	    $tmpArr			= [$token,$timestamp, $nonce];
	    sort($tmpArr,SORT_STRING);
		//2）将三个参数字符串拼接成一个字符串进行sha1加密
		$smpStr			= implode($tmpArr);
		$tmpStr			=sha1($smpStr);
		//3）开发者获得加密后的字符串可与signature对比，标识该请求来源于微信
		if($tmpStr == $signature  && $echostr){
			echo $echostr;
		}else{
			$this->reposeMsg();	
    	}
   }
    //关注回复消息  
    public function reposeMsg()  
    {  
    	
	    //1.接受数据  
	    $postStr = file_get_contents("php://input"); //接受xml数据  
	   
	    //2.处理消息类型,推送消息  
	    $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);   //将xml数据转化为对象

		Log::write($postObj);
	    if( strtolower($postObj->MsgType) == 'event')  
	    {
	    	     
	   		//关注公众号事件  
	        if( strtolower($postObj->Event) == 'subscribe' )  
	        {  
	        	$toUser    =  $postObj->FromUserName;  
	            $fromUser  =  $postObj->ToUserName;  
	            $time      =  time();  
	            $msgType   =  'text';  
	            $content   =  '你终于来啦,等你等的好辛苦啊!';  
	            $template  =  "<xml>  
			                <ToUserName><![CDATA[%s]]></ToUserName>  
			                <FromUserName><![CDATA[%s]]></FromUserName>  
			                <CreateTime>%s</CreateTime>  
			                <MsgType><![CDATA[%s]]></MsgType>  
			                <Content><![CDATA[%s]]></Content>  
			                </xml>";  
	           echo sprintf($template, $toUser, $fromUser, $time, $msgType, $content);  
	        }else{
	        	echo "不是订阅事件";
	        }
	    } 
    
	}
}
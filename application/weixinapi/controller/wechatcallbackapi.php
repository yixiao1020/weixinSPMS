<?php
namespace app\weixinapi\controller;
use app\service\weixinSDK;
use app\model\News;
use think\Log;

class wechatcallbackapi extends controller
{
    public function valid(){
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            header('content-type:text');
            echo $echoStr;
            exit;
        }
    }
    //验证微接入验证
    private function checkSignature(){
    	$token = "TOKEN";
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
		$tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    public function reposeMsg(){
    	//接收微信发送过来的数据包
        $postStr = file_get_contents("php://input"); 
		
        if (!empty($postStr)){
        	$weixinSDK = new weixinSDK();
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            Log::write($postObj); //写日志
            switch(strtolower($postObj->MsgType)){
            	case 'event':
            		//如果消息类型为event则处理以下业务
	            	if(strtolower($postObj->Event) == 'subscribe'){
	            		//事件类型，为subscribe(订阅)则处理以下业务
	        			$content		= "你好，欢迎关注我们1111！";
	            		$weixinSDK->transmitText($postObj,$content);
	            	}	
	            break;	
	            
	            case 'text':
	            	switch(strtolower($postObj->Content) ){
	            		case '1':
	            		//事件类型，为subscribe(订阅)则处理以下业务
	       				$content		= "你好，我就是1222";
	       				$weixinSDK->transmitText($postObj,$content);
	       				break;	
	       				
	       				case '2':
	       				$arr = [
	       					[
	       						"Title" 		=>"百度",	//标题 
	       						"Description"	=>"有问题找百度"	,	//描述
	       						"PicUrl"		=>"https://www.baidu.com/img/bd_logo1.png",	//图片地址
	       						"Url"			=>"http://www.baidu.com",			//访问网址
	       					],
	       					[
	       						"Title" 		=>"百度",	//标题 
	       						"Description"	=>"有问题找百度"	,	//描述
	       						"PicUrl"		=>"https://www.baidu.com/img/bd_logo1.png",	//图片地址
	       						"Url"			=>"http://www.baidu.com",			//访问网址
	       					],
	       					[
	       						"Title" 		=>"百度",	//标题 
	       						"Description"	=>"有问题找百度"	,	//描述
	       						"PicUrl"		=>"https://www.baidu.com/img/bd_logo1.png",	//图片地址
	       						"Url"			=>"http://www.baidu.com",			//访问网址
	       					],
	       				];
	       				$weixinSDK->transmitNews($postObj,$arr);
	       				break;
					}
				break;	
            }
        }else{
            echo "";
            exit;
        }
    }

}
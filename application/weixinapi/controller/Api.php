<?php
namespace app\weixinapi\controller;
use think\Controller;
use think\Log;
use app\weixinapi\model\weixinSDK;
use app\weixinapi\model\News;
class Api extends controller
{
	
   	public function test(){
		$news = new News();
		$res = $news::get(['trigger_key' => 2]);
		$nwesid = $res->newsid;
		$lists = $news->getNews($nwesid);
		//var_dump($nwesid);die;
		//var_dump($list);die;
		$arr = [];
		$res1 = [];
		foreach ($lists as $user){
			$res1['trigger_key' ]	=$user->trigger_key;
			$res1['Title' ]			=$user->title;
			$res1['Description' ]	=$user->description;
			$res1['PicUrl' ]		=$user->pic_url;
			$res1['Url' ]			=$user->url;
			$arr[] =$res1;
		}
		var_dump($arr);	
	}
	
	public function test1(){
   		$url = "http://www.hao123.com";
   		$weixinSDK = new weixinSDK();
   		$res = $weixinSDK->curl($url);
   		var_dump($res);
   	}
     /**
     * 响应微信发送的Token验证
     * @param  [type] $signature [description]
     * @param  [type] $timestamp [description]
     * @param  [type] $nonce     [description]
     * @param  [type] $token     [description]
     * @return [type]            [description]
     */
    public function api()
    {
    	
		if (isset($_GET['echostr'])) {
		    $this->valid();
		}else{
		    $this->reposeMsg();
		}
    }
    
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
    	$token = "weixin";
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
		$news = new News();
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
	            	
	            	/*switch(strtolower($postObj->Content) ){
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
					}*/
					$val = strtolower($postObj->Content);
					$res = $news::get(['trigger_key' => $val]);
					$type = $res->type;
					switch($type){
						case 1:
						if($res){
							$content =$res->content;
							$weixinSDK->transmitText($postObj,$content);
						}else{
							return false;
						}
						break;
						
						case 6:
						if($res){
							$nwesid = $res->newsid;
							$lists = $news->getNews($nwesid);
							$arr = [];
							$res1 = [];
							foreach ($lists as $vo){
								$res1['Title' ]			=$vo->title;
								$res1['Description' ]	=$vo->description;
								$res1['PicUrl' ]		=$vo->pic_url;
								$res1['Url' ]			=$vo->url;
								$arr[] =$res1;
							}
							$weixinSDK->transmitNews($postObj,$arr);
						}else{
							return false;
						}
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
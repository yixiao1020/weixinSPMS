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
		
        if (!empty($postStr)){
        	//$weixinSDK = new weixinSDK();
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            Log::write($postObj); //写日志
            switch(strtolower($postObj->MsgType)){
            	case 'event':
            		//如果消息类型为event则处理以下业务
	            	switch(strtolower($postObj->Event)){
	            		case 'subscribe'://事件类型，为subscribe(订阅)则处理以下业务
	        				$val = 'subscribe';
							$this->msgType($val,$postObj);
	            		break;
	            		
	            		case 'click'://事件类型，为点击事件时
	        				$val = strtolower($postObj->EventKey);
							$this->msgType($val,$postObj);
	            		break;
	            	}	
	            break;	
	            
	            case 'text':
					$val = strtolower($postObj->Content);
					$this->msgType($val,$postObj);
				break;	
            }
        }else{
            echo "";
            exit;
        }
    }
    
    public function msgType($val,$postObj){
    	$news = new News();
    	$weixinSDK = new weixinSDK();
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
    }
    
/*获取微信服务器IP*/
	public function getWxServerIp(){
		$weixinSDK 		=new weixinSDK();
		$accessToken	= $weixinSDK->getToken()['accessToken'];
		$url			= "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=$accessToken";
		$res			= $weixinSDK->curl($url);
		
		var_dump($res);
	}
	
	/*创建微信自定义菜单*/
	public function createMenu(){
		$weixinSDK 		=new weixinSDK();
		$accessToken	= $weixinSDK->getToken()['accessToken'];
		$arrMenu		= [
						    "button"=>[
							    [	
							       	"type"=>"click",
							        "name"=>urlencode("今日歌曲"),
							        "key"=>"1"
							    ],
						      	[
						           "name"=>urlencode("菜单"),
						           "sub_button"=>[
							           [	
							               "type"=>"view",
							               "name"=>urlencode("搜索"),
							               "url"=>"http://www.soso.com/"
							            ],
							            [
							               "type"=>"click",
							               "name"=>urlencode("赞一下"),
							               "key"=>"2"
							            ]
						        	]
						       	],
						       	[	
							       	"type"=>"view",
							        "name"=>urlencode("百度"),
							        "url"=>"http://www.baidu.com/"
							    ]
						    ]
						];
		$jsonmenu 		=urldecode(json_encode($arrMenu));
		$url			= "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$accessToken";
		$res			= $weixinSDK->curl($url,'post',$jsonmenu);
		
		return $jsonmenu .'<br/>'.$res."<br/>accesstoken:".$accessToken;
	}
}
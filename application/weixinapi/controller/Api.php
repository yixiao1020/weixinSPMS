<?php
namespace app\weixinapi\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Log;
use app\service\weixinSDK;
use app\model\News;
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

     //验证微接入验证
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
    
    /**
     * 响应微信发送的Token验证
     * @param  [type] $signature [description]
     * @param  [type] $timestamp [description]
     * @param  [type] $nonce     [description]
     * @param  [type] $token     [description]
     * @return [type]            [description]
     */
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
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            Log::write($postObj,"微信服务回传信息"); //写日志
            switch(strtolower($postObj->MsgType)){
            	//事件类型回复消息处理方法
            	case 'event':
	            	switch(strtolower($postObj->Event)){
	            		//关注类型事件消息处理
	            		case 'subscribe':
	        				$val = 'subscribe';
							$this->msgType($val,$postObj);
	            		break;
	            		//点击类型事件消息处理
	            		case 'click'://事件类型，为点击事件时
	        				$val = strtolower($postObj->EventKey);
							$this->msgType($val,$postObj);
	            		break;
	            		
	            		//场景类型事件消息处理
	            		case 'scan'://事件类型，为点击事件时
	        				$val = strtolower($postObj->EventKey);
	        				log::write($val,"场景参数");die;
							$this->msgType($val,$postObj);
	            		break;
	            	}	
	            break;	
	            //文字类型触发消息处理方法
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
    
    /**
     * 响应微信发送的Token验证
     * @param  [string] $val  			[触发消息关键字]
     * @param  [Obj] 	$postObj 		[接收微信服务器发送的XML转换的数据]
     * @return [string]            		[封装回复消息类型选择与回复消息内容函数]
     */
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
	
	public function getBaseInfo(){
		//1 第一步：用户同意授权，获取code
		$weixinSDK 		=new weixinSDK();
		$authorization	= 2;															//定义授权方式1为静默授权，2为全面授权
		$appid 			= weixinSDK::$AppID;
		$redirect_uri	= urlencode("http://xb.214love.cn/weixinapi/api/getUserInfo");
		if($authorization == 1){
			$scope = "snsapi_base";
		}
		if($authorization == 2){
			$scope = "snsapi_userinfo";
		}
		$url			= "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect_uri&response_type=code&scope=$scope&state=123#wechat_redirect";
		header("location:$url");
	}
	/*获取网页静默授权*/
	public function getUserInfo(){
		$weixinSDK 		=new weixinSDK();
		$authorization	= 2;															//定义授权方式1为静默授权，2为全面授权
		//Session::clear();
		if($authorization ==1 && Session::get('openid')){
			return Session::get('openid')."缓存信息";
		}
		if($authorization ==2 && Session::get('userOpenid')){
			return Session::get('userInfo')."缓存信息";
		}
		
		$appid 			= weixinSDK::$AppID;
		$appsecret 		= weixinSDK::$AppSecret;
		$code 			= $_GET["code"];
		$url 			= "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
		$res 			= $weixinSDK ->curl($url);
		$arr			= json_decode($res,true);	//把接收到微信传过来的JSON数据转为数组
		
		if(isset($arr['errcode'])){
			log::wirte("操作失败,错误码：".$arr['errcode'].",错误信息：".$arr['errmsg'],"error");
			return "操作失败,错误码：".$arr['errcode'].",错误信息：".$arr['errmsg'];
		}
		
		$openid			= $arr['openid'];
		$access_token	= $arr['access_token'];
		if($authorization ==1){
			Session::set('openid',$openid);
			return $openid;
		}
		if($authorization ==2){
			$userUrl		= "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
			$userInfo		= $weixinSDK ->curl($userUrl);
			$userInfoArr	= json_decode($userInfo,true);
			Session::set('userOpenid',$userInfoArr['openid']);
			Session::set('userInfo',$userInfo);
			//var_dump(Session::get('userInfoArr')['openid']);
			return $userInfo;
		}
	}
	
	public function getss(){
		$weixin = new weixinSDK();
   		$as = $weixin->getToken()['accessToken'];
   		return $as;
   	}

}
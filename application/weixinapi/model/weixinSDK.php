<?php
namespace app\weixinapi\model;
use think\Session;
use think\Log;

class weixinSDK
{
	//微信参数设置
	public function weixinparameter(){
		$AppID		= "wx6da29af1c50cd281";
		$AppSecret	= "0cd545e175743db2d9d18aeab8b3b313";
		return ['appid'=>$AppID,'appsecret'=>$AppSecret];
	}

  	/**
     * 文本消息转xml
     * @param  [type]  $object   [description]
     * @param  [type]  $content  [description]
     * @return [type]            [description]
     */
    public function transmitText($object,$content){
	    $textTpl	= "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[text]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					</xml>";
		$resultStr  =sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(),  $content);         		
	 	echo $resultStr;
    }

    /**
     * 图片消息转xml
     * @param  [type]  $object   [description]
     * @param  [type]  $content  [description]
     * @param  integer $funcFlag [description]
     * @return [type]            [description]
     */
    public function transmitPic($object, $content, $funcFlag = 0){
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[image]]></MsgType>
                    <Image>
                        <MediaId><![CDATA[%s]]></MediaId>
                    </Image>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $funcFlag);
        echo $resultStr;
    }

    /**
     * 图文消息数组转xml/可转多图文
     * @param  [type]  $object   [description]
     * @param  [type]  $arr_item [description]
     * @param  integer $funcFlag [description]
     * @return [type]            [description]
     */
    public function transmitNews($object, $arr_item){
        if(!is_array($arr_item)){
        	echo "数据格式不正确";
        	exit;
        }else{
	        $itemTpl = "<item>
	                    <Title><![CDATA[%s]]></Title>
	                    <Description><![CDATA[%s]]></Description>
	                    <PicUrl><![CDATA[%s]]></PicUrl>
	                    <Url><![CDATA[%s]]></Url>
	                    </item>";
	        $item_str = "";
	        
	        foreach ($arr_item as $item)
	            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
	
	        $newsTpl = "<xml>
	                    <ToUserName><![CDATA[%s]]></ToUserName>
	                    <FromUserName><![CDATA[%s]]></FromUserName>
	                    <CreateTime>%s</CreateTime>
	                    <MsgType><![CDATA[news]]></MsgType>
	                    <Content><![CDATA[]]></Content>
	                    <ArticleCount>%s</ArticleCount>
	                    <Articles>".$item_str."</Articles>
	                    </xml>";
	        $resultStr = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($arr_item));
	        echo $resultStr;
        }

    }
    
     /**
     * curl工具封装
     * @param  [type]  $object   [description]
     * @param  [type]  $url		 [description]
     * @return [type]            [description]
     */
    public function curl($url, $type = "get", $arr = ""){
    	//初始化curl
    	$ch	= curl_init();
    	//设置curl的参数
    	curl_setopt($ch,CURLOPT_URL,$url);
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    	if($type == "post"){
    		curl_setopt($ch,CURLOPT_POST,1);
    		curl_setopt($ch,CURLOPT_POSTFIELDS, $arr);
    	}
    	//采集
    	$res = curl_exec($ch);
    	if(curl_errno($ch)){
    		return curl_error($ch);
    	}
    	curl_close($ch);
    	return $res;
    }
    
    public function getToken(){
    	if(Session::get('accessToken')&&time() < Session::get('expires_time')){
    		return ['accessToken'=>Session::get('accessToken'),'expires_time'=>Session::get('expires_time')] ;
    	}else{
			$appid 		= $this->weixinparameter()['appid'];
			$appsecret	= $this->weixinparameter()['appsecret'];
	   		$url 		= "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
	   		$res 		= $this->curl($url);
	   		$arr		=json_decode($res,true);
	   		if(isset($arr['access_token'])){
	   			Session::set('accessToken',$arr['access_token']);
	   			Session::set('expires_time',time()+7200);
	   			return ['accessToken'=>Session::get('accessToken'),'expires_time'=>Session::get('expires_time')] ;
	   		}else{
	   			return "access_token获取失败败，错误码：".$arr['errcode'];
	   		}
    	}
   	}
}
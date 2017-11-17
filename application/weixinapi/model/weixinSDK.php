<?php
namespace app\weixinapi\model;
use think\Log;

class weixinSDK
{

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
    public function curl($url){
    	//初始化curl
    	$ch	= curl_init();
    	//设置curl的参数
    	curl_setopt($ch,CURLOPT_URL,$url);
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    	//采集
    	$res = curl_exec($ch);
    	if(curl_errno($ch)){
    		return curl_error($ch);
    	}
    	curl_close($ch);
    	return $res;
    }
}
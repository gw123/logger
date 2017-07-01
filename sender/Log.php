<?php
namespace common\utils;

class  Log{
    //调试服务器地址
    public  static  $serverURL='';
    //  令牌 有两个作用 1. 区分会话 ,  2 . 安全验证  默认为token 为了安全请在实际使用中替换
    public  static  $token = 'token';
    // 是否禁止调试
    public static   $forbidden = false;

    public  static  $error = null;

    /***
     * @param string $serverURL  调试服务器接受调试信息地址 ,
     * 如 http://www.xytschool.com:8080
     */
    public  static function setServer($serverURL='')
    {
        Log::$serverURL = $serverURL;
    }

    public  static function setToken( $token = 'token')
    {
        Log::$token = $token;
    }
    public static function info($content , $group='all')
    {
        return self::send('info',$content , $group);
    }

    public static function waring($content , $group='all')
    {
        return self::send('waring',$content , $group);
    }

    public static function error($content , $group='all')
    {
        return self::send('error',$content , $group);
    }

    public static function send($type ,$content , $group='all')
    {
        $token = Log::$token;
        if(is_string($content))
            $contentType ='text';
        else
        {
            $contentType = 'json';
            $content = json_encode($content);
        }
        $frame = ['token'=>$token,'type'=>$type,  "group"=>$group , 'data'=>$content, 'contentType' =>$contentType] ;

       return self::_send(Log::$serverURL,"POST",$frame);
    }

    public static function _send($url, $method = 'GET', $params = array(),$returnCookie=false)
    {
        $data = '';
        if (!empty($url)) {
            try {
                $ch = curl_init();
                if(strtoupper($method) == 'GET') {
                    $url = $url . '?' . http_build_query($params, '', '&');
                }

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, false);

                //判断是否获取头部信息
                if($returnCookie) {
                    curl_setopt($ch, CURLOPT_HEADER, 1);
                }
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 1);//超时时间
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                //curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
                if (strtoupper($method) == 'POST') {
                    $curlPost = $params;
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));
                }
                $data = curl_exec($ch);
                if(!$data)
                {
                    $info = curl_getinfo($ch);
                    Log::$error = $info;
                    return false;
                }
                if($returnCookie && $data) {
                    $cookie = false;
                    list($header,$body) = explode("\r\n\r\n", $data);
                    preg_match("/Set\-Cookie:([^\r\n]*)/i", $header, $matches);
                    if(isset($matches[1])) {
                        $cookie = array_slice($matches,1);
                    }
                    return [$cookie,$body];
                }

                curl_close($ch);
            } catch (Exception $e) {
                $data = null;
            }
        }

        return $data;
    }
}

//定义数据格式  类型  消息组
//Log::setServer('http://xytschool.com:8080');
//log::setToken('gw123');
//
//if(!Log::waring('hello word') ) { var_dump( Log::$error ); };
//log::error('程序出错');
//echo Log::info('通知:...' , 'group1');


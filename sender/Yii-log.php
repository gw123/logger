<?php
namespace common\utils;
use yii\base\Component;

/****
 * Yii 组件方式调用web调试器
 * Class Log
 * @package common\utils
 */
class  Log extends Component {
    //调试服务器地址
    public    $serverURL='xytschool.com:8080';
    //  令牌 有两个作用 1. 区分会话 ,  2 . 安全验证  默认为token 为了安全请在实际使用中替换
    public    $token = '17ky';
    // 是否禁止调试
    public     $forbidden = false;
    public    $error = null;

    public function init()
    {
        parent::init();
        // ... 配置生效后的初始化过程
    }

    /***
     * @param string $serverURL  调试服务器接受调试信息地址 ,
     * 如 http://www.xytschool.com:8080
     */
    public   function setServer($serverURL='')
    {
        $this->serverURL = $serverURL;
    }

    public   function setToken( $token = 'token')
    {
        $this->token = $token;
    }
    public  function info($content , $group='all')
    {
        return self::send('info',$content , $group);
    }

    public  function waring($content , $group='all')
    {
        return self::send('waring',$content , $group);
    }

    public  function error($content , $group='all')
    {
        return self::send('error',$content , $group);
    }

    public  function send($type ,$content , $group='all')
    {
        $token = $this->token;
        if(is_string($content))
            $contentType ='text';
        else
        {
            $contentType = 'json';
            $content = json_encode($content);
        }
        $frame = ['token'=>$token,'type'=>$type,  "group"=>$group , 'data'=>$content, 'contentType' =>$contentType] ;

       return self::_send($this->serverURL,"POST",$frame);
    }

    public  function _send($url, $method = 'GET', $params = array(),$returnCookie=false)
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
                    $this->error = $info;
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

/*****************************************
 # (一) 在 Yii 的配置文件上加上 logger的配置  组件的ID可以随意修改

'components' => [
'logger'=>[
'class' => 'common\utils\Log',       // 组件类的命名空间
'serverURL'=>'xytschool.com:8080',   // 配置调试服务器地址
'token'=>'17ky',                     // 验证令牌
'forbidden'=>false
],

# (二) 测试发送功能
class CeshiController extends BackendController
{
    public function actionLog()
    {
        $log =  Yii::$app->logger;
        if(!$log->waring('hello word') ) {
            // 连接调试服务器出错 , 请检查错误
            var_dump($log->error);
            exit();
        };
        $log->error('程序出错');
        $log->info('步骤一通过:...' , 'group1');
        $log->info('步骤二通过:...' , 'group1');
    }
}
在浏览器上 访问 http://host/ceshi/log   (host 是的的主机) 查看效果
 */

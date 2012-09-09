<?php

require_once 'OpenSDK/OAuth/Client.php';
/**
 * QQSNS 专用OAuth Client
 *
 * 囧 opensns.qq.com 的OAuth没有完全遵循OAuth1.0协议
 * 囧 官方的SDK GET方法不接受参数，难道官方所有的GET接口都不打算接受参数了？
 *
 * @ignore
 * @author icehu@vip.qq.com
 *
 */

class OpenSDK_OAuth_QQSNSClient extends OpenSDK_OAuth_Client
{
    /**
     * 组装参数签名并请求接口
     *
     * @param string $url
     * @param array $params
     * @param string $method
     * @param false|array $multi false:普通post array: array ( '{fieldname}' =>array('type'=>'mine','name'=>'filename','data'=>'filedata') ) 文件上传
     * @return string
     */
    public function request( $url, $method, $params, $multi = false )
    {
        $oauth_signature = $this->sign($url, $method, $params, $multi);
        $params[$this->oauth_signature_key] = $oauth_signature;
        return $this->http($url, $params, $method, $multi);
    }

    /**
     * OAuth 协议的签名
     *
     * @param string $url
     * @param string $method
     * @param array $params
     * @return string
     */
    protected function sign( $url , $method, $params, $multi )
    {
        if($multi && is_array($multi))
        {
            //上传图片专用签名
            //囧 图片内容需要做签名，并且图片上传时 。
            //囧 整个sign_parts不做urlencode 。
            foreach($multi as $field => $path)
            {
                $params[$field] = file_get_contents($path);
            }
            uksort($params, 'strcmp');
            $pairs = array();
            foreach($params as $key => $value)
            {
//                $key = self::urlencode_rfc1738($key);
//                $pairs[] = $key . '=' . self::urlencode_rfc1738($value);
                //囧 qq opensns 竟然不编码
                $pairs[] = $key . '=' . $value;
            }
            $sign_parts = implode('&', $pairs);
            $base_string = implode('&', array( strtoupper($method) , $url , $sign_parts ));
        }
        else
        {
            uksort($params, 'strcmp');
            $pairs = array();
            foreach($params as $key => $value)
            {
//                $key = self::urlencode_rfc1738($key);
//                $pairs[] = $key . '=' . self::urlencode_rfc1738($value);
                //囧 qq opensns 竟然不编码
                $pairs[] = $key . '=' . $value;
            }
            $sign_parts = self::urlencode_rfc1738(implode('&', $pairs));
            $base_string = implode('&', array( strtoupper($method) , self::urlencode_rfc1738($url) , $sign_parts ));
        }

        //囧 官方不对appkey_secret 和 token_secret编码
        //是否编码都无所谓，因为appkey_secret 和 token_secret 都没有需要编码的字符。
        //但是不编码不合规范。为了符合规范还是编码一下。
        $key_parts = array(self::urlencode_rfc1738($this->_app_secret), self::urlencode_rfc1738($this->_token_secret));

        $key = implode('&', $key_parts);
        $sign = base64_encode(OpenSDK_Util::hash_hmac('sha1', $base_string, $key, true));
        if($this->_debug)
        {
            echo 'base_string: ' , $base_string , "\n";
            echo 'sign key: ', $key , "\n";
            echo 'sign: ' , $sign , "\n";
        }
        return $sign;
    }

    /**
     * rfc1738 编码
     * @param string $str
     * @return string
     */
    protected static function urlencode_rfc1738($str)
    {
        return rawurlencode($str);
    }

}

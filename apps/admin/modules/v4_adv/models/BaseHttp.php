<?php
namespace modules\v4_adv\models;


class BaseHttp
{
    //const HOST_URL = 'http://10.168.196.111:19080/www-backstage/';
    const HOST_URL = 'http://121.40.78.19:8080/service_push/';

    public static function http($url,$params=array(),$method='GET',$format='text',$multi = false, $extheaders = array())
	{
    	if(!function_exists('curl_init')) exit('Need to open the curl extension');
        $method = strtoupper($method);        
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ci, CURLOPT_HEADER, false);
        $headers = (array)$extheaders;
        switch ($method)
        {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($params))
                {
                    if($multi)
                    {
                        foreach($multi as $key => $file)
                        {
                            $params[$key] = '@' . $file;
                        }
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                        $headers[] = 'Expect: ';
                    }
                    else
                    {
                        $params_str = $format=='json' ? json_encode($params) : self::buildHttpQuery($params);
                        //$headers[] = 'Content-Type: application/json; charset=utf-8';
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params_str);
                    }
                }
                break;
            case 'DELETE':
            case 'GET':
                $method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($params))
                {
                    $url = $url . (strpos($url, '?') ? '&' : '?')
                        . (is_array($params) ? self::buildHttpQuery($params) : $params);
                }
                break;
        }
        //exit($url);
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
        curl_setopt($ci, CURLOPT_URL, $url);
        if($headers)
        {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        }

        $response = curl_exec($ci);
        //var_dump($response);exit();
        $status_code = curl_getinfo($ci,CURLINFO_HTTP_CODE);
        curl_close ($ci);        
        if($status_code==200) return json_decode($response,true);
        //\Log::error($response);
        //var_dump($response);exit();
        return false;
	}	
	
	public static function buildHttpQuery($params)
	{
		$query_attr = array();
		foreach($params as $key=>$val){
			if(is_array($val)){
				foreach($val as $one){
					$query_attr[] = $key . '=' . urlencode($one);
				}
			}else{
				$query_attr[] = $key . '=' . urlencode($val);
			}
		}
		return implode('&',$query_attr);
	}
}
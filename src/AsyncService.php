<?php


namespace AsyncService;

/**
 * HTTP异步请求服务
 * Class AsyncService
 * @package service
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/22 15:32
 */
class AsyncService
{
	//get请求方式
	const METHOD_GET  = 'GET';
	//post请求方式
	const METHOD_POST = 'POST';

	/**
	 * 发起http异步请求
	 * @param string $url http地址
	 * @param string $method 请求方式
	 * @param array $params 参数
	 * @param string $ip 支持host配置
	 * @param int $connectTimeout 连接超时，单位为秒
	 * @throws Exception
	 */
	public static function exec($url, $method = self::METHOD_GET, $params = array(), $ip = null, $connectTimeout = 1)
	{
		//var_dump($url);
		$urlInfo = parse_url($url);

		$host = $urlInfo['host'];
		$port = isset($urlInfo['port']) ? $urlInfo['port'] : 80;
		$path = isset($urlInfo['path']) ? $urlInfo['path'] : '/';
		!$ip && $ip = $host;

		$method = strtoupper(trim($method)) !== self::METHOD_POST ? self::METHOD_GET : self::METHOD_POST;
		$params = http_build_query($params);

		if($method === self::METHOD_GET && strlen($params) > 0){
			$path .= '?' . $params;
		}

		$fp = fsockopen($ip, $port, $errorCode, $errorInfo, $connectTimeout);

		if($fp === false){
			throw new Exception('Connect failed , error code: ' . $errorCode . ', error info: ' . $errorInfo);
		}
		else{
			$http  = "$method $path HTTP/1.1\r\n";
			$http .= "Host: $host\r\n";
			$http .= "Content-type: application/x-www-form-urlencoded\r\n";
			$method === self::METHOD_POST && $http .= "Content-Length: " . strlen($params) . "\r\n";
			$http .= "\r\n";
			$method === self::METHOD_POST && $http .= $params . "\r\n\r\n";

			if(fwrite($fp, $http) === false || fclose($fp) === false){
				throw new Exception('Request failed.');
			}
		}
	}

}

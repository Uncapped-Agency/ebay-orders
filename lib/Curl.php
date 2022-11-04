<?php
class Curl{
	public static function get($route = '', $data = [], $headers = []){
		return self::send('get', self::buildUrl($route, $data), [], $headers);
	}

	public static function post($route = '', $data = [], $headers = []){
		return self::send('post', $route, $data, $headers);
	}

	public static function send($method='', $route = '', $data = [], $headers = []){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_URL, $route);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		$formated_headers = [];
		foreach ($headers as $key => $val) {
			if (is_string($key)) {
				$formated_headers[] = $key . ': ' . $val;
			} else {
				$formated_headers[] = $val;
			}
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $formated_headers);

		if($data){
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		
		$result = curl_exec($ch);

		if ($result === false) {
			$errno = curl_errno($ch);
			$errmsg = curl_error($ch);
			$msg = "cURL request failed with error [$errno]: $errmsg";
			curl_close($ch);
			throw new \Exception($request, $msg, $errno);
		}
		
		curl_close($this->ch);
		return $result;
	}

	public static function buildUrl($url, array $query)
	{
		if (empty($query)) {
			return $url;
		}

		$parts = parse_url($url);

		$queryString = '';
		if (isset($parts['query']) && $parts['query']) {
			$queryString .= $parts['query'].'&'.http_build_query($query);
		} else {
			$queryString .= http_build_query($query);
		}

		$retUrl = $parts['scheme'].'://'.$parts['host'];
		if (isset($parts['port'])) {
			$retUrl .= ':'.$parts['port'];
		}

		if (isset($parts['path'])) {
			$retUrl .= $parts['path'];
		}

		if ($queryString) {
			$retUrl .= '?' . $queryString;
		}

		return $retUrl;
	}
}
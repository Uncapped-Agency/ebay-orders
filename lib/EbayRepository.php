<?php
class EbayRepository{
	public $tokenKey = "ac_token";
	public $tokenTimeKey = "ac_token_created_at";
	public $stateKey = "ac_token_state";
	public $db;
	public $sandbox;


	public function __construct($db, $sandbox = true, )
	{
		$this->db = $db;
		$this->sandbox = $sandbox;
	}

	public function getOrders()
	{
		$data = [
			// 'orderIds' => 'refresh_token',
			// 'filter' => $refresh_token,
			// 'limit' => $refresh_token,
			// 'offset' => $refresh_token,
			// 'fieldGroups' => $refresh_token,
		];
		$response = Curl::get($this->getUrl('/sell/fulfillment/v1/order'), $data, $this->getHeaders());
		if(!$response || !isset($response["orders"])){
			throw new \Exception("Invalid CURL response at 'getOrders' method");
		}
		return $this->parseOrders($response["orders"]);
	}

	public function getToken()
	{
		//from session
		if(isset($_SESSION[$this->tokenKey]) && !empty($_SESSION[$this->tokenKey]) &&
			isset($_SESSION[$this->tokenTimeKey]) && !empty($_SESSION[$this->tokenTimeKey]) &&
			$_SESSION[$this->tokenTimeKey] > time()){
			return $_SESSION[$this->tokenKey];
		}
		//from db
		$token = $this->getTokenFromDB();
		if($token !== false){
			return $token;
		}
		//start auth code grant flow
		return $this->getAuthGrantCode();
	}

	public function getTokenFromDB()
	{
		$sql = "SELECT * FROM auth_grants WHERE 1 ORDER BY id DESC LIMIT 0,1";
		$row = $this->db->query($sql)->fetchArray();
		if($this->db->query_count>0){
			//check if token is expired
			if(isset($row["access_token"]) && !empty($row["access_token"]) && isset($row["expiry"]) && !empty($row["expiry"]) && $row["expiry"] > time()){
				$this->updateTokenInSession($row["access_token"], $row["expiry"]);
				return $row["access_token"];
			}
			//check if refreshToken is not expired
			if(isset($row["refresh_token"]) && !empty($row["refresh_token"]) && isset($row["refresh_token_expiry"]) && !empty($row["refresh_token_expiry"]) && $row["refresh_token_expiry"] > time()){
				return $this->refreshToken($row["id"], $row["refresh_token"]);
			}
		}
		return false;
	}

	public function grantFlowCallback()
	{
		if(!isset($_SESSION[$this->stateKey]) || empty($_SESSION[$this->stateKey]) || !isset($_GET['code']) || empty($_GET['code']) || !isset($_GET['state']) || empty($_GET['state']) || $_SESSION[$this->stateKey] !== $_GET['state']){
			throw new \Exception("Invalid state or code in response at 'grantFlowCallback' method");
		}
		
		//call for access token
		$data = [
			'grant_type' => 'authorization_code',
			'redirect_uri' => EBAY_RU_NAME,
			'code' => $_GET['code']
		];
		$response = Curl::post($this->getUrl('identity/v1/oauth2/token'), $data, $this->getOauthHeaders());
		if(!$response || !isset($response["access_token"])){
			throw new \Exception("Invalid CURL response at 'grantFlowCallback' method");
		}

		$data = [
			'access_token' => $response['access_token'],
			'expiry' => time() + $response['expires_in'] - 60, //expiry less 1 minute
			'refresh_token' => $response['refresh_token'],
			'refresh_token_expiry' => time() + $response['refresh_token_expires_in'] - 60, //expiry less 1 minute
			'token_type' => $response['token_type'],
		];
		//set session
		$this->updateTokenInSession($data["access_token"], $data["expiry"]);
		//set database
		$this->saveTokenInDB($data);
	}


	public function refreshToken($id, $refresh_token)
	{
		//call for token refresh
		$data = [
			'grant_type' => 'refresh_token',
			'refresh_token' => $refresh_token,
			'scope' => urlencode(implode(' ', $this->getScopes()))
		];
		$response = Curl::post($this->getUrl('identity/v1/oauth2/token'), $data, $this->getOauthHeaders());
		if(!$response || !isset($response["access_token"])){
			throw new \Exception("Invalid CURL response at 'refreshToken' method");
		}

		$data = [
			'access_token' => $response['access_token'],
			'expiry' => time() + $response['expires_in'] - 60, //expiry less 1 minute
			'token_type' => $response['token_type'],
		];
		//set session
		$this->updateTokenInSession($data["access_token"], $data["expiry"]);
		//set database
		$this->updateTokenInDB($id, $data);
		return $response['access_token'];
	}

	public function getAuthGrantCode()
	{
		//redirect
		$_SESSION[$this->stateKey] = $this->generateRandomString();
		$data = [
			'client_id' => EBAY_CLIENT_ID,
			'locale' => EBAY_LOCALE,
			'redirect_uri' => EBAY_RU_NAME,
			'response_type' => 'code',
			'scope' => urlencode(implode(' ', $this->getScopes())),
			'state' => $_SESSION[$this->stateKey]
		];
		$url = $this->getUrl('oauth2/authorize', $data);
		header("Location: $url");
		exit();
	}
	public function parseOrders($orders)
	{
		$parsed = $orders;
		return $parsed;
	}

	public function getHeaders()
	{
		return [
			'Accept' => 'application/json',
			'Authorization' => 'Bearer '.$this->getToken(),
		];
	}

	public function updateTokenInSession($token, $expiry)
	{
		$_SESSION[$this->tokenKey] = $token;
		$_SESSION[$this->tokenTimeKey] = time() - (60*2);
	}

	public function saveTokenInDB($data)
	{
		$sql = "INSERT INTO auth_grants (".implode(' ', array_keys($data)).") VALUES (".implode(' ', array_values($data)).")";
		$this->db->query($sql);
		return $this->db->affectedRows();
	}

	public function updateTokenInDB($id, $data)
	{
		$sql = sprintf("UPDATE TABLE auth_grants SET access_token = '', expiry = %d  WHERE id = %d", $data['access_token'], $data['expiry'], $id);
		$this->db->query($sql);
		return $this->db->affectedRows();
	}

	public function getUrl($suffix, $query=[])
	{
		$suffix = substr($suffix, 0, 1) == '/' ? $suffix : '/'.$suffix;
		$base = $this->sandbox ? "https://auth.sandbox.ebay.com" : "https://auth.ebay.com";
		return Curl::buildUrl($base.$suffix, $query);
	}
	public function getScopes()
	{
		return [
			'https://api.ebay.com/oauth/api_scope/sell.fulfillment',
			'https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly'
		];
	}
	public function getOauthHeaders()
	{
		return [
			'Content-Type' => 'application/x-www-form-urlencoded',
			'Authorization' => 'Basic '.base64_encode(EBAY_CLIENT_ID.':'.EBAY_CLIENT_SECRET)
		];
	}
	public function generateRandomString($length = 30)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $index = rand(0, strlen($characters) - 1);
	        $randomString .= $characters[$index];
	    }
	    return $randomString;
	}
}
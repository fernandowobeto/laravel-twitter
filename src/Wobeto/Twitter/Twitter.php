<?php

/**
 * @author   Fernando Wobeto <fernandowobeto@gmail.com>
 * @license  MIT License
 */

namespace Wobeto\Twitter;

use \Config;

class Twitter{

	private $oauth_access_token;
	private $oauth_access_token_secret;
	private $consumer_key;
	private $consumer_secret;
	
	protected $oauth;
	
	private $getdata = array();
	private $requestmethod;
	
	private $url;
	private $settings = array('oauth_access_token','oauth_access_token_secret','consumer_key','consumer_secret');
	
	private $api_url = 'https://api.twitter.com';
	private $api_version = '1.1';
	
	private $resources = array(
		'request'=>array(
			 'resource'=>'statuses/user_timeline.json',
			 'method'=>'GET'
		),
		 'post_message'=>array(
			  'resource'=>'statuses/update.json',
			  'method'=>'POST'
		 ),
		 'delete_message'=>array(
			  'resource'=>'statuses/destroy/%d.json',
			  'method'=>'POST'
		 ),
		 'profile'=>array(
			  'resource'=>'account/verify_credentials.json',
			  'method'=>'GET'
		 ),
		 'followers_list'=>array(
			  'resource'=>'followers/list.json',
			  'method'=>'GET'
		 )
	);
	/**
	 * Create the API access object. Requires an array of settings::
	 * oauth access token, oauth access token secret, consumer key, consumer secret
	 * These are all available by creating your own application on dev.twitter.com
	 * Requires the cURL library
	 * 
	 */
	public function __construct(){
		if(!in_array('curl',get_loaded_extensions())){
			throw new Exception('É preciso instalar o cURL, veja em: http://curl.haxx.se/docs/install.html');
		}
		
		$settings = Config::get('twitter.auth');

		if(count(array_intersect_key(array_flip($this->settings),$settings))!=count($this->settings)){
			throw new Exception('Tenha certeza que definiu corretamente os parâmetros');
		}
		
		foreach($settings AS $setting=> $value){
			$this->$setting = $value;
		}
	}
	
	/**
	 * Perform the actual data retrieval from the API
	 * 
	 * @param boolean $return If true, returns data.
	 * 
	 * @return string json If $return param is true, returns json data.
	 */
	public function all($total = NULL){
		if(is_numeric($total)):
			$this->setData('count',$total);
		endif;		
		return $this->prepare('request')->make();
	}
	
	public function post($message){
		return $this->setData('status',$message)->prepare('post_message')->make();	
	}
	
	public function profile(){		
		return $this->prepare('profile')->make();
	}	
	
	public function delete($id){		
		return $this->setData('id',$id)->prepare('delete_message',$id)->make();
	}	
	
	public function getFollowers(){
		return $this->prepare('followers_list')->make();
	}
	
	private function prepare($resource,$additional = NULL){
		$resource = $this->resources[$resource];
		$path = sprintf('%s/%s/%s',$this->api_url,$this->api_version,$resource['resource']);
		if(!is_null($additional)):
			$path = sprintf($path,$additional);
		endif;
		$this->buildOauth($path,$resource['method']);
		return $this;
	}
	
	private function make(){
		$header = array($this->buildAuthorizationHeader($this->oauth),'Expect:');

		$options = array(
			 CURLOPT_HTTPHEADER=>$header,
			 CURLOPT_HEADER=>false,
			 CURLOPT_URL=>$this->url,
			 CURLOPT_RETURNTRANSFER=>true,
			 CURLOPT_TIMEOUT=>10
		);

		if(count($this->getData())){
			switch($this->requestmethod){
				case 'POST':
					$options[CURLOPT_POSTFIELDS] = $this->getData();
				break;
				case 'GET':
					$options[CURLOPT_URL] .= '?'.http_build_query($this->getData());
				break;
			}
		}
		
		$this->resetData();

		$feed = curl_init();
		curl_setopt_array($feed,$options);
		$json = curl_exec($feed);
		curl_close($feed);

		return json_decode($json);		
	}	

	/**
	 * Build the Oauth object using params set in construct and additionals
	 * passed to this method. For v1.1, see: https://dev.twitter.com/docs/api/1.1
	 * 
	 * @param string $url The API url to use. Example: https://api.twitter.com/1.1/search/tweets.json
	 * @param string $requestMethod Either POST or GET
	 * @return \Twitter Instance of self for method chaining
	 */
	public function buildOauth($url,$requestMethod){
		$requestMethod = strtoupper($requestMethod);

		if(!in_array($requestMethod,array('POST','GET'))){
			throw new Exception('Request method must be either POST or GET');
		}

		$this->requestmethod = $requestMethod;

		$oauth = array(
			 'oauth_consumer_key'=>$this->consumer_key,
			 'oauth_nonce'=>time(),
			 'oauth_signature_method'=>'HMAC-SHA1',
			 'oauth_token'=>$this->oauth_access_token,
			 'oauth_timestamp'=>time(),
			 'oauth_version'=>'1.0'
		);

		if($this->requestmethod=='GET'&&count($this->getData())){
			$oauth = array_merge($oauth,$this->getData());
		}

		$base_info = $this->buildBaseString($url,$requestMethod,$oauth);
		$composite_key = rawurlencode($this->consumer_secret).'&'.rawurlencode($this->oauth_access_token_secret);
		$oauth_signature = base64_encode(hash_hmac('sha1',$base_info,$composite_key,true));
		$oauth['oauth_signature'] = $oauth_signature;

		$this->url = $url;
		$this->oauth = $oauth;

		return $this;
	}

	/**
	 * Private method to generate the base string used by cURL
	 * 
	 * @param string $baseURI
	 * @param string $method
	 * @param array $params
	 * 
	 * @return string Built base string
	 */
	private function buildBaseString($baseURI,$method,$params){
		$return = array();
		ksort($params);

		foreach($params as $key=> $value){
			$return[] = "$key=".$value;
		}
		return $method."&".rawurlencode($baseURI).'&'.rawurlencode(implode('&',$return));
	}

	/**
	 * Private method to generate authorization header used by cURL
	 * 
	 * @param array $oauth Array of oauth data generated by buildOauth()
	 * 
	 * @return string $return Header used by cURL for request
	 */
	private function buildAuthorizationHeader($oauth){
		$return = 'Authorization: OAuth ';
		$values = array();

		foreach($oauth as $key=> $value){
			$values[] = "$key=\"".rawurlencode($value)."\"";
		}

		$return .= implode(', ',$values);
		return $return;
	}
	
	private function setData($key,$value = NULL){
		if(!is_array($key)):
			$key = array($key=>$value);
		endif;
		$this->getdata = $key;
		return $this;
	}
	
	private function getData(){
		return $this->getdata;
	}
	
	private function resetData(){
		$this->getdata = array();
	}	

}
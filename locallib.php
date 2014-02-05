<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Internal library of functions for module quizletimport
 *
 * All the quizletimport specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_quizletimport
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/oauthlib.php');

/**
 * File browsing support class
 */
class quizletimport_quizlet {

    /** @var bool flag of login status */
    public $logged=false;

    public $state ="";
	
    /**
     * Constructor of quizlet plugin
     *
     * @param array $options
     */
    public function __construct($quizletimport) {
        global $CFG;


       /* $callbackurl = new moodle_url($CFG->wwwroot.'/mod/quizletimport/quizletcallback.php', array(
            'callback'=>'yes'
            )); */
       // $callbackurl = new moodle_url('http://demo.poodll.com/filter/poodll/quizlet.php');
        $callbackurl = new moodle_url($CFG->wwwroot. '/admin/oauth2callback.php');

		
        $this->state = md5(mt_rand().microtime(true)); // CSRF protection
		 $config = get_config('quizletimport');
        $args = array(
            'oauth_consumer_key'=>$config->apikey,
            'oauth_consumer_secret'=>$config->apisecret,
            'authorize_url' => "https://quizlet.com/authorize",
            'request_token_api' => "https://quizlet.com/authorize",
            'access_token_api' => 'https://api.quizlet.com/oauth/token',
            'oauth_callback' => $callbackurl->out(false),
			'api_scope' => 'read%20write_set',
            'api_root' => 'https://api.quizlet.com/oauth'
        );

        $this->quizlet = new quizlet($args);
    }
    

	
	   /**
     * Set quizlet option
     * @param array $options
     * @return mixed
     */
     /*
    public function set_option($options = array()) {
        if (!empty($options['quizlet_key'])) {
            set_config('quizlet_key', trim($options['quizlet_key']), 'quizlet');
        }
        if (!empty($options['quizlet_secret'])) {
            set_config('quizlet_secret', trim($options['quizlet_secret']), 'quizlet');
        }
  
        unset($options['quizlet_key']);
        unset($options['quizlet_secret']);
        $ret = parent::set_option($options);
        return $ret;
    }
    */

    /**
     * Get quizlet options
     * @param string $config
     * @return mixed
     */
     /*
    public function get_option($config = '') {
        if ($config==='quizlet_key') {
            return trim(get_config('quizlet', 'quizlet_key'));
        } elseif ($config==='quizlet_secret') {
            return trim(get_config('quizlet', 'quizlet_secret'));
        } else {
            $options = parent::get_option();
            $options['quizlet_key'] = trim(get_config('quizlet', 'quizlet_key'));
            $options['quizlet_secret'] = trim(get_config('quizlet', 'quizlet_secret'));
        }
        return $options;
    }
    */
}//end of quizlet import



/**
 * Authentication class to access Quizlet API
 * originally extended oauth_helper, but it was not so helpful
 * quizlet oauth works differently to facebook/google in some ways
 * i)The initial request url for an oauth_token can be made very simply
 * ii) quizlet uses http basic auth in request for access_token, facebook etc doesnt
 * iii) quizlet uses access token and username in data requests, facebook etc use access_token and secrets
 * 
 *
 * @package    quizlet_inport
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizlet {
   
   const ACCESS_TOKEN = "access_token";
   const ACCESS_USERNAME= "user_id";
   
       /** @var string consumer key, issued by oauth provider*/
    protected $consumer_key;
    /** @var string consumer secret, issued by oauth provider*/
    protected $consumer_secret;
    /** @var string oauth root*/
    protected $api_root;
    /** @var string request token url*/
    protected $request_token_api;
    /** @var string authorize url*/
    protected $authorize_url;
    protected $http_method;
    /** @var string */
    protected $access_token_api;
    /** @var curl */
    protected $http;
    /** @var array options to pass to the next curl request */
    protected $http_options;
	
	protected $api_scope;
    /**
     * Constructor for dropbox class
     *
     * @param array $args
     */
    function __construct($args) {
       if (!empty($args['api_root'])) {
            $this->api_root = $args['api_root'];
        } else {
            $this->api_root = '';
        }
        $this->consumer_key = $args['oauth_consumer_key'];
        $this->consumer_secret = $args['oauth_consumer_secret'];

        if (empty($args['request_token_api'])) {
            $this->request_token_api = $this->api_root . '/request_token';
        } else {
            $this->request_token_api = $args['request_token_api'];
        }

        if (empty($args['authorize_url'])) {
            $this->authorize_url = $this->api_root . '/authorize';
        } else {
            $this->authorize_url = $args['authorize_url'];
        }

        if (empty($args['access_token_api'])) {
            $this->access_token_api = $this->api_root . '/access_token';
        } else {
            $this->access_token_api = $args['access_token_api'];
        }

        if (!empty($args['oauth_callback'])) {
            $this->oauth_callback = new moodle_url($args['oauth_callback']);
        }
        if (!empty($args['access_token'])) {
            $this->access_token = $args['access_token'];
        }
        if (!empty($args['access_token_secret'])) {
            $this->access_token_secret = $args['access_token_secret'];
        }
		if (empty($args['api_scope'])) {
			$this->api_scope ="read%20write_set";
		}else{
            $this->api_scope = $args['api_scope'];
        }
		
        $this->http = new curl(array('debug'=>false));
        $this->http_options = array();
    }
    
	/**
     * Confirm we are authenticated
     * 
     */
	public function is_authenticated(){
		if($this->get_stored_data(self::ACCESS_TOKEN) ==null){
			return false;
		}else{
			return true;
		}
	}
	
	/**
     * Fetch the intial url for confirming auth to quizlet
     * 
     */
    public function fetch_auth_url(){
		global $PAGE;

    	$thecallbackurl = rawurlencode($this->oauth_callback->out(false));

		//This is a bit complex
		//oauth lib for moodle sends the final redirect url as "state" variable
		//the actual security check is done by getting the sessparam from that
		//see /admin/oauth2callback.php to see how it happens.
		$urlstate = $PAGE->url;
		$urlstate->param('sesskey', sesskey());
		$urlstatestring = rawurlencode($urlstate->out(false));
		
    	$ret = $this->authorize_url . "?state={$urlstatestring}" . 
    			"&client_id={$this->consumer_key}&scope={$this->api_scope}" .
    			"&response_type=code&redirect_uri={$thecallbackurl}";
    			
    	return $ret;		
    	
    }
    
	/**
     * Request oauth access token from server
	 * If this succeeds authentication is done, and it is on 
	 * to getting data
	 *
     * @param string $token
     */
    public function get_access_token($token) {
	
		$payload = array(
			'code' => $token,
			'redirect_uri' => $this->oauth_callback->out(false),
			'grant_type' => 'authorization_code'
		);
		$curl = curl_init($this->access_token_api);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERPWD, "{$this->consumer_key}:{$this->consumer_secret}");
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
		$returndata = json_decode(curl_exec($curl), true);
		$responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
	 
		// Handle return data or error
		if ($responseCode == 200) { 
			$this->store_data(self::ACCESS_TOKEN, $returndata['access_token']);
			$this->store_data(self::ACCESS_USERNAME, $returndata['user_id']);
			return $this->fetch_data_return($returndata );
		}else{
			return $this->fetch_error_return($returndata );
		}
	 
		
		//$accessToken = $token['access_token'];
		//$username = $token['user_id']; // the API sends back the username of the user in the access token
		

	/*
        $this->sign_secret = $this->consumer_secret.'&'.$secret;
        $params = $this->prepare_oauth_parameters($this->access_token_api, array('oauth_token'=>$token, 'oauth_verifier'=>$verifier), 'POST');
        $this->setup_oauth_http_header($params);
        // Should never send the callback in this request.
        unset($params['oauth_callback']);
        $content = $this->http->post($this->access_token_api, $params, $this->http_options);
        $keys = $this->parse_result($content);
        $this->set_access_token($keys['oauth_token'], $keys['oauth_token_secret']);
        return $keys;
		*/
    }
	
	private function fetch_error_return($data){
		$ret = array();
		$ret['success'] = false;
		$ret['data'] = null;
		if(!$data){
			$ret['error'] = "an unknown error occurred. Nothing recieved from quizlet.";
			return $ret;
		}
		if(!$data['error']){
			$ret['error'] = 'Error code: ' . $data['error'] ;
		}
		if(!$data['error_description']){
			$ret['error'] = $ret['error'] . ': Description: ' . $data['error_description'] ;
		}
		return $ret;
	}
	
	private function fetch_data_return($data){
		$ret = array();
		$ret['success'] = true;
		$ret['data'] = $data;
		$ret['error'] = '';
		return $ret;
	}
	   /**
     * Request oauth protected resources
     * @param string $method
     * @param string $url
     * @param string $token
     * @param string $secret
     */
    public function request($endpoint, $params){
		//build our request URL
		$apiurl = 'https://api.quizlet.com/2.0/';
		$endpoint = str_replace('@username@', $this->get_stored_data(self::ACCESS_USERNAME),$endpoint);
		$useparams='?whitespace=1';
		if($params){
			foreach($params  as $key => $value){
				$useparams .='&';
				$useparams .= $key . '=' . $value ;
			}
		}
		$curl = curl_init("https://api.quizlet.com/2.0" . $endpoint . $useparams);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->get_stored_data(self::ACCESS_TOKEN)));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$data = json_decode(curl_exec($curl));
		$responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		 echo "https://api.quizlet.com/2.0" . $endpoint . $useparams;
		if (floor($responseCode / 100) != 2) { // A non 200-level code is an error (our API typically responds with 200 and 204 on success)
			return $this->fetch_error_return($data);
		}else{
			return $this->fetch_data_return($data);
		}
	
		/*
        if (empty($token)) {
            $token = $this->access_token;
        }
        if (empty($secret)) {
            $secret = $this->access_token_secret;
        }
        // to access protected resource, sign_secret will alwasy be consumer_secret+token_secret
        $this->sign_secret = $this->consumer_secret.'&'.$secret;
        if (strtolower($method) === 'post' && !empty($params)) {
            $oauth_params = $this->prepare_oauth_parameters($url, array('oauth_token'=>$token) + $params, $method);
        } else {
            $oauth_params = $this->prepare_oauth_parameters($url, array('oauth_token'=>$token), $method);
        }
        $this->setup_oauth_http_header($oauth_params);
        $content = call_user_func_array(array($this->http, strtolower($method)), array($url, $params, $this->http_options));
        // reset http header and options to prepare for the next request
        $this->http->resetHeader();
        // return request return value
        return $content;
		*/
    }
	

	
	    /**
     * Store a token/username between requests. Currently uses
     * session named by get_storename
     *
     * @param stdClass|null $token token object to store or null to clear
     */
    protected function store_data($field, $data) {
        global $SESSION;

        //$this->accesstoken = $token;
        $name = $this->get_storename($field);

        if ($data !== null) {
            $SESSION->{$name} = $data;
        } else {
            unset($SESSION->{$name});
        }
    }
	
	    /**
     * Returns the tokenname/username field for the access_token to be stored
     * through multiple requests.
     *
     * The default implentation is to use the classname combiend
     * with the scope.
     *
     * @return string tokenname for prefernce storage
     */
    protected function get_storename($field) {
        // This is unusual but should work for most purposes.
        return get_class($this).'-'.md5($this->api_scope) . '-' . $field;
    }
	
	/**
     * Retrieve a token stored.
     *
     * @return stdClass|null token object
     */
    protected function get_stored_data($field) {
        global $SESSION;

         $name = $this->get_storename($field);

        if (isset($SESSION->{$name})) {
            return $SESSION->{$name};
        }

        return null;
    }


    /**
     * Get file listing from dropbox
     *
     * @param string $path
     * @param string $token
     * @param string $secret
     * @return array
     */
     /*
    public function get_listing($path='/', $token='', $secret='') {
        $url = $this->dropbox_api.'/metadata/'.$this->mode.$path;
        $content = $this->get($url, array(), $token, $secret);
        $data = json_decode($content);
        return $data;
    }
    */


}

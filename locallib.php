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
    //later we eed to get these from settings
    public $quizlet_key ="fFCBWXYZSc";
    public $quizlet_secret ="QSMAMrrOOMaI0h.LS8axpQ";
    public $state ="";
	
    /**
     * Constructor of quizlet plugin
     *
     * @param array $options
     */
    public function __construct($options = array()) {
        global $CFG;

       // $this->quizlet_key = $this->get_option('quizlet_key');
        //$this->quizlet_secret  = $this->get_option('quizlet_secret');

/*
        if (isset($options['access_key'])) {
            $this->access_key = $options['access_key'];
        } else {
            $this->access_key = get_user_preferences($this->setting.'_access_key', '');
        }
        if (isset($options['access_secret'])) {
            $this->access_secret = $options['access_secret'];
        } else {
            $this->access_secret = get_user_preferences($this->setting.'_access_secret', '');
        }

        if (!empty($this->access_key) && !empty($this->access_secret)) {
            $this->logged = true;
        }
*/
       /* $callbackurl = new moodle_url($CFG->wwwroot.'/mod/quizletimport/quizletcallback.php', array(
            'callback'=>'yes'
            )); */
        $callbackurl = new moodle_url('http://demo.poodll.com/filter/poodll/quizlet.php');
            
        $this->state = md5(mt_rand().microtime(true)); // CSRF protection

        $args = array(
            'oauth_consumer_key'=>$this->quizlet_key,
            'oauth_consumer_secret'=>$this->quizlet_secret,
            'authorize_url' => "https://quizlet.com/authorize",
            'request_token_api' => "https://quizlet.com/authorize",
            'access_token_api' => 'https://api.quizlet.com/oauth/token',
            'oauth_callback' => $callbackurl->out(false),
            'api_root' => 'https://api.quizlet.com/oauth'
        );

        $this->quizlet = new quizlet($args);
    }
    
    public function fetch_auth_url(){
    	$result = $this->quizlet->request_token();
		$authurl = $result['authorize_url'];
    	return($authurl);
    }

    /**
     * Set access key
     *
     * @param string $access_key
     */
    public function set_access_key($access_key) {
        $this->access_key = $access_key;
    }

    /**
     * Set access secret
     *
     * @param string $access_secret
     */
    public function set_access_secret($access_secret) {
        $this->access_secret = $access_secret;
    }


    /**
     * Check if moodle has got access token and secret
     *
     * @return bool
     */
    public function check_login() {
        return !empty($this->logged);
    }

  

    /**
     * Request access token
     *
     * @return array
     */
    public function callback() {
        $token  = optional_param('oauth_token', '', PARAM_TEXT);
       // $secret = get_user_preferences($this->setting.'_request_secret', '');
        $access_token = $this->quizlet->get_access_token($token, $secret);
        // set_user_preference($this->setting.'_access_key', $access_token['oauth_token']);
        // set_user_preference($this->setting.'_access_secret', $access_token['oauth_token_secret']);
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
 * Authentication class to access Dropbox API
 *
 * @package    quizlet_inport
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizlet extends oauth_helper {
   
    /** @var string dropbox api url*/
   // private $dropbox_api = 'https://api.quizlet.com';
    /** @var string dropbox content api url*/
  //  private $dropbox_content_api = 'https://api.quizlet.com';

    /**
     * Constructor for dropbox class
     *
     * @param array $args
     */
    function __construct($args) {
        parent::__construct($args);
    }
    
    private function fetch_auth_url(){
    	$scope = "read%20write_set";
    	$thecallbackurl = rawurlencode($this->oauth_callback->out(false));
    	$state = md5(mt_rand().microtime(true));
    	$ret = $this->authorize_url . "?state={$state}" . 
    			"&client_id={$this->consumer_key}&scope={$scope}" .
    			"&response_type=code&redirect_uri={$thecallbackurl}";
    			
    	return $ret;		
    	
    }
    
    /*  We prepare the auth url using our client id. The callback will recieve
    	this request_token as code
    */
    public function request_token() {
    	$result = array();
    	$result['authorize_url'] = fetch_auth_url();
    	return $result;
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

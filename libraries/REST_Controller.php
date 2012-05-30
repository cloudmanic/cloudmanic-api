<?php defined('BASEPATH') OR exit('No direct script access allowed');
//
// By: Spicer Matthews
// Company: Cloudmanic Labs, LLC 
// Website: http://cloudmanic.com
// Code Taken From: Phil Sturgeon http://philsturgeon.co.uk
//

class REST_Controller extends CI_Controller 
{
	protected $_request_method;
	protected $_output_format;
	protected $_get_args = array();
	protected $_post_args = array();
	protected $_put_args = array();
	protected $_delete_args = array();
	protected $_args = array();
	protected $_spark_version = '1.0.0';
	 
	// List all supported methods, the first will be the default format
	protected $_supported_formats = array(
		'xml' => 'application/xml',
		'rawxml' => 'application/xml',
		'json' => 'application/json',
		'serialize' => 'application/vnd.php.serialized',
		'php' => 'text/plain'
	);
	
	// Default response to requests
	protected $_return = array('status' => 1, 'errors' => array(), 
															'data' => array(), 'total' => 0,
															'filtered' => 0); 
	
	//
	// Constructor …
	//
	function __construct()
	{
		parent::__construct();
		
		// If this is a spark we load up the spark package 
		// so we can have access to all the files within.
		if(defined('SPARKPATH'))
		{			
			$this->load->spark('cloudmanic-api/' . $this->_spark_version);
			log_message('debug', 'Spark Package Initialized');
		}
	
		// Load up configs & libraries.
		$this->load->config('rest');
		$this->load->library('security');
		$this->load->library('format');
		
		// Set up our GET / POST variables
		$this->_get_args = array_merge($this->uri->ruri_to_assoc(), $_GET);
		$this->_post_args = $_POST;
		$this->_args = array_merge($this->_get_args, $this->_post_args);
		
		// Detect params. that the request sent in.
		$this->_request_method = $this->_detect_method();
		$this->_output_format = $this->_detect_output();
		
		// Now authenticate the request.
		$this->_authenticate();
	}
	
	//
	// Remap: Requests are not made to methods directly The request will be for an "object".
	//				this simply maps the object and method to the correct Controller method.
	//
	function _remap($object_called, $arguments)
	{
		$controller_method = $object_called . '_' . $this->_request_method;
		
		// Make sure it exists, but can they do anything with it?
		if(! method_exists($this, $controller_method))
		{
			$this->_return['status'] = 0;
			$this->_return['errors'][] = 'Unknown method.';		
			$this->response($this->_return, 200);
		}
		
		// All is good. Call the API Contoller method
		call_user_func_array(array($this, $controller_method), $arguments);
	}
	
	//
	// Response: Takes pure data and optionally a status code, then creates the response.
	//
	function response($data = array(), $http_code = null)
	{
		// If data is empty and not code provide, error and bail
		if(empty($data) && $http_code === null)
		{
			$http_code = 404;
 		} 
		
		// Format and return output.
		is_numeric($http_code) OR $http_code = 200;
		header('Content-Type: '.$this->_supported_formats[$this->_output_format]);
		$output = $this->format->factory($data)->{'to_' . $this->_output_format}();
		header('HTTP/1.1: ' . $http_code);
		header('Status: ' . $http_code);
		header('Content-Length: ' . strlen($output));
		
		exit($output);
	}
	
	//
	// A getter function to return a POST variable.
	//
	function get($key = NULL, $xss_clean = TRUE)
	{
		if($key === NULL)
		{
			return $this->_get_args;
		}

		return array_key_exists($key, $this->_get_args) ? $this->_xss_clean($this->_get_args[$key], $xss_clean) : FALSE;
	}

	//
	// A getting function to return a POST varaible.
	//
	public function post($key = NULL, $xss_clean = TRUE)
	{
		if($key === NULL)
		{
			return $this->_post_args;
		}

		return $this->input->post($key, $xss_clean);
	}
	
	// ----------- Private Helpers ------------ //
	
	//
	// Here we authenticate the API Request.
	// We hand the request off to a library 
	// and a method that you set in the rest.php 
	// config. If this request returns FALSE
	// We die sending back a can't access 
	// our API response. 
	// 
	private function _authenticate()
	{
		$config = config_item('api_enable_custom_auth');
		$skip = config_item('api_skip_auth');
		$this->load->library($config['library']);
		
		// First see if this is a request we skip over.
		// Meaning we set in the config file no authentication 
		// for this controller method.
		if(isset($skip[$this->router->class][$this->router->method]) &&
				$skip[$this->router->class][$this->router->method])
		{
			return TRUE;
		}
		
		// Return TRUE and carry on with the REQUEST if the 
		// library returns TRUE.
		if(isset($config['library']) && isset($config['method']))
		{
			if($this->{$config['library']}->{$config['method']}())
			{
				return TRUE;
			}
		} else
		{
			return TRUE;
		}
		
		// Oops we did not authenticate tell the user and stop.
		$this->_return['status'] = 0;
		$this->_return['errors'][] = 'Not Authorized.';		
		$this->response($this->_return, 200);
	}
	
	//
	// Figure out what our output format should be.
	// We trigger off the format arg.
	//
	private function _detect_output()
	{
		if(isset($this->_args['format']) && 
				isset($this->_supported_formats[$this->_args['format']]))
		{
			return $this->_args['format'];
		}
	
		$default = config_item('api_default_format');
		
		if(isset($this->_args['format']))
		{
			if(! isset($this->_supported_formats[$this->_args['format']]))
			{
				show_error('Default format not supported.');		
			}
		}
	
		return $default;
	}
	
	//
	// Figure out what type of request this is.
	// We default to a get request.
	//
	private function _detect_method()
	{
		$method = strtolower($this->input->server('REQUEST_METHOD'));

		if(in_array($method, array('get', 'delete', 'post', 'put')))
		{
			return $method;
		}

		return 'get';
	}
	
	//
	// Clean up data.
	//
	private function _xss_clean($val, $bool)
	{
		return $bool ? $this->security->xss_clean($val) : $val;
	}
}


/* End File */
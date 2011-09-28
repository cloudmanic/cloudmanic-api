## Overview

This package is an API controller with default CRUD operations buit-in. From the url request we can have fully customizable CRUD operations driven from your models. 

Codeignitor does not have a built-in API like other frameworks. Delivering data via json, xml, and other formats is part of almost any web application these days. We need to deliver data to javascript based interactions. We need to deliver data to mobile devices. 

This package is designed to be installed via [http://getsparks.org](http://getsparks.org). This package gives you a new controller you extend. Instead of extending CI_Controller you extend API_Controller. By extending this controller you are given options for formatting your output, standard CRUD operations, and authentication. Since we are extending the controller you do not have to load this via $this->load->spark.

THIS IS NOT A REST API. While this API takes some concepts from a traditional REST API it is not built to the correct standard. 

The original concept for this package comes from [Phil Sturgeon](http://philsturgeon.co.uk) who authored a full REST API for Codeignitor. To learn more about his solution check out [Working with RESTful Services in CodeIgniter](http://net.tutsplus.com/tutorials/php/working-with-restful-services-in-codeigniter-2/). Some of the code in this package is a direct copy and paste from his open source library. We have stripped out some of the bells and whistles he provides to deliver a more robust but focused solution. 
 
## Requirements

1. PHP 5.3+
2. CodeIgniter 2.0.0+
3. CURL


## Installation 

Step #1) This has nothing to do with configs but this file is loaded early enough to so is a great place to have an auto loader. When you extend your controller with "API_Controller" the system will not know where to find that class so with the PHP autoloader you can tell the system where the library file lives and auto load it. More information here: [http://philsturgeon.co.uk/news/2010/02/CodeIgniter-Base-Classes-Keeping-it-DRY](http://philsturgeon.co.uk/news/2010/02/CodeIgniter-Base-Classes-Keeping-it-DRY).

If you are not installing this package via sparks or a raw third party install put your REST_Controller library in the core directory and remove the "case 'REST_Controller' & 'API_Controller':" statement. 

Also, don't forget to update the spark include path to the proper version number. This is where it says "X.X.X" below.


```
File: config/config.php

/*
| -------------------------------------------------------------------
|  Native Auto-load
| -------------------------------------------------------------------
| 
| Nothing to do with cnfig/autoload.php, this allows PHP autoload to work
| for base controllers and some third-party libraries.
|
*/
function __autoload($class)
{
	if((strpos($class, 'CI_') !== 0) && (strpos($class, 'MY_') !== 0))
	{
		switch($class)
		{			
			case 'API_Controller':	
			case 'REST_Controller':		
				include_once(SPARKPATH . 'cloudmanic-api/X.X.X/libraries/'. $class . EXT);	
			break;
			
			default:
				@include_once(APPPATH . 'core/'. $class . EXT);	
			break;
		}
	}
}
```

Step #2) Review the configs in SPARKPATH . 'cloudmanic-api/rest.php. By setting this config $config['api_model_guess'] the API will read the URL request and load a model based on that request. Then based on the action it will call particular functions in that model to return data. More on this below.
	
If you are going to authenticate you need to set $config['api_enable_custom_auth'] in the config. You are going to set a library and a method to call when a user tries to authenticate. If the function returns TRUE the user is authenticated if false the user is given an error message. The API will auto load the library you are calling with $this->load->library('blah'). You have to build your own authentication library and method. This give you complete control. More on authentication below. 

Step #3) Build your first controller. You build a controller like any other Codeignitor but you extend API_Controller. Below is an example.
	
```
<?php if(! defined('BASEPATH')) exit('No direct script access allowed');

class People extends API_Controller { }

/* End File */
```

You would access this API this way: http://yourdomain.com/people/{get, create, delete, update}/format/{json, php, xml}


## Authentication

You are in complete control of how a user authenticates. You do this by building a library and method that gets called on requests. You set this library and method in $config['api_enable_custom_auth']. If you do not set this requests will not be authenticated. This is nice because you can support different forms of authentication. For example you might want to authenticate a web request that already has a session differently from an API request from a mobile device that might use API keys. Below is an example of how you might handle this.

Rest.php

```
$config['api_enable_custom_auth'] = array('library' => 'auth', 'method' => 'api');
```

Auth.php


```
//
// We call this from API requests. We see if we have an 
// API Key or a Session Key. We create a session differently
// based on what type of request comes in. Return TRUE if
// we are authenticated return FALSE if we are not authenticated.
//
function api()
{
  // If we have a session we via a Cookie and the CI session lib
  // we create the session.
  if($this->CI->session->userdata('LoggedIn'))
  {
  	return TRUE;
  }
  
  // Now check to see if an API key was passed in.
  // If an API key was passed in we check our API table
  // and create a session from there.
  if($this->CI->input->server('PHP_AUTH_PW'))
  {
		$key = $this->CI->input->server('PHP_AUTH_PW');
		
		if($key == 'blah woot blah')
		{
			return TRUE;
		}
  }

  return FALSE;
}  
```

## Basic CRUD

If you have an empty controller like the one above the following API requests can be called.


```
http://yourdomain.com/people/{get, create, delete, update}/format/{json, php, xml}
```

These calls are delivered from the API_Controller.php library. These calls will detect a model name from the API url. You can set which segment with from the $config['api_model_guess'] config. In the example above the url segment would be 1 (people).

```
$config['api_model_guess'] = array('segment' => 1, 'postfix' => '_model');
```

With the above request the system would auto load the models/people_model.php then it would call a function within that model based on the action (get, create, delete, update). The data that function returns will then be formatted and returned with the request. You can set the function name that gets called with the $config['api_model_functions'] config.

If you do not want to set the model by guessing in the URL request you can set the model name in your constructor with the set_model() function call. See below.

```
<?php if(! defined('BASEPATH')) exit('No direct script access allowed');

class People extends API_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->_set_model('people');
	}
}

/* End File */

```

If you do not want to use the standard CRUD operations that we provide you can override the following functions.

```
class People extends API_Controller
{	
	function get_get()
	{
		// do something ….
	}
	
	function create_post()
	{
		// do something ….
	}
	
	function delete_post(
	{
		// do something ….
	}
	
	function update_post()
	{
		// do something ….
	}
}
```

You can also return just a sub-set of columns by setting the set_select_fields columns. You can also control what columns a user can insert or update into. If they post more than these columns they will be filtered out.

```
class People extends API_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->set_select_fields(array('Id', 'LastName', 'FirstName', 'Email'));
		$this->set_create_fields(array('Id', 'LastName', 'FirstName', 'Email'));
		$this->set_update_fields(array('Id', 'LastName', 'FirstName', 'Email'));
	}
}
```


You can also tell the system not to allow certain CRUD functions. API requests to these functions. You might not want to open up full CRUD to this model. 

```
class People extends API_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->set_not_allowed_methods(array('get', 'update'));
	}
}

```

## Validation

When using our built-in CRUD operations we provide validation via the config/form_validation.php. We call validation rules from there. The rule names will be in this format 'api-' . object . '-' . action. So for an update operation on people your validation rule would be "api-people-update". If validation fails the the field names with error messages will be returned.



## Custom API Calls

As you can see the main part of this package is allowing you to have a toolbox for easy CRUD operations. Very little has to be done to have full CRUD on your database via your models. You can also build your own controller methods. You just have to add a "_post" or "_get" after you method calls. Here is an example.

```
class People extends API_Controller
{
	function max_get()
	{
		$this->load->model('people_model');
		$this->_return['data'] = $this->people_model->get_max_number_of_people();
		$this->response($this->_return, 200);
	}
}
```

You can can pass in any array you want into response() but $this->_return is preloaded in a nice format with meta data. The second argument is the http response code. 

You would access the above request like this.

```
http://example.org/people/max/format/json
```


## Functions

Here is a list of public functions you can call in your controllers. 

* set_model()
* clear_select_fields()
* clear_create_fields()
* clear_update_fields()
* set_select_fields()
* set_create_fields()
* set_update_fields()
* set_not_allowed_methods(array())
* clear_not_allowed_methods()


## Author(s) 

* Company: Cloudmanic Labs, [http://cloudmanic.com](http://cloudmanic.com)

* By: Spicer Matthews [http://spicermatthews.com](http://spicermatthews.com)

* By: Phil Sturgeon (http://philsturgeon.co.uk/)[http://philsturgeon.co.uk/]
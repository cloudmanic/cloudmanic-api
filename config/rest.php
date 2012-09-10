<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| API Format
|--------------------------------------------------------------------------
|
| What format should the data be returned in by default?
|
|	Default: xml
|
*/
$config['api_default_format'] = 'json';


/*
|--------------------------------------------------------------------------
| API Enable Custom Authentication
|--------------------------------------------------------------------------
|
| If this variable is empty we provide no authentication for the API 
| requests. Your system is wide open. You can set the library, and method
| indexs in the array. We will load this library and call the method for 
| authentication. If the method returns FALSE we failed authentication.
| If the method returns true we assume we are authenticated and continue 
| with the request. 
|
| $config['api_enable_custom_auth'] = array('library' => '', 'method' => '');
|
*/
//$config['api_enable_custom_auth'] = array('library' => '', 'method' => '');
																						
/*
|--------------------------------------------------------------------------
| API Skip Auth
|--------------------------------------------------------------------------
|
| Sometimes you want to have part of your API that does not require 
| authentication. We can use this to set a controller and method
| that will skip over authentication.
|
|
*/
$config['api_skip_auth']['users']['get'] = TRUE;																						
																																											
/*
|--------------------------------------------------------------------------
| API Auto Guess Model
|--------------------------------------------------------------------------
|
| Programmers are lazy. Lets make our controllers even simpler by auto guessing
| what model the url request is for. This way to activate this API call 
| all you have to do build an empty controller and BOOM you have full CRUD on a model. 
| If this array is empty it will not try to guess your model you will have to call
| the $this->set_model() function. If you set this array you do not have to call set_model()
| First argment is which url segment use as a guess for the model. The section is a postfix
| you might want to append to a model file name. 
|
| $config['api_model_guess'] = array('segment' => 1, 'postfix' => '_model');
|
*/
$config['api_model_guess'] = array('segment' => 1, 'postfix' => '_model');

/*
|--------------------------------------------------------------------------
| API Crud Functions
|--------------------------------------------------------------------------
|
| If you are using our pre-canned CRUD functions the system will auto
| load a model and then call a particular function in that model
| to have data returned. Here you can set what those functions will be.
| All these functions must be set in your model. Maybe extend CI_Model
| With MY_Model to support call these calls. 
|
|
*/
$config['api_model_functions']['get'] = 'get';
$config['api_model_functions']['getid'] = 'get_by_id';
$config['api_model_functions']['create'] = 'insert';
$config['api_model_functions']['update'] = 'update';
$config['api_model_functions']['delete'] = 'delete';
$config['api_model_functions']['total'] = 'table_total';
$config['api_model_functions']['filtered'] = 'get_filter_query_count';
$config['api_model_functions']['order'] = 'set_order';
$config['api_model_functions']['limit'] = 'set_limit';
$config['api_model_functions']['select'] = 'set_select';
$config['api_model_functions']['search'] = 'set_search';
$config['api_model_functions']['noextra'] = 'set_no_extra';


/* End file */
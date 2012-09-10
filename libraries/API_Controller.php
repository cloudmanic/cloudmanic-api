<?php defined('BASEPATH') OR exit('No direct script access allowed');
//
// By: Spicer Matthews
// Company: Cloudmanic Labs, LLC 
// Website: http://cloudmanic.com
//

class API_Controller extends REST_Controller 
{
	protected $_model = '';
	protected $_model_methods;
	protected $_not_allowed_methods = array(); 
	protected $_select_fields = array(); 
	protected $_insert_fields = array(); 
	protected $_update_fields = array();
	protected $_custom_auth = ''; 
	protected $_override_access = FALSE;
																					
	// ------------- CRUD Operations ---------------- //
		
	//
	// Return all data from a particular model. 
	//
	function get_get()
	{
		$this->_check_access('get');
		$this->_model_init();
		$this->_query_init();
		
		// If we pass in an id field we call a different function. 
		if($this->get('id'))
		{
			$this->_return['data'] = $this->{$this->_model}->{$this->_model_methods['getid']}($this->get('id'));
		} else 
		{
			$this->_return['data'] = $this->{$this->_model}->{$this->_model_methods['get']}();		
			$this->_return['filtered'] = $this->{$this->_model}->{$this->_model_methods['filtered']}();
			$this->_return['total'] = $this->{$this->_model}->{$this->_model_methods['total']}();				
		}

		$this->response($this->_return, 200);
	}
	
	//
	// Insert data into a particular model. 
	//
	function create_post()
	{
		$this->_check_access('create');
		$this->_model_init();

		// First we validate. If successful we insert the data.
		$config = config_item('api_model_guess');
		$this->load->library('form_validation');
		$obj = str_ireplace($config['postfix'], '', $this->_model); 
		if($this->form_validation->run('api-' . $obj . '-create') == FALSE)
		{
			$this->_return['status'] = 0;
			foreach($this->form_validation->_error_array AS $key => $row)
			{
				$this->_return['errors'][] = array('field' => $key, 'error' => $row);
			}
		} else
		{
			$this->_return['status'] = 1;
			$this->_return['data']['Id'] = $this->{$this->_model}->{$this->_model_methods['create']}($this->_insert_filter($_POST));
		}
		
		$this->response($this->_return, 200);
	}
	
	//
	// Update data into a particular model. 
	//
	function update_post()
	{
		$this->_check_access('update');
		$this->_model_init();

		// First we validate. If successful we insert the data.
		$config = config_item('api_model_guess');
		$this->load->library('form_validation');
		$obj = str_ireplace($config['postfix'], '', $this->_model); 
		if($this->form_validation->run('api-' . $obj . '-update') == FALSE)
		{
			$this->_return['status'] = 0;
			foreach($this->form_validation->_error_array AS $key => $row)
			{
				$this->_return['errors'][] = array('field' => $key, 'error' => $row);
			}
		} else
		{
			$this->_return['status'] = 1;
			$this->{$this->_model}->{$this->_model_methods['update']}($this->_update_filter($_POST), $this->get('id'));
		}
		
		$this->response($this->_return, 200);
	}

	//
	// Delete data from the model. 
	//
	function delete_post()
	{
		$this->_check_access('delete');
		$this->_model_init();
		
		// First we validate. If successful we insert the data.
		$config = config_item('api_model_guess');
		$this->load->library('form_validation');
		$obj = str_ireplace($config['postfix'], '', $this->_model); 
		if($this->form_validation->run('api-' . $obj . '-delete') == FALSE)
		{
			$this->_return['status'] = 0;
			foreach($this->form_validation->_error_array AS $key => $row)
			{
				$this->_return['errors'][] = array('field' => $key, 'error' => $row);
			}
		} else
		{
			$this->_return['status'] = 1;
			$this->{$this->_model}->{$this->_model_methods['delete']}($this->post('Id'));
		}
		
		$this->response($this->_return, 200);
	}
	
	// ------------- Setters & Getters ---------------------- //
	
	//
	// Clear select fields.
	//
	function clear_select_fields()
	{
		$this->_select_fields = array();
	}

	//
	// Clear insert fields.
	//
	function clear_create_fields()
	{
		$this->_insert_fields = array();
	}
	
	//
	// Clear update fields.
	//
	function clear_update_fields()
	{
		$this->_update_fields = array();
	}
	
	// 
	// Set which fields we should be allowed to select.
	// Some times we might not want to give full API access
	// to all our fields. 
	//
	function set_select_fields($data)
	{
		if(is_array($data))
		{
			foreach($data AS $key => $row)
			{
				$this->_select_fields[] = $row;
			}
		} else 
		{
			$this->_select_fields = $data;
		}
	}

	// 
	// Set which fields we should be allowed to insert.
	// Some times we might not want to give full API access
	// to insert all our fields. 
	//
	function set_create_fields($data)
	{
		if(is_array($data))
		{
			foreach($data AS $key => $row)
			{
				$this->_insert_fields[] = $row;
			}
		} else 
		{
			$this->_insert_fields = $data;
		}
	}
	
	// 
	// Set which fields we should be allowed to update.
	// Some times we might not want to give full API access
	// to update all our fields. 
	//
	function set_update_fields($data)
	{
		if(is_array($data))
		{
			foreach($data AS $key => $row)
			{
				$this->_update_fields[] = $row;
			}
		} else 
		{
			$this->_update_fields = $data;
		}
	}
	
	//
	// Here we set the model that we are using for this API call.
	//
	function set_model($model)
	{
		$this->_model = $model;
	}
	
	//
	// Some times we have a method we do not want to publicly 
	// expose. You can pass in array of methods you do not want
	// to give access to. These are the action methods in the
	// top of this library. This array can be up to 
	// array('get', 'create', 'delete', 'update'); We can 
	// pass in a string or an array.
	//
	function set_not_allowed_methods($methods)
	{
		if(is_array($methods))
		{
			$this->_not_allowed_methods = $methods;
		} else 
		{
			$this->_not_allowed_methods[] = $methods;
		}	
	}
	
	//
	// Clear not allowed methods.
	//
	function clear_not_allowed_methods()
	{
		$this->_not_allowed_methods = array();		
	}
	
	// ------------- Private Helper Functions --------------- //

	//
	// Sometimes we do not want to give a user complete
	// access to insert fields in a database table. 
	// Any extra post variables are removed by this filter.
	// 
	function _insert_filter($data)
	{
		if($this->_insert_fields)
		{
			$tmp = array();
			foreach($this->_insert_fields AS $key => $row)
			{
				$tmp[$row] = $data[$row];
			}
			return $tmp;
		} else 
		{ 
			return $data;
		}
	}
	
	//
	// Sometimes we do not want to give a user complete
	// access to update fields in a database table. 
	// Any extra post variables are removed by this filter.
	// 
	function _update_filter($data)
	{
		if($this->_update_fields)
		{
			$tmp = array();
			foreach($this->_update_fields AS $key => $row)
			{
				$tmp[$row] = $data[$row];
			}
			return $tmp;
		} else 
		{ 
			return $data;
		}
	}
	
	//
	// Check to see if we have granted the user permission for this request.
	//
	function _check_access($type)
	{
		if($this->_override_access)
		{
			return true;
		}
	
		// See if the user has not said this method is not allowed.
		if(in_array($type, $this->_not_allowed_methods))
		{
			$this->response(array('status' => 0, 'errors' => array('Access not allowed.')), 401);
		}
	}
	
	// 
	// Setup all the config variables that are needed for our default 
	// CRUD operations triggered off models. Then load our model.
	//
	function _model_init()
	{
		$config = config_item('api_model_guess');
		$this->_model_methods = config_item('api_model_functions'); 
		
		if((empty($this->_model)) && (isset($config['segment']) && isset($config['postfix'])))
		{
			$this->_model = $this->uri->segment($config['segment']) . $config['postfix']; 
		}
		
		if(! empty($this->_model))
		{
			$this->load->model($this->_model);
			$this->_check_model_functions();
		} else 
		{
			$this->response(array('status' => 0, 'errors' => array('Must set a model name.')), 401);
		}
	}
	
	//
	// Checks to make sure all the required functions 
	// in our models for our generic API are set. 
	//
	function _check_model_functions()
	{
		foreach($this->_model_methods AS $key => $row)
		{
			if(! method_exists($this->{$this->_model}, $row))
			{
				show_error($this->_model . ' must have method ' . $row . '()');
			}
		}
	}
	
	//
	// There are certain arguments we can pass in at the url that
	// we use to call model based functions. We have to have the model
	// set for this function to do anythging.
	//
	function _query_init()
	{	
		// Grab data from model
		if($this->get('order'))
		{
		  $order = explode(':', $this->get('order'));
		  foreach($order AS $key => $row)
		  {
		  	$this->{$this->_model}->{$this->_model_methods['order']}($row . ' ' . $this->get('sort'));
		  }
		} 
		
		// Set limit & offset
		if($this->get('limit'))
		{
		  if($this->get('offset'))
		  {
		  	$this->{$this->_model}->{$this->_model_methods['limit']}($this->get('limit'), $this->get('offset'));
		  } else 
		  {
		  	$this->{$this->_model}->{$this->_model_methods['limit']}($this->get('limit'));				
		  }
		}
		
		// Set no extra
		if($this->get('noextra'))
		{
			$this->{$this->_model}->{$this->_model_methods['noextra']}();
		} 
		
		// Set select fields
		if($this->_select_fields)
		{
		  // Set the fields we want to return.
		  foreach($this->_select_fields AS $key => $row)
		  {
		  	$this->{$this->_model}->{$this->_model_methods['select']}($row);	
		  }
		}
	}
}

/* End File */
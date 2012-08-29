<?php
//
// Company: Cloudmanic Labs, LLC (http://www.cloudmanic.com)
// Author: Spicer Matthews <spicer@cloudmanic.com>
// Date: 8/29/2012
// Description: We have to make $_error_array so the API controller can access the 
//								error messages. 
//
class MY_Form_validation extends CI_Form_validation
{
	public $_error_array = array();
}

/* End File */
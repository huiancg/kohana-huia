<?php defined('SYSPATH') OR die('No direct script access.');

class Session_Exception extends Kohana_Session_Exception {

	public function __construct($message = "", array $variables = NULL, $code = 0, Exception $previous = NULL)
	{
		try {
			// clean cookie
			setcookie ('session', '', time() - 3600);
			session_destroy();
			session_write_close();
		} catch (Exception $e) {
			//
		}
		
		return parent::__construct($message, $variables, (int) $code, $previous);
	}

}
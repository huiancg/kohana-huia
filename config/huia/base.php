<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(
	'orm_deep' => 10,
	'cache_token' => sha1(Cookie::$salt . '.C4cH3c13An37!'),
	'autogen' => array(
		'database' => Kohana::$environment !== Kohana::PRODUCTION,
		'tables'   => FALSE,
		'models'   => Kohana::$environment === Kohana::DEVELOPMENT,
	),
	'ignore_models' => array('App', 'Auth_Role', 'Auth_User', 'Auth_User_Token', 'Log', 'Role', 'User', 'User_Token'),
);
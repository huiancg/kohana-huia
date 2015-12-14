<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(
	'default' => array(
		'account' => (Kohana::$environment === Kohana::PRODUCTION) ? 'UA-XXXXXXXX-X' : 'UA-XXXXXXXX-X',
		'href'    => 'google-analytics.com/ga.js',
	),
	'google-tag-manager' => array(
		'account' => (Kohana::$environment === Kohana::PRODUCTION) ? 'GTM-XXXXXX' : 'GTM-XXXXXX',
	),
);
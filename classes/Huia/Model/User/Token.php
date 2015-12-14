<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_Model_User_Token extends Model_Auth_User_Token {

	protected $_table_columns = array(
		'id' => array (
			'data_type' => 'int unsigned',
			'extra' => 'auto_increment',
			'key' => 'PRI',
			'display' => '11',
		),
		'user_id' => array (
			'data_type' => 'int unsigned',
			'key' => 'MUL',
			'display' => '11',
		),
		'user_agent' => array (
			'data_type' => 'varchar',
			'character_maximum_length' => '40',
		),
		'token' => array (
			'data_type' => 'varchar',
			'key' => 'UNI',
			'character_maximum_length' => '40',
		),
		'created' => array (
			'data_type' => 'int unsigned',
			'display' => '10',
		),
		'expires' => array (
			'data_type' => 'int unsigned',
			'key' => 'MUL',
			'display' => '10',
		),
	);

}
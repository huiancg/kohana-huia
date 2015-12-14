<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_Model_User extends Model_Auth_User {

  protected $_table_columns = array(
    'id' => array (
      'data_type' => 'int unsigned',
      'extra' => 'auto_increment',
      'key' => 'PRI',
      'display' => '11',
    ),
    'email' => array (
      'data_type' => 'varchar',
      'character_maximum_length' => '254',
    ),
    'username' => array (
      'data_type' => 'varchar',
      'character_maximum_length' => '32',
    ),
    'password' => array (
      'data_type' => 'varchar',
      'character_maximum_length' => '64',
    ),
    'logins' => array (
      'data_type' => 'int unsigned',
      'display' => '10',
    ),
    'last_login' => array (
      'data_type' => 'int unsigned',
      'is_nullable' => TRUE,
      'display' => '10',
    ),
  );
  
  /**
   * A user has many tokens and roles
   *
   * @var array Relationhips
   */
  protected $_has_many = array(
    'roles'       => array('model' => 'Role', 'through' => 'roles_users'),
  );

}
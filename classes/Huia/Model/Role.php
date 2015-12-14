<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_Model_Role extends Model_Auth_Role {

  protected $_table_columns = array(
    'id' => array (
      'data_type' => 'int unsigned',
      'extra' => 'auto_increment',
      'key' => 'PRI',
      'display' => '11',
    ),
    'name' => array (
      'data_type' => 'varchar',
      'character_maximum_length' => '32',
    ),
    'description' => array (
      'data_type' => 'varchar',
      'character_maximum_length' => '255',
    ),
  );

}
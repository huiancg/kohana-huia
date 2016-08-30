<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_Model_Log extends Model_App {

  protected $_table_columns = array(
    'id' => array (
      'data_type' => 'int unsigned',
      'extra' => 'auto_increment',
      'key' => 'PRI',
      'display' => '11',
    ),
    'level' => array (
      'data_type' => 'int',
      'display' => '11',
    ),
    'body' => array (
      'data_type' => 'varchar',
      'character_maximum_length' => '255',
    ),
    'file' => array (
      'data_type' => 'varchar',
      'character_maximum_length' => '255',
    ),
    'line' => array (
      'data_type' => 'int',
      'is_nullable' => TRUE,
      'display' => '10',
    ),
    'class' => array (
      'data_type' => 'varchar',
      'character_maximum_length' => '255',
    ),
    'function' => array (
      'data_type' => 'varchar',
      'is_nullable' => TRUE,
      'character_maximum_length' => '255',
    ),
    'additional' => array (
      'data_type' => 'text',
      'is_nullable' => TRUE,
    ),
    'uri' => array (
      'data_type' => 'varchar',
      'character_maximum_length' => '255',
    ),
    'agent' => array (
      'data_type' => 'varchar',
      'character_maximum_length' => '255',
    ),
    'ip' => array (
      'data_type' => 'varchar',
      'character_maximum_length' => '16',
    ),
    'referer' => array (
      'data_type' => 'varchar',
      'is_nullable' => TRUE,
      'character_maximum_length' => '255',
    ),
    'post' => array (
      'data_type' => 'text',
    ),
    'timestamp' => array (
      'data_type' => 'int',
      'display' => '10',
      'is_nullable' => TRUE,
    ),
    'time' => array (
      'data_type' => 'int unsigned',
      'display' => '10',
    ),
  );

  public function rules()
  {
    return array(
      'level' => array(
        array('numeric'),
        array('not_empty'),
        array('max_length', array(':value', 11)),
      ),
      'body' => array(
        array('not_empty'),
        array('max_length', array(':value', 255)),
      ),
      'file' => array(
        array('not_empty'),
        array('max_length', array(':value', 255)),
      ),
      'line' => array(
        array('numeric'),
        array('max_length', array(':value', 10)),
      ),
      'class' => array(
        array('not_empty'),
        array('max_length', array(':value', 255)),
      ),
      'function' => array(
        array('max_length', array(':value', 255)),
      ),
      'additional' => array(
        array('max_length', array(':value', 16777215)),
      ),
      'uri' => array(
        array('not_empty'),
        array('max_length', array(':value', 255)),
      ),
      'agent' => array(
        array('not_empty'),
        array('max_length', array(':value', 255)),
      ),
      'ip' => array(
        array('not_empty'),
        array('max_length', array(':value', 16)),
      ),
      'referer' => array(
        array('max_length', array(':value', 255)),
      ),
      'post' => array(
        array('not_empty'),
      ),
      'timestamp' => array(
        array('not_empty'),
      ),
      'time' => array(
        array('numeric'),
        array('not_empty'),
        array('max_length', array(':value', 10)),
      ),
    );
  }

  public function labels()
  {
    return array(
      'level' => __('Level'),
      'body' => __('Body'),
      'file' => __('File'),
      'line' => __('Line'),
      'class' => __('Class'),
      'function' => __('Function'),
      'additional' => __('Additional'),
      'uri' => __('Uri'),
      'agent' => __('Agent'),
      'ip' => __('Ip'),
      'referer' => __('Referer'),
      'post' => __('Post'),
      'timestamp' => __('Timestamp'),
      'time' => __('Time'),
    );
  }

}
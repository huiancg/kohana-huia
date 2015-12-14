<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_Analytics {
  
  /**
   * Default config to load.
   * 
   * @var string
   */
  public static $_default = 'default';

  /**
   * Render analytics by views
   * 
   * @param string $name
   * @return string
   */
  public static function render($name = NULL)
  { 
    if ($name === NULL)
    {
      $name = Analytics::$_default;
    }

    $config = Kohana::$config->load('huia/analytics.'.$name);

    $view = View::factory('huia/analytics/'.$name);
    $view->set($config);

    return $view->render();
  }

}

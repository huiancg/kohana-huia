<?php defined('SYSPATH') or die('No direct script access.');

class Huia_Controller_Fallback extends Controller_App {

  /**
   * Temp controller when has a route with a controller and action valid
   * 
   * @var Controller_App
   */
  protected $_controller_base = NULL;

  /**
   * @var bool
   * @see Controller_Fallback::has_controller_and_action()
   */
  protected $has_controller_and_action = NULL;

  /**
   * Check if exists a controller action to a route
   */
  protected function has_controller_and_action()
  {
    if ($this->has_controller_and_action === NULL)
    {
      $parts = explode('/', $this->request->uri(), 2);
      $controller = ucfirst($parts[0]);
      $class = 'Controller_'.ucfirst($parts[0]);
      $action = isset($parts[1]) ? $parts[1] : 'index';

      // controller and action exists
      $this->has_controller_and_action = (class_exists($class) AND (method_exists($class, 'action_' . $action)));
      if ($this->has_controller_and_action)
      {
        $this->_controller_base = new $class($this->request, $this->response);
        $this->_controller_base->action = $action;
        $this->_controller_base->controller = $controller;
      }
    }

    return $this->has_controller_and_action;
  }

  public function before()
  {
    $this->_controller = $this->controller = $this->request->param('_controller');
    $this->_action     = $this->action     = $this->request->param('_action');

    if ($this->has_controller_and_action())
    {
      $this->_controller_base->before();
      $this->_controller_base->{'action_' . $this->_controller_base->action}();
    }
    else
    {
      parent::before();
    }
  }

  public function action_index()
  {
    //
  }

  public function after()
  {
    if ($this->has_controller_and_action())
    {
      $this->_controller_base->after();
    }
    else
    {
	  parent::after();
    }
  }

} // End Controller_Fallback
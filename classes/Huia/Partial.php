<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_Partial {

  /**
   * Check if required route is current
   * 
   * @param string $route
   * @return bool
   */
  public static function is($route)
  {
    $route = ($route) ? $route : '/';
    return Request::current()->uri() === $route;
  }
  
  /**
   * Autorender partials in view
   * 
   * @param string $view View to render if Route is current
   * @param string OR NULL $route
   * @return string Rendered view if exists
   */
  public static function factory($view, $route = NULL)
  {
    $route = ($route !== NULL) ? $route : $view;
    
    if (Partial::is($route))
    {
      $vars = View::factory('template/index');
      
      if (Kohana::find_file('views/'.$vars->controller, $view))
      {
        return View::factory($vars->controller.'/'.$view)->render();
      }
      else if (Kohana::find_file('views/'.$view, 'index'))
      {
        return View::factory($view.'/index')->render();
      }

      return View::factory($view)->render();
    }

    return '';
  }

}

<?php defined('SYSPATH') or die('No direct script access.');

// DB by env
if (class_exists('Database'))
{
	Database::$default = Kohana::$environment;
}

// global create dir
function create_dir($path)
{
  if ( ! is_dir($path))
  {
    try
    {
      // Create the directory
      mkdir($path, 0755, TRUE);

      // Set permissions (must be manually set to fix umask issues)
      chmod($path, 0755);
    }
    catch (Exception $e)
    {
      if (Kohana::$environment === Kohana::PRODUCTION)
      {
        throw new Kohana_Exception('Could not create directory :dir',
          array(':dir' => Debug::path($path)));
      }
    }
  }
}

if ( ! function_exists('dd'))
{
  function dd()
  {
    echo call_user_func_array('Debug::vars', func_get_args());
    exit();
  }
}

// Auto base_url
if (Kohana::$base_url === '/')
{
    $cache = (Kohana::$caching) ? Kohana::cache('Kohana::$base_url') : FALSE;
    if ( ! $cache)
    {
        preg_match('/index.php[\/]*(.*)/', ( ! empty($_SERVER['SUPHP_URI'])) ? $_SERVER['SUPHP_URI'] : $_SERVER['PHP_SELF'], $match);
        $protocol = (Arr::get($_SERVER, 'HTTPS') === 'on') ? 's' : '';
        $base_url = preg_split("/\?/", str_ireplace(((isset($match[1])) ? trim($match[1], '/') : ''), '', urldecode(trim((( ! empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '/'), '/'))));
        $port = (Arr::get($_SERVER, 'SERVER_PORT', 80) != 80) ? ':'.Arr::get($_SERVER, 'SERVER_PORT') : ''; 
        $cache = trim(sprintf("http".$protocol."://%s/%s", (( ! empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : 'localhost'.$port), reset($base_url)),'/') . '/';
        unset($match, $base_url);
        
        if (Kohana::$caching)
        {
            Kohana::cache('Kohana::$base_url', $cache);
        }
    }
    Kohana::$base_url = $cache;
    unset($cache);
}

// Auto Routes
$routes = (Kohana::$caching) ? Kohana::cache('Huia::routes') : FALSE;

if ( ! $routes)
{
  $routes = array();

  foreach (Kohana::list_files('views', array(APPPATH)) as $key => $view)
  {
    $views = array_keys($view);
    
    foreach ($views as $view)
    {
      $view = str_replace(array('views'.DIRECTORY_SEPARATOR, EXT), '', $view);
      
      list($controller, $action) = explode(DIRECTORY_SEPARATOR, $view, 2);

      // ignore templates
      if ($controller === 'template' OR $controller === 'huia' OR $controller === 'manager')
      {
        continue;
      }
      
      $class = 'Controller_' . ucfirst($controller);

      // controller and action exists
      if (class_exists($class) AND (method_exists($class, 'action_' . $action)))
      {
        continue;
      }

      if ($controller === 'home')
      {
        if ($action === 'index')
        {
          $routes[] = array(
            'route' => '',
            'action' => $action,
            'controller' => $controller,
            '_priority' => 3,
          );
        }
        $routes[] = array(
          'route' => $action,
          'action' => $action,
          'controller' => $controller,
          '_priority' => 1,
        );
      }
      else if ($action === 'index')
      {
        $routes[] = array(
          'route' => $controller,
          'action' => $action,
          'controller' => $controller,
          '_priority' => 2,
        );
      }

      $routes[] = array(
        'route' => $controller . '(/' . $action . ')',
        'action' => $action,
        'controller' => $controller,
        '_priority' => 0,
      );
    }
  }
  
  $priority = array();
  foreach ($routes as $key => $row)
  {
    $priority[$key] = $row['_priority']; 
  }
  
  array_multisort($priority, SORT_DESC, $routes);

  if (Kohana::$caching)
  {
    Kohana::cache('Huia::routes', $routes);
  }
}

if ( ! empty($routes))
{
  foreach ($routes as $index => $route)
  {
    Route::set('controller_fallback_'.$index, $route['route'])
      ->defaults(array(
        'controller' => 'fallback',
        'action'     => 'index',
        '_controller' => $route['controller'],
        '_action'     => $route['action'],
      ));
  }
}

unset($routes);

// sitemap
Route::set('sitemap', 'sitemap(.xml)')
  ->defaults(array(
    'controller' => 'sitemap',
    'action'     => 'index',
  )); 
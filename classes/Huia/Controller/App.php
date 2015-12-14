<?php defined('SYSPATH') or die('No direct script access.');

abstract class Huia_Controller_App extends Controller {

  /**
   * @var  View  page template
   */
  protected $_cache_key = NULL;
  
  /**
   * @var string Template to render, inside template folder
   */
  public $template = 'index';
  
  /**
   * @var string Page title 
   */
  public $title = NULL;    
  
  /**
   * @var string Page description
   */
  public $description = NULL;
  
  /**
   * @var bool Render only content if ajax request if true
   */
  public $auto_ajax = TRUE;
  
  /**
   * @var bool Use auto cache to views
   */
  public $cached = TRUE;
  
  /**
   * @var bool If request probably is Ajax
   * @see $this->before
   */
  public $is_ajax = NULL;
  
  /**
   * @var bool If request probably is Crawler
   * @see $this->before
   */
  public $is_crawler = NULL;

  /**
   * @var string Defined controller
   * @see Controller_App::before()
   */
  public $controller = NULL;
  
  /**
   * @var string Defined action
   * @see Controller_App::before()
   */
  public $action = NULL;
  
  /**
   * @var bool If request probably is Mobile
   * @see $this->before
   */
  public $is_mobile = NULL;

  /**
   * @var bool If render template, defined in content view
   */
  public static $render_template = TRUE;
  
  /**
   * @var  View  page content
   */
  public $content = NULL;

  /**
   * Set the cache if Kohana is Caching and cached is true
   * 
   * @see cached
   * @see Controller_App::after()
   */
  protected function _cache_set()
  {
    if ( ! Kohana::$caching OR ! $this->cached OR $this->response->status() !== 200 OR $this->request->method() !== 'GET')
    {
      return;
    }

    $cache = array(
      'body' => $this->response->body(),
      'headers' => (array) $this->response->headers(),
    );
    Cache::instance()->set($this->_cache_key, $cache);
  }

  /**
   * Render cached responde, if defined
   * 
   * @see Controller_App::before()
   */
  protected function _cache()
  {
    if ( ! Kohana::$caching OR ! $this->cached)
    {
      return;
    }
    
    $this->_cache_key = 'Huia.Controller.' . Kohana::$base_url . gethostname() . $this->request->uri() . '.' . $this->is_ajax . '.' . $this->is_mobile();

    $cache = Cache::instance()->get($this->_cache_key);
    if ($cache)
    {
      header('From-Cache: 1');

      foreach ($cache['headers'] as $key => $value)
      {
        header($key.': '.$value);
      }
      
      exit($cache['body']);
    }
  }

  /**
   * Return the rendered view to crawler
   * 
   * @see Controller_App::before()
   */
  protected function _prerender()
  {
    if ($this->is_crawler())
    {
      $config = Kohana::$config->load('huia/prerender');

      if ( ! $config->enabled)
      {
        return;
      }

      $escaped_fragment = $this->request->query('_escaped_fragment_');
      $uri = ($escaped_fragment) ? $escaped_fragment : $this->request->uri() . URL::query(); 
      $current_url = URL::base(TRUE, TRUE) . $uri;
      
      $request = Request::factory($config->url.'/api/html');
      $request->query('Token', '1a251072038aa739E25bd40dbe7dfdaE');
      $request->query('Url', $current_url);

      $reponse = $request->execute();

      $body = @json_decode($reponse->body());

      $html = ($body AND isset($body->Html)) ? $body->Html : NULL;

      if ($html)
      {
        exit($html);
      }
    }
  }

  /**
   * Set variables to view by controller action
   * 
   * @see Controller_App::before()
   */
  protected function _meta()
  {
    $default = Kohana::$config->load('huia/meta.default');
    $current = Kohana::$config->load('huia/meta.'.$this->controller . '.' . $this->action);
	
    if ($current)
    {
      $default = Arr::merge($default, $current);
    }
    
    if ( ! $default OR empty($default))
    {
      return;
    }

    foreach ($default as $key => $value)
    {
      if ( ! isset($this->{$key}))
      {
        $this->{$key} = NULL;
      }
      if ( ! $this->{$key} OR is_array($this->{$key}))
      {
        $values = is_array($this->{$key}) ? $this->{$key} : array();
        $this->{$key} = __($value, $values);
      }
      View::set_global($key, $this->{$key});
    }
  }

  /**
   * Check if useragent is a Crawler
   * 
   * @return bool
   */
  public function is_crawler()
  {
    return preg_match('/(bot|crawl|slurp|spider|seeker|facebook)/i', Arr::get($_SERVER, 'HTTP_USER_AGENT', ''));
  }
  
  /**
   * Check if useragent is a Mobile
   * 
   * @return bool
   */
  public function is_mobile()
  {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", Request::$user_agent);
  }

  /**
   * Loads the template [View] object and content [View] object.
   */
  public function before()
  {
    $this->_prerender();

    $this->action || $this->action = $this->request->action();
    $this->controller || $this->controller = $this->request->controller();
    
    $this->action = strtolower($this->action);
    $this->controller = strtolower($this->controller);

    $this->is_ajax = $this->request->is_ajax();
    $this->is_crawler = $this->is_crawler();
	$this->is_mobile = $this->is_mobile();

    // do cache
    $this->_cache();

    // autogen database
    if (class_exists('Kohana_ORM'))
    {
      ORM_Autogen::autogen();
    }
    
    // Template View auto load
    if ($this->template !== NULL)
    {
      // Load the template
      $this->template = 'template/'.$this->template;
    }

    View::set_global('controller', $this->controller);
    View::set_global('action', $this->action);
    View::set_global('is_ajax', $this->is_ajax);
    View::set_global('is_crawler', $this->is_crawler);
    View::set_global('is_mobile', $this->is_mobile);	

    parent::before();
  }

  /**
   * Assigns the template [View] as the request response.
   */
  public function after()
  {
    if ($this->auto_ajax AND $this->is_ajax)
    {
      $this->template = NULL;
    }
    
    if ( ! $this->response->body())
    {
      $view = NULL;
      
      // Content View auto load
      $directory = ($this->request->directory() ? $this->request->directory().'/' : '');  
      $dir = str_replace('_', '/', strtolower($directory).strtolower($this->controller));
      $file = str_replace('_', '/', $this->action);
      
      // Set default template file
      if (Kohana::find_file('views/'.$dir, $file))
      {
        $this->content = View::factory($dir.'/'.$file);
      }
      
      if ($this->template AND $this->content)
      {
        $view = View::factory($this->template);
        $this->_meta();
      }
      
      if ($this->content)
      {
        if ($this->template === NULL)
        {
          $view = $this->content;
        }
        else
        {
          $view->content = $this->content;
        }
      }
      
      if ($view)
      {
        $view = $view->render();
        if (self::$render_template)
        {
          $this->response->body($view);
        }
        else if ($this->content)
        {
          $this->response->body($this->content);
        }
      }

    }

    $this->_cache_set();
    
    parent::after();
  }

  /**
   * @deprecated since version 2.0
   * @param midex $data
   */
  public function json($data)
  {
    $this->response->json($data);
  }

} // End App
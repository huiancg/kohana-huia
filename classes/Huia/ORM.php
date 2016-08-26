<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_ORM extends Kohana_ORM {

  protected static $_all_routes = [];

  protected static $_route_models = [];

  public static $_orm_deep = NULL;
  
  public static function orm_deep()
  {
    if (self::$_orm_deep === NULL)
	{
	  self::$_orm_deep = Kohana::$config->load('huia/base.orm_deep');
	}
	return self::$_orm_deep;
  }

  /**
   * 
   * @return ORM
   */
  protected static function get_table()
  {
    return str_replace('Model_', '', get_called_class());
  }
  
  /**
   * 
   * @return Database_Result
   */
  public static function all()
  {
    return ORM::factory(self::get_table())->find_all();
  }

  /**
   * List all models
   * 
   * @return array
   */
  public static function get_models()
  {
    $dir = 'classes'.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR;
    $models = self::_get_models(Kohana::list_files($dir));
    sort($models);
    $ignore_models = Kohana::$config->load('huia/base.ignore_models');

    foreach ($models as $index => $model)
    {
      if (preg_match('/^Base_/', $model) OR in_array($model, $ignore_models))
      {
        unset($models[$index]);
      }
    }

    return $models;
  }

  /**
   * Parse all models
   * 
   * @param  array $items
   * @param  array  $models
   * @return array
   */
  protected static function _get_models($items, $models = array())
  {
    $modules = array_values(Kohana::modules());
    if (is_array($items))
    {
      foreach ($items as $key => $value)
      {
        $models = self::_get_models($value, $models);
      }
    }
    else
    {
      $dir = 'classes'.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR;
      $items = str_replace($modules, '', $items);
      $dir = str_replace(array($dir, APPPATH, EXT), '', $items);
      $model = str_replace(DIRECTORY_SEPARATOR, '_', $dir);

      $reflection = new ReflectionClass('Model_'.$model);
      if ( ! $reflection->isAbstract())
      {
        $models[] = $model;
      }
    }
    return array_unique($models);
  }
  
  /**
   * Return formated of query request, with childs
   * 
   * @param string $table_name
   * @return array
   */
  public function all_as_array($table_name = NULL, $filter = NULL, $callback = NULL, $deep = 0)
  {
    if ($deep > self::orm_deep())
    {
      return 'Too Deep :(';
    }

    $deep++;
    
    if ($filter)
    {
      $filter($this);
    }

    $has_many = $this->has_many();
    $belongs_to = $this->belongs_to();

    // accept loaded item
    $models = ( ! $this->_loaded) ? $this->find_all() : array($this);
    
    $results = array();

    $int_values = [];
    $bool_values = [];
    $blob_values = [];
    foreach ($this->_table_columns as $key => $values)
    {
      $data_type = Arr::get($values, 'data_type');

      $is_int = in_array($data_type, ['int', 'tinyint']);
      $is_blob = ($data_type === 'blob');
      $is_bool = (((Arr::get($values, 'display', 0)) == 1) AND $is_int);
      if ($is_bool)
      {
        $bool_values[] = $key;
      }
      else if ($is_int)
      {
        $int_values[] = $key;
      }
      else if ($is_blob)
      {
        $blob_values[] = $key;
      }
    }

    foreach ($models as $item)
    {
      $result = $item->as_array();

      foreach ($result as $key => $value)
      {
        if (in_array($key, $int_values))
        {
          $result[$key] = (int) $result[$key];
        }
        else if (in_array($key, $bool_values))
        {
          $result[$key] = (bool) $result[$key];
        }
        else if (in_array($key, $blob_values))
        {
          $result[$key] = json_decode($result[$key], TRUE);
        }

        if (is_string($key) AND is_string($value) AND preg_match('/^(image|thumb|file|upload)/', $key))
        {
          $result[$key] = $item->get_url($key);
        }
        if ( ! isset($result['slug']))
        {
          $result['slug'] = $this->slug();
        }
        if ( ! isset($result['link']))
        {
          $result['link'] = $this->link();
        }
      }

      if ($callback)
      {
        $table = ($table_name) ? $table_name : $this->table_name();
        $result = $callback($table, $result);
        // ignore row
        if ($result === NULL)
        {
          continue;
        }
      }

      foreach ($belongs_to as $key => $values)
      {
        // schmittless
        if (ORM::get_model_name($key) === ORM::get_model_name($table_name))
        {
          continue;
        }
        $model_name = Arr::get($values, 'model');
        $foreign_key = Arr::get($values, 'foreign_key');
        $model = ORM::factory($model_name)->where('id', '=', $item->{$foreign_key});
        $result[$key] = $model->all_as_array($this->table_name(), $filter, $callback, $deep);
      }

      foreach ($has_many as $key => $values)
      {
        // schmittless
        if ($key === $table_name)
        {
          continue;
        }
        $result[$key] = $item->$key->all_as_array($this->table_name(), $filter, $callback, $deep);
      }

      $results[] = $result;
    }

    return $results;
  }
  
  /**
   * Get full url of database fields with fisical insumes
   * 
   * @param string $attr
   * @return string
   */
  public function get_url($attr = NULL)
  {
    return URL::site('public/upload/'. strtolower(self::get_table()) .'/'.$this->{$attr});
  }

  /**
   * Alias to get_url (deprecated)
   * 
   * @deprecated since version 2.0
   * @param string $attr
   * @return string
   */
  public function get_image_url($attr = 'image')
  {
    return $this->get_url($attr);
  }
  
  /**
   * Dynamic Finder:
   *    $orm->find_by_name('eduardo');
   *    $orm->find_all_by_name('eduardo');
   *    $orm->count_by_name('eduardo');
   *    $orm->find_all_by_name_or_email('eduardo');
   *    $orm->find_all_by_name_and_email('eduardo', 'du@kanema.com.br');
   *    $orm->find_all_by_name_and_email_and_is_active('eduardo', 'du@kanema.com.br', TRUE);
   *    $orm->find_all_by_name_and_email_and_is_active_limit('eduardo', 'du@kanema.com.br', TRUE, 5);
   *    $orm->first_by_name('eduardo');
   *    $orm->last_by_name('eduardo');
   * 
   * @param   string  $method Call methods divide by underscore
   * @param   array   $arguments  Parameters
   * @return  ORM OR void
   */
  protected function dynamic_finder($method, array $arguments)
  {
    if (preg_match('/^(?<find_type>(find|find_all|first|last|count))_by_/', $method, $matchs))
    {
      $find_type = $matchs['find_type'];
      $method = str_replace($matchs[0], '', $method);

      // Get the limit
      $limit = explode('_limit', $method);
      if (count($limit) === 2)
      {
        $this->limit(array_pop($arguments));
      }
      $method = $limit[0];

      // Get the first or last by primary key
      if ($find_type === 'first' OR $find_type === 'last')
      {
        $order = ($find_type === 'first') ? 'ASC' : 'DESC';
        $this->order_by($this->_table_name . '.' . $this->primary_key(), $order);
        $find_type = 'find';
      }
      else
      {
        // Get the order part
        $order_by = explode('_order_by_', $method);
        if (count($order_by) === 2)
        {
          $this->order_by($this->_table_name . '.' . $order_by[1]);
        }
        $method = $order_by[0];
      }

      // Get the and parts
      $and_parts = explode('_and_', $method);
      foreach ($and_parts as $and_part)
      {
        // Get the or parts
        $or_parts = explode('_or_', $and_part);
        if (count($or_parts) === 1)
        {
          $last_argument = (count($arguments) !== 0) ? array_shift($arguments) : $last_argument;
          $this->where($this->_object_name . '.' . $or_parts[0], '=', $last_argument);
        }
        else
        {
          foreach ($or_parts as $or_part)
          {
            $last_argument = (count($arguments) !== 0) ? array_shift($arguments) : $last_argument;
            $this->or_where($this->_object_name . '.' . $or_part, '=', $last_argument);
          }
        }
      }

      // Execute the query
      return $this->{$find_type}();
    }
  }

  public function __call($method, array $arguments)
  {
    $response = $this->dynamic_finder($method, $arguments);
    if ($response === NULL)
    {
      throw new Kohana_Exception('Call to undefined method :method()', array(':method' => $method));
    }
    return $response;
  }
  
  /**
   * Auto format model by table name
   * 
   * @param string $model
   * @return string
   */
  public static function get_model_name($model)
  {
    $prefix = Database::instance()->table_prefix();
    $model = ($prefix) ? str_replace($prefix, '', $prefix) : $model;
    $model = explode('_', Inflector::singular($model));
    $model = array_map('ucfirst', $model);
    return implode('_', $model);
  }

  /*******************************/

  /**
   * Check if a id existis in current model
   * 
   * @param  int $id
   * @return bool
   */
  public static function exists($id)
  {
    return self::factory(self::get_model_name(self::get_table()), $id)->loaded();
  }

  /**
   * Return all ids of current model
   * 
   * @return array
   */
  public static function ids()
  {
    $ids = self::all()->as_array(NULL, 'id');
    return array_map('intval', $ids);
  }

  /**
   * Return a select builder for current model
   * 
   * @return array
   */
  public static function select_options()
  {
    $models = self::all()->as_array('id', 'name');
    asort($models);
    return [ 0 => __('All') ] + $models;
  }

  /**
   * All valid routes of models
   * 
   * @return array
   */
  public static function all_routes()
  {
    return self::$_all_routes;
  }

  /**
   * Filter to apply in every dynamic route
   * 
   * @param  Route $route
   * @param  array $params
   * @param  Request $request
   * @return bool
   */
  public static function route_filter($route, $params, $request)
  {
    $object_name = strtolower(self::get_table());
    
    $key = 'Route::' . Request::initial()->uri();

    if (Kohana::$environment !== Kohana::DEVELOPMENT)
    {
      extract((array) Cache::instance()->get($key));
    }
    
    if ( ! isset($valid))
    {
      $model = self::find_by_slug(Arr::get($params, $object_name));
      $valid = (bool) ($model->id AND (Request::initial()->url() === $model->link()));


      $childs = [];

      foreach ($model->belongs_to() as $name => $values)
      {
        if ($object_name !== $name)
        {
          $childs[] = $model->{$name};
        }
      }

      Cache::instance()->set($key, compact('model', 'valid', 'childs'));
    }

    if ( ! $valid)
    {
      return FALSE;
    }

    $model->initial();

    foreach ($childs as $item)
    {
      $item->initial();
    }
  }

  /**
   * Return a regxp of valid routes in current model
   * 
   * @return string
   */
  public static function get_routes()
  {
    $models = self::all();
    $routes = [];
    foreach ($models as $cat)
    {
      $routes[] = $cat->slug();
    }

    uasort($routes, function($value) {
      $is_sub = (strpos($value, "/") === FALSE);
      return $is_sub;
    });

    return '('.join($routes, '|').')';
  }
  
  /**
   * Get route validations
   * 
   * @return string
   */
  public function get_route()
  {
    return Arr::get(Route::all(), $this->object_name());
  }

  /**
   * Dynamic criation of a link
   * 
   * @return string
   */
  public function link()
  {
    $route = $this->get_route();
    if ($route)
    {
      $params = [
      $this->object_name() => $this->slug(),
      ];

      foreach ($this->belongs_to() as $name => $values)
      {
        if ($name !== $this->object_name())
        {
          $model = $this->{$name};
          $params[$name] = $model->slug();
        }
      }

      return Route::url($this->object_name(), $params);
    }
    
    return URL::site($this->slug());
  }

  /**
   * Slug to current model
   * 
   * @return string
   */
  public function slug()
  {
    $slug = NULL;

    
    if (isset($this->name))
    {
      $slug = URL::slug($this->name);
    }
    else if (isset($this->title))
    {
      $slug = URL::slug($this->title);
    }

    if (isset($this->slug))
    {
      $slug = $this->slug;
    }

    return $slug;
  }

  /**
   * All valid slugs of a model
   * 
   * @param  array $models
   * @return array
   */
  public static function all_slugs($models = NULL)
  {
    $results = [];

    if ($models === NULL)
    {
      $models = self::all();
    }

    foreach ($models as $model)
    {
      $results[(int) $model->id] = $model->slug();
    }

    return $results;
  }

  /**
   * Find a slug
   * 
   * @param  string $slug
   * @return self
   */
  public static function find_by_slug($slug)
  {
    $slugs = self::all_slugs();
    return self::find_by_slug_with_slugs($slug, $slugs);
  }
  
  /**
   * [find_by_slug_with_slugs description]
   * 
   * @param  string $slug
   * @param  array $models
   * 
   * @return self
   */
  public static function find_by_slug_with_slugs($slug, $models)
  {
    return self::filter_slug($slug, $models);
  }

  /**
   * [filter_slug description]
   * 
   * @param  string $slug
   * @param  array $slugs
   * @return self
   */
  public static function filter_slug($slug, $slugs)
  {
    $slug = array_filter($slugs, function($item) use ($slug) {
      return $slug === $item;
    });

    $model = self::factory(self::get_table());

    if (count($slug) === 0)
    {
      return $model;
    }

    $keys = array_keys($slug);
    $id = array_shift($keys);

    if (isset($model->published))
    {
      $model->where('published', '=', TRUE);      
    }

    if (isset($model->date))
    {
      $model->where('date', '>=', DB::expr('NOW()'));      
    }

    return $model
        ->where('id', '=', (int) $id)
        ->find();
  }

  /**
   * Set initial model to current route
   * 
   * @return self
   */
  public function initial()
  {
    if ($this->id AND Arr::get(self::$_route_models, $this->object_name()) === NULL)
    {

      self::$_route_models[$this->object_name()] = $this;
      View::set_global($this->object_name(), $this);
      View::set_global($this->object_name() . '_id', (int) $this->id);
    }
    return Arr::get(self::$_route_models, $this->object_name());
  }

  /**
   * All routes
   * 
   * @return array
   */
  public static function route_models()
  {
    return self::$_route_models;
  }

  /**
   * All routes
   * 
   * @return array
   */
  public static function route_models_array()
  {
    $data = [];
    foreach (self::$_route_models as $model_name => $model)
    {
      $data[$model_name] = Arr::get($model->all_as_array(), 0);
    }
    return $data;
  }

  /**
   * Route model
   * 
   * @return self
   */
  public static function route_model()
  {
    $models = self::route_models();
    return array_shift($models);
  }

  /**
   * Find ou create a thumb with a image
   * 
   * @param  string $image
   * @param  int $width
   * @param  int $height
   * @param  int  $master
   * @param  boolean $crop
   * @param  integer $quality
   * 
   * @return string
   */
  public function get_thumb_url($image, $width = NULL, $height = NULL, $master = NULL, $crop = FALSE, $quality = 80)
  {
    $exploded = explode('.', $this->{$image}, 2);

    if (count($exploded) !== 2)
    {
      return;
    }

    $file_name = $exploded[0];
    $file_extention = $exploded[1];

    $file_thumb = 'public/upload/'. strtolower(self::get_table()) .'/' . $file_name . '_' . $width . 'x' . $height . 'thumb_' . $master . (($crop) ? '.crop' : '') . '.' . $file_extention;
    
    if ( ! @file_exists($file_thumb))
    {
      $file = 'public/upload/'. strtolower(self::get_table()) .'/' . $this->{$image};

      try
      {
        $image = Image::factory(DOCROOT . $file);
      }
      catch (Kohana_Exception $e)
      {
        return;
      }
      
      if ($crop)
      {
        $this->image_crop($image, $width, $height);
      }
      else
      {
        $image->resize($width, $height, $master);
      }
      
      $image->save(DOCROOT . $file_thumb, $quality);
    }

    return URL::site($file_thumb);
  }

  /**
   * [image_crop description]
   * 
   * @param  string $image
   * @param  int $width
   * @param  int $height
   * @return Image
   */
  protected function image_crop($image, $width, $height)
  {
    $offset_x = 0;
    $offset_y = 0;
    $has_greatest_height = ($image->width / $image->height) > ($width / $height);

    if ($has_greatest_height)
    {       
      $resized_width = ($height / $image->height) * $image->width;
      $offset_x = round(($resized_width - $width) / 2);
      $image->resize(NULL, $height);
    }       
    else    
    {       
      $resized_height = ($width / $image->width) * $image->height;
      $offset_y = round(($resized_height - $height) / 2);
      $image->resize($resized_width, $resized_height);
      $image->resize($width);
    }

    $image->crop($width, $height, $offset_x, $offset_y);
  }
  
}

<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_ORM extends Kohana_ORM {

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

    foreach ($models as $item)
    {
      $result = $item->as_array();

      foreach ($result as $key => $value)
      {
        if (is_string($key) AND is_string($value) AND preg_match('/^(image|thumb|file_|upload_)/', $key))
        {
          $result[$key] = $item->get_url($key);
        }

        if ( ! isset($result['slug']) AND is_string($key) AND is_string($value) AND preg_match('/^(name|title)/', $key))
        {
          $result['slug'] = Huia_URL::slug($value .' '. $result['id']);
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
  
}

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

  /**
   * Create the record depending on primary
   *
   * @chainable
   * @param  Validation $validation Validation object
   * @return \ORM
   */
  public function save_composite($validation = NULL)
  {
    $values = $this->object();
    unset($values['created_at'], $values['updated_at']);

    // create new instance
    $model = ORM::factory(substr(get_class($this), 6));
    
    $model->values($values);
    
    $model = $model->create($validation);

    $model->set_composite_id();

    $model->set_composite_actived();

    $this->save_composite_childs($model);

    return $model;
  }

  /**
   * Save all relations of a object
   */
  public function save_composite_childs($model)
  {
    foreach ($this->has_many() as $key => $values)
    {
      $foreign_key = Arr::get($values, 'foreign_key');
      $through = Arr::get($values, 'through');
      $items = $this->{$key}->find_all();
      $has_items = count($items);
      if ($has_items)
      {
        foreach ($items as $item)
        {
          if ($through)
          {
            $far_key = Arr::get($values, 'far_key');
            
            $data = array();
            $data[] = (int) $item->id;
            $data[] = (int) $model->id;
            
            $through_query = DB::insert($through, array($far_key, $foreign_key))->values($data);
            $through_query->execute();
          }
          else
          {
            $values = $item->object();
            $values[$foreign_key] = $model->id;
            unset($values['created_at'], $values['updated_at']);
            $item->clear()->values($values);
            $item->create();
          }
        }
      }
    }
  }
  
  /**
   * Get composite follow the pattern:
   *  Model primary key, underscore, Model object name
   *  Ex.: In a table products with id as pk
   *      id_product
   * 
   * @return string
   */
  public function composite_pk()
  {
    return $this->_primary_key . '_' . $this->_object_name;
  }
  
  /**
   * Set last composite with the composite pk as actived
   * @see composite_pk()
   */
  public function set_composite_draft_actived()
  {
    $this->find_last_composite()->set_composite_actived();
  }

  /**
   * Delete every draft has be created after current actived
   * @see find_actived_by_composite()
   */
  public function clean_draft()
  {
    $current = $this->find_actived_by_composite();
    $query = DB::delete($this->table_name())
                    ->where($this->composite_pk(), '=', $current->{$current->composite_pk()})
                    ->where($this->_primary_key, '>', $current->{$this->_primary_key})
                    ->execute();
  }

  /**
   * Set composite id by composite pk
   */
  public function set_composite_id()
  {
    DB::update($this->table_name())
                    ->set(array($this->composite_pk() => $this->id))
                    ->where('id', '=', $this->id)
                    ->where($this->composite_pk(), 'IS', NULL)
                    ->execute();
  }

  /**
   * Set the current model as actived by composite pk
   */
  public function set_composite_actived()
  {
    // remove current composite
    DB::update($this->table_name())
                    ->set(array('actived' => FALSE))
                    ->where('actived', '=', TRUE)
                    ->where($this->composite_pk(), '=', $this->{$this->composite_pk()})
                    ->execute();
    
    // set new composite
    DB::update($this->table_name())
                    ->set(array('actived' => TRUE))
                    ->where('actived', '=', FALSE)
                    ->where($this->_primary_key, '=', $this->{$this->_primary_key})
                    ->execute();
  }

  /**
   * Find the model with the composite pk
   * 
   * @return \ORM
   */
  public function find_actived_by_composite()
  {
    $model = clone $this;
    $model->clear();
    $model->where($this->composite_pk(), '=', $this->{$this->composite_pk()});
    $model->where('actived', '=', TRUE);
    return $model->find();
  }

  /**
   * Find current draft from a composite
   * 
   * @return \ORM
   */
  public function find_last_composite()
  {
    $model = clone $this;
    $model->clear();
    $model->where($this->composite_pk(), '=', $this->{$this->composite_pk()});
    $model->order_by($this->_primary_key, 'DESC');
    return $model->find();
  }

  /**
   * 
   * @return \ORM
   */
  public function filter_composite()
  {
    $this->where('actived', '=', TRUE);
    return $this;
  }

  /**
   * Verify if current model has most recent composite element
   * 
   * @see has_draft()
   * @return bool
   */
  public function has_draft()
  {
    $model = $this->find_actived_by_composite();
    $model->where($this->_primary_key, '>', $model->{$this->_primary_key});
    $model->where($this->composite_pk(), '=', $this->{$this->composite_pk()});
    return (bool) $model->count_all();
  }
  
}

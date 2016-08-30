<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_ORM_Autogen {

  protected static function check_upset_tables()
  {
    $models = Kohana::list_files('classes/model');
    $models = array_keys(Arr::flatten($models));

    $tables = array();
    $throughs = array();

    foreach ($models as $name)
    {
      $name = str_replace(array('classes/model' . DIRECTORY_SEPARATOR, EXT), '', $name);
      $name = str_replace(DIRECTORY_SEPARATOR, '_', $name);
      $reflection = new ReflectionClass('Model_'.$name);

      if (preg_match('/^Base_/', $name))
      {
        continue;
      }

      if ($reflection->isAbstract() OR $name === 'App' OR preg_match('/^(Auth_|User_Token)/', $name))
      {
        continue;
      }

      $object_name = ORM::get_model_name($name);

      $model = ORM::factory($name);

      $labels = $model->labels();
      $rules = $model->rules();
      
      $belongs_to = $model->belongs_to();
      $has_many = $model->has_many();
      $table_name = $model->table_name();
      $tables[$table_name] = $model->table_columns();

      // through
      foreach ($has_many as $key => $values)
      {
        $through = Arr::get($values, 'through');
        if ($through AND ! Arr::get($through, $tables) AND preg_match('/^'.$table_name.'_/', $through))
        {
          $farkey = ORM::get_model_name(str_replace($table_name.'_', '', $through));
          $tables[$through] = array(
            strtolower($farkey).'_id' => array(
              'type' => 'int',
              'data_type' => 'int',
              'is_nullable' => FALSE
            ),
            strtolower($object_name).'_id' => array(
              'type' => 'int',
              'data_type' => 'int',
              'is_nullable' => FALSE
            ),
          );

          $throughs[] = $through;
        }
      }
    }

    $queries = [];

    foreach ($tables as $name => $value)
    {
      $items = self::check_source($name, $value, $throughs);

      if ( ! empty($items))
      {
        $queries[$name] = $items;
      }
    }

    // dd($queries);

    return $queries;
  }

  public static function check_invalid_tables()
  {
    $list_tables = self::list_tables();
    
    $queries = [];

    foreach ($list_tables as $item)
    {
      $item_queries = [];
      
      if ( ! class_exists('Model_Base_'.$item['model']))
      {
        $query = 'DROP TABLE `' . $item['table_name'] . '`;';
        $item_queries[] = self::format_query($query, 'Drop table :table_name', [':table_name' => $item['table_name'],]);
      }

      if ( ! empty($item_queries))
      {
        $queries[$item['table_name']] = $item_queries;
      }
    }

    return $queries;

  }
  
  public static function generate_tables()
  {
    $queries = self::check_upset_tables();

    $queries += self::check_invalid_tables();

    if (self::valid_autogen_form())
    {
      if (Request::current()->post('autogen_ignore'))
      {
        Cookie::set('autogen_ignore', 1);
        return;
      }
      
      $valid_queries = self::filter_valid_queries($queries, Request::current()->post('queries'));

      foreach ($valid_queries as $query)
      {
        Database::instance()->query(NULL, $query);
      }
    }
    else
    {
      if ( ! empty($queries))
      {
        $token = Security::token(TRUE);
        exit(View::factory('huia/orm/autogen', compact('queries', 'token')));
      }
    }
  }

  protected static function filter_valid_queries($queries, $ids)
  {
    if ( ! $ids OR empty($ids))
    {
      return [];
    }

    $valid_queries = [];

    foreach ($queries as $table => $values)
    {
      foreach ($values as $items)
      {
        if (in_array(Arr::get($items, 'id', 0), $ids))
        {
          $valid_queries[] = $items['query'];
        }
      }
    }

    return $valid_queries;
  }

  protected static function valid_autogen_form()
  {
    return Request::current()->method() === Request::POST AND
           Request::current()->post('token') === Security::token();
  }

  protected static function format_field_query($field, $data)
  {
    $type = strtoupper(Arr::get($data, 'data_type'));

    $type_parts = explode(' ', $type);
    $type = $type_parts[0];

    $lenght = Arr::get($data, 'display', Arr::get($data, 'character_maximum_length'));
    
    // field name
    $item = '`'.$field.'` ';
    
    // type and size
    $item .= (($lenght ) ? $type.'('.$lenght.')' : $type) . ' ';
    
    // Extra type
    $item .= isset($type_parts[1]) ? strtoupper($type_parts[1]) . ' ' : '';

    // NOT NULL
    if ( ! Arr::get($data, 'is_nullable'))
    {
      $item .= 'NOT NULL ';
    }
    else
    {
      $item .= 'NULL ';
    }

    if (Arr::get($data, 'extra') === 'auto_increment')
    {
      $item .= 'AUTO_INCREMENT PRIMARY KEY ';
    }

    return trim($item);
  }

  protected static function format_query($query, $description, $values = [])
  {
    return [
      'id' => md5($query),
      'description' => __($description, $values),
      'query' => $query,
    ];
  }
  
  public static function check_source($table_name, $model_values, $ignore = array())
  {
    $queries = [];

    if ( ! self::table_exists($table_name))
    {
      $query = array();
      foreach ($model_values as $field => $data)
      {
        $query[] = self::format_field_query($field, $data);
      }

      $default_engine = 'ENGINE=InnoDB DEFAULT CHARSET=utf8;';
      $query = 'CREATE TABLE `' . $table_name . '` ('. join(', ', $query) .') '.$default_engine;

      $queries[] = self::format_query($query, 'Create table :table_name', [':table_name' => $table_name,]);

      //DB::query(NULL, $query)->execute();
    }
    else if ( ! in_array($table_name, $ignore))
    {
      // compare table
      $model = ORM::factory(ORM::get_model_name($table_name));
      $model->reload_columns(TRUE);
      $source_values = self::format_columns($model->table_columns());

      $add = array();
      $drop = array();
      $modify = array();

      foreach ($model_values as $column => $data)
      {
        if (isset($source_values[$column]))
        {
          foreach ($data as $key => $value)
          {
            if ( ! isset($source_values[$column]))
            {
              $add[] = $column;
            }
            else if ( ! isset($source_values[$column]))
            {
              $modify[] = $column;
            }
          }

          foreach ($data as $key => $value)
          {
            if ( ! isset($source_values[$column]))
            {
              $add[] = $column;
            }
            else if ( ! isset($source_values[$column][$key]))
            {
              $modify[] = $column;
            }
            else if ($source_values[$column][$key] !== $model_values[$column][$key])
            {
              $modify[] = $column;
            }
          }
        }
        else
        {
          $add[] = $column;
        }
      }

      foreach ($source_values as $column => $data)
      {
        if ( ! isset($model_values[$column]))
        {
          $drop[] = $column;
        }
        foreach ($data as $key => $value)
        {
          if ( ! isset($model_values[$column]))
          {
            $drop[] = $column;
          }
        }
      }

      if (empty($add) AND empty($modify) AND empty($drop))
      {
        return $queries;
      }

      // dd($table_name, $add, $modify, $drop);

      // ADD [COLUMN] col_name column_definition [FIRST | AFTER col_name]
      // MODIFY [COLUMN] col_name column_definition [FIRST | AFTER col_name]
      // DROP [COLUMN] col_name

      foreach (array_unique($drop) as $name)
      {
        $query = 'ALTER TABLE `'.$table_name.'` DROP `'.$name.'`;';

        $queries[] = self::format_query($query, 'Remove property :name', [':name' => $name,]);

        // DB::query(NULL, $query)->execute();
      }

      $keys = array_keys($model_values);
      
      foreach (array_unique($add) as $name)
      {
        $query = self::format_field_query($name, $model_values[$name]);
        
        $index = array_search($name, $keys);
        $index = ($index === 0) ? ' FIRST ' : ' AFTER ' . $keys[$index - 1];

        $query = 'ALTER TABLE `'.$table_name.'` ADD '.$query.$index.';';

        $queries[] = self::format_query($query, 'Add property :name', [':name' => $name,]);

        // DB::query(NULL, $query)->execute();
      }

      foreach (array_unique($modify) as $name)
      {
        $modify_query = self::format_field_query($name, $model_values[$name]);
        $query = 'ALTER TABLE `'.$table_name.'` MODIFY '.$modify_query.';';

        $queries[] = self::format_query($query, 'Modify :modify_query', [':modify_query' => $modify_query,]);
        // DB::query(NULL, $query)->execute();
      }

    }


    $primary_keys = self::primary_keys($model_values, $table_name);
    if ($primary_keys)
    {
      $queries[] = $primary_keys;
    }

    return $queries;
  }

  protected static function primary_keys($model_values, $table_name)
  {
    $primary_keys = array();
    $auto_increment = array();
    
    foreach ($model_values as $name => $values)
    {
      if (Arr::get($values, 'key') === 'PRI')
      {
        $primary_keys[] = $name;
      }
      if (Arr::get($values, 'extra') === 'auto_increment')
      {
        $auto_increment[] = $name;
      }
    }

    $query = array();
    if ( ! empty($auto_increment))
    {
      foreach ($auto_increment as $field)
      {
        $sql = self::format_field_query($field, $model_values[$field]);
        $query[] = $sql.' AUTO_INCREMENT';
      }
      foreach ($primary_keys as $field)
      {
        if (in_array($field, $auto_increment))
        {
          continue;
        }

        $query[] = self::format_field_query($field, $model_values[$field]);
      }
    }

    $result = '';
    
    if ( ! empty($primary_keys))
    {
      $add = 'ADD PRIMARY KEY ('. join(',', $primary_keys) .')';
      $query = ( ! empty($query)) ? ' MODIFY '.join(', MODIFY ', $query).',' : '';
      $execute = 'ALTER TABLE '.$table_name.' DROP PRIMARY KEY, '.$query.' '. $add;
    }

    return $result;
  }

  public static function autogen()
  { 
    if (Cookie::get('autogen_ignore'))
    {
      return;
    }

    $autogen = Kohana::$config->load('huia/base.autogen');

    if (Arr::get($autogen, 'database') AND ! self::db_exists())
    {
      $database = Kohana::$config->load('database.'.Kohana::$environment.'.connection.database');
      DB::query(NULL, 'CREATE DATABASE `'.$database.'`')->execute();

      // reset connection
      Database::instance()->disconnect();
    }

    // generate tables
    if (Arr::get($autogen, 'tables'))
    {
      self::generate_tables();
    }
    
    // generate models
    if (Arr::get($autogen, 'models') AND self::db_exists())
    {
      self::generate_models();
    }

    // clean models
    if (Arr::get($autogen, 'models_clean') AND self::db_exists())
    {
      self::clean_models();
    }
  }
  
  public static function table_exists($table_name)
  {
    try
    {
      DB::select()->from($table_name)->execute();
      return TRUE;
    }
    catch (Database_Exception $e)
    {
      return FALSE;
    }
  }
  
  public static function db_exists($name = NULL)
  {
    try
    {
      Database::instance($name)->query(Database::SELECT, 'SELECT 1');
      return TRUE;
    }
    catch (Database_Exception $e)
    {
      return FALSE;
    }
  }

  protected static function list_tables()
  {
    $items = [];

    foreach (Database::instance()->list_tables() as $name)
    {
      $_columns = array_keys(Database::instance()->list_columns($name));
      $ignore_models = Kohana::$config->load('huia/base.ignore_models');
      
      // ignore through
      if ( ! in_array('id', $_columns))
      {
        continue;
      }
      
      $model_name = ORM::get_model_name($name);

      // skip ignored
      if (in_array($model_name, $ignore_models))
      {
        continue;
      }
      
      $items[] = [
        'model' => $model_name,
        'table_name' => $name,
      ];
    }

    return $items;
  }
  
  public static function generate_models()
  {
    foreach (self::list_tables() as $item)
    {
      self::generate_model(Arr::get($item, 'model'));
    }
  }

  public static function currrent_models()
  {
    $models = Kohana::list_files('classes/model/base');
    
    $results = [];

    foreach (Arr::flatten($models) as $model => $location)
    {
      $file = str_replace(['classes/model/base'.DIRECTORY_SEPARATOR, EXT], '', $model);

      $table_name =  str_replace(DIRECTORY_SEPARATOR, '_', $file);
      
      $results[] = [
        'is_app' => (strpos($location, APPPATH) === 0),
        'file' => str_replace('classes'.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'Base', 'classes'.DIRECTORY_SEPARATOR.'Model', $location),
        'file_base' => $location,
        'model' => $table_name,
        'table_name' => strtolower(Inflector::plural($table_name)),
      ];
    };

    return $results;
  }
  
  public static function clean_models()
  {
    $currrent_models = self::currrent_models();

    foreach ($currrent_models as $item)
    {
      if (Arr::get($item, 'is_app') AND ( ! self::table_exists(Arr::get($item, 'table_name'))))
      {
        @unlink(Arr::get($item, 'file'));
        @unlink(Arr::get($item, 'file_base'));
      }
    };
  }
  
  public static function generate_model($model, $force = FALSE)
  {
    $class_name = 'Model_'.$model;

    $file = str_replace('_', DIRECTORY_SEPARATOR, $model);

    $base_dir = 'Base';
    
    $model_dir = APPPATH.'classes'.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR;
    $model_base = $model_dir . $base_dir . DIRECTORY_SEPARATOR;
    
    $file_name = Kohana::find_file('classes/model', $file);
    $file_base_name = Kohana::find_file('classes/model/base', $file);
    
    $table_name = strtolower(Inflector::plural($model));
    $table_id = strtolower($model) . '_id';
    
    $rules = array();
    $labels = array();
    $has_many = array();
    $belongs_to = array();
    $columns = Database::instance()->list_columns($table_name);

    foreach ($columns as $field)
    {
      $name = Arr::get($field, 'column_name');
      $key = Arr::get($field, 'key');
      $type = Arr::get($field, 'type');
      $low_name = str_replace('_id', '', $name);
      $maximum_length = Arr::get($field, 'character_maximum_length', Arr::get($field, 'display'));
      
      $title = ucfirst($name);
      
      // ignore id and _at$
      if ($name === 'id' OR preg_match('@_at$@', $name))
      {
        continue;
      }
      
      $field_rules = array();
      
      // unique
      if ($key === 'UNI')
      {
        $field_rules[] = "array(array(\$this, 'unique'), array(':field', ':value')),";
      }
      
      // unique
      if ($type === 'int')
      {
        $field_rules[] = "array('numeric'),";
      }

      // not null
      if (Arr::get($field, 'is_nullable') === FALSE)
      {
        $field_rules[] = "array('not_empty'),";
      }
      
      // max length
      if ($maximum_length)
      {
        $field_rules[] = "array('max_length', array(':value', $maximum_length)),";
      }
      
      // cpf
      if ($name === 'cpf')
      {
        $field_rules[] = "array('cpf', array(':value')),";
      }

      // email
      if (preg_match('@email@', $name))
      {
        $field_rules[] = "array('email', array(':value')),";
      }
      
      // rules
      if ( ! empty($field_rules))
      {
        $rules[$name] = $field_rules;
      }
      
      // labels
      if ( ! preg_match('@_id$@', $name))
      {
        $labels[$name] = $title;
      }
    
      // belongs to
      if (preg_match('@_id$@', $name))
      {
        $model_name = ORM::get_model_name($low_name);
        
        $belongs_to[$low_name] = "array(".
          "'model' => '" . $model_name . "'".
        "),";
        
        $labels[$low_name] = ucfirst($low_name);
      }
    }
    
    foreach (Database::instance()->list_tables() as $name)
    {
      $_columns = array_keys(Database::instance()->list_columns($name));
      
      // has many through
      if (preg_match('/(^'.$table_name.'_(.*)|(.*)_'.$table_name.'$)/', $name, $matchs))
      {
        $related = $matchs[count($matchs) - 1];
        $has_many[$related] = "array(".
          "'model' => '" . ORM::get_model_name($related) . "', ".
          "'through' => '" . $name . "'".
        "),";
        
        $labels[$related] = ucfirst($related);
      }
      
      // has many
      if (in_array('id', $_columns) AND in_array($table_id, $_columns))
      {
        $related = Inflector::singular($name);
        $field_name = Inflector::singular($table_name);
        
        $model_name = ORM::get_model_name($related);
        
        $has_many[str_replace($field_name.'_', '', $name)] = "array(".
          "'model' => '" . $model_name . "'".
        "),";
      }
    }

    $class_extends = 'Model_App';

    $columns = self::format_columns($columns);
    
    $full_class_name = 'Model_' . $base_dir . '_' . $model;
    
    $view = View::factory('huia/orm/base');
    $view->set('class_name', $full_class_name);
    $view->set('class_extends', $class_extends);
    $view->set('table_name', $class_name);
    $view->set('rules', $rules);
    $view->set('labels', $labels);
    $view->set('has_many', $has_many);
    $view->set('belongs_to', $belongs_to);
    $view->set('columns', $columns);
    
    $render_view = $view->render();
    
    $hash_current = ($file_base_name) ? preg_replace("/[^A-Za-z0-9]/", "", @file_get_contents($file_base_name)) : NULL;
    $hash_new = preg_replace("/[^A-Za-z0-9]/", "", $render_view);
	
    if ($hash_current !== $hash_new)
    {
      $file_base_name = $model_base . $file . EXT;
      create_dir(dirname($file_base_name));
      file_put_contents($file_base_name, $view->render());
    }
    
    // Create if dont exists
    if ( ! $file_name)
    {
      $view = View::factory('huia/orm');
      $view->set('class_name', $class_name);
      $view->set('class_extends', 'Model_' . $base_dir . '_' . $model);
      $file_name = $model_dir . $file . EXT;
      create_dir(dirname($file_name));
      file_put_contents($file_name, $view->render());
    }
  }

  protected static function format_columns($tables)
  {
    foreach ($tables as &$columns)
    {
      $columns = Arr::extract($columns, array('data_type', 'is_nullable', 'extra', 'key', 'character_maximum_length', 'display', 'default'));
      foreach ($columns as $key => $value)
      {
        if ( ! $value)
        {
          unset($columns[$key]);
        }
      }
    }
    return $tables;
  }

}

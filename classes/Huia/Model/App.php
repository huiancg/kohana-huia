<?php defined('SYSPATH') OR die('No direct access allowed.');

class Huia_Model_App extends ORM {

  /**
   * Model configuration, reload on wakeup?
   * @var bool
   */
  protected $_reload_on_wakeup = FALSE;
  
  protected $_created_column = array('column' => 'created_at', 'format' => 'Y-m-d H:i:s');
  protected $_updated_column = array('column' => 'updated_at', 'format' => 'Y-m-d H:i:s');
  
  /**
   * Throw a error in model
   * 
   * @param string $message
   * @param array $values
   * @param null $values
   * @throws ORM_Validation_Exception
   */
  public function error($message, $values = array())
  {
    throw new ORM_Validation_Exception(
        NULL, 
        Validation::factory(array()), 
        $message, 
        $values
      );
  }

}
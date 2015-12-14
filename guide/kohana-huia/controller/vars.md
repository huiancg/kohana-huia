Existem algumas variáveis internas que são definidas no `before` 

~~~
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
~~~
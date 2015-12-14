### Auto cache
Todas as requisições por metodo `GET` possuem suas respostas armazenadas em uma camada de cache para suavizar a utilização de recursos do servidor, caso dejese desativar essa persistência utilize o seguinte parâmetro:

~~~
  /**
   * @var bool Use auto cache to views
   */
  public $cached = TRUE;  
~~~

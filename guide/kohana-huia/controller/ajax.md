
### Renderizar subview por ajax
Para telas que possuem multiplas views na mesma tela, como sites com navegação vertical, onde o conteúdo é carregado dinamicamente, por padrão o conteúdo que é servido através de uma solicitação ajax é somente o conteúdo, por exemplo, se você tem um template em `views/template/index.php` e uma view da home em `views/home/index.php` ao acessar pelo navegador a rota `/home/index` será exibido o template com o conteúdo internamente, já se fizer a chamada por ajax o conteúdo que será entrega será comente do `views/home/index.php`.

Para desativar essa funcionalidade utilize o parâmetro:

~~~
  /**
   * @var bool Render only content if ajax request if true
   */
  public $auto_ajax = TRUE;
~~~
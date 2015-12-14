## Controller base

Existe uma Controller base com sistema de template e cache incluidos extenda, por padrão, a controller ´Controller_App´ como exemplo abaixo:
~~~
// application/classes/Controller/Home.php

class Controller_Home extends Controller_App {

  public function action_index()
  {
    //
  }

}
~~~

Para renderizar as views existe uma design patterns baseado no nome da controller concatenado com o nome da action, ou seja, na controller home, com action index (como exemplo acima) irá utilizar o template padrão `application/views/template/index.php` e utilizará uma variável chamada `$content` com o conteúdo que será buscado em `application/views/home/index.php`

~~~
// application/views/template/index.php

<html>
<body>
    <?php echo $content; ?>
</body>
</html>
~~~

~~~
// application/views/home/index.php

<h1>Hello</h1>
~~~

No navegador a resposta correspondente a essa solicitação será similar a essa:

~~~
// http://localhost:9000/

<html>
<body>
    <h1>Hello</h1>
</body>
</html>
~~~


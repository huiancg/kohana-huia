
### Rotas automáticas

Não existe a necessidade de criar controllers e actions caso não exista lógica nas mesmas. É feita uma leitura na pasta de views e é utilizada uma controller chamada Controller_Fallback que interpreta a view e conta a controller correspondente dinâmicamente, ou seja, basta criar uma extrutura similar a essa:

~~~
// application/views/home/index.php
<h1>Hello</h1>
~~~

Que a mesma será inseria automáticamente no template ao acessar a rota da home.
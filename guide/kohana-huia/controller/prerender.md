
### Prerender
Para garantir que o Google e outros crawlers leiam dados avançados utilizando javascript como Angular e outros é feita uma renderização via PhantomJS em uma aplicação ASP.NET e o retorno do que é executado é entregue ao crawler.
Por exemplo, caso tenha o seguinte html:

~~~
<script>
document.write('<h1>Hello</h1>');
</script>
~~~

O que será entregue ao crawler será:

~~~
<h1>Hello</h1>
~~~


Para desativar essa funcionalidade a configuração da aplicação:

~~~
// application/config/huia/prerender.php

return array(
    'enabled' => TRUE,
    'url' => 'http://phantomjs.dev/',
);

~~~

- Caso o ambiente da huia caia o processo de prerender é desativado, utilize o prerender somente se o projeto requirir e se possível coloque o prerender no ambiente do cliente.
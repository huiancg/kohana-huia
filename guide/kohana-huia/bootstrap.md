
### Bootstrap
O arquivo que concentra as configurações de execução e carregamentos do sistema possui a seguinte extrutura:

#### Configurações de inicialização do core
Existe um metodo que define o `base_url` caso não defina.
O cache somente é feito em produção, e o profile somente fora de produção. A pasta de staging é sempre a base do ambiente de phphomolog.

~~~
/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - integer  cache_life  lifetime, in seconds, of items cached              60
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 * - boolean  expose      set the X-Powered-By header                        FALSE
 */
Kohana::init(array(
	'index_file'   => '',
	'base_url'     => ((Kohana::$environment === Kohana::PRODUCTION) ? '/' : NULL),
	'caching'      => Kohana::$environment === Kohana::PRODUCTION,
	'profile'      => Kohana::$environment !== Kohana::PRODUCTION,
));

~~~

#### Modulos
Para tornar mais facil de identificar os modulos são carregados através de um arquivo de config que é alterado no install da aplicação ou posteriormente.
~~~
// include routes
include APPPATH.'config/modules'.EXT;
~~~

#### Logs
O log em produção por padrão é em banco de dados, e localmente em arquivos.
~~~
/**
 * Attach the file write to logging. Multiple writers are supported.
 */
if (Kohana::$environment !== Kohana::DEVELOPMENT)
{
	Kohana::$log->attach(new Log_Database());
}
else
{
	Kohana::$log->attach(new Log_File(APPPATH.'logs'));
}
~~~

#### Página de erro
A página de erro é somente exibida em produção:
~~~
// show errors only in production
if (Kohana::$environment === Kohana::PRODUCTION)
{
	Kohana_Exception::$error_view = 'error/default';
}
~~~

#### Rotas
São colocadas em arquivos dentro da pasta de config, caso necessite de outras bata criar uma pasta routes dentro de config e adicionar ao bootstrap.
~~~
// include routes
include APPPATH.'config/routes'.EXT;
~~~
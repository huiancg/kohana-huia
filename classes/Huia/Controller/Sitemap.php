<?php defined('SYSPATH') or die('No direct script access.');

class Huia_Controller_Sitemap extends Controller_App {

	public $urls = array();

	public function action_index()
	{
		$this->add_config();
		$this->add_routes();
	}

	public function add_config()
	{
		$config_routes = (array) Kohana::$config->load('huia/sitemap');

		foreach ($config_routes as $config_route)
		{
			if (Arr::get($config_route, 'link'))
			{
				$this->urls[] = [
					'loc' => Arr::get($config_route, 'link'),
				];
			}
			else
			{
				$this->add_model(Arr::get($config_route, 'name'), Arr::get($config_route, 'callback'));
			}
		}
	}

	protected function add_routes()
	{
		$routes = Route::all();
		foreach ($routes as $route)
		{
			$name = Route::name($route);
			$valid_name = !!! preg_match('/^(api|manager|huia)/', $name);
			$model_name = 'Model_' . ORM::get_model_name($name);
			
			if ($valid_name AND class_exists($model_name))
			{
				$this->add_model($name);
			}
		}
	}

	protected function add_model($model, $filter = NULL)
	{
		$items = Model_App::factory($model);

		$items->filter_sitemap();

		foreach ($items->find_all() as $item)
		{
			if ($filter AND ! $filter($item))
			{
				continue;
			}

			$lastmod = strtotime(($item->updated_at) ? $item->updated_at : $item->created_at);

			$this->urls[] = [
				'loc' => $item->link(),
				'lastmod' => date('Y-m-d', $lastmod),
			];
		}
	}

	public function clean_urls()
	{
		$links = [];
		$this->urls = array_filter($this->urls, function($url) use (&$links) {
			$loc = Arr::get($url, 'loc');
			
			if ( ! $loc OR in_array($loc, $links))
			{
				return FALSE;
			}
			
			$links[] = $loc;

			return TRUE;
		});
	}

	public function after()
	{
		$this->response->headers('Content-Type', 'text/xml; charset=' . Kohana::$charset);
		$view = View::factory('sitemap/index');
		$this->clean_urls();
		$view->urls = $this->urls;
		$this->response->body($view->render());
	}

}
<?php defined('SYSPATH') or die('No direct script access.');

class Huia_Controller_Cachecleaner extends Controller {

	public function before()
	{
		$token = $this->request->post('token');

		if ($this->not_valid_token($token))
		{
			throw new Kohana_Exception("Invalid Token: :token", [
					':token' => $token
				]);
		}
	}

	protected function token()
	{
		return Kohana::$config->load('huia/base.cache_token');
	}

	protected function not_valid_token($token)
	{
		return $this->request->action() !== 'token' AND
					 $token !== $this->token();
	}

	public function action_index()
	{
		$configs = array_keys((array) Kohana::$config->load('cache'));
		foreach ($configs as $name)
		{
			Cache::instance($name)->delete_all();
		}
		$this->response->json([
			'configs' => $configs,
		]);
	}

	public function action_token()
	{
		if (Auth::instance()->logged_in('admin'))
		{
			$this->response->json([
				'token' => $this->token(),
			]);
		}
		else
		{
			throw new Kohana_Exception("Admin login required.");
		}
	}

} // End Controller_Cachecleaner
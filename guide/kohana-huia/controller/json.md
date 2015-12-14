Para entregar alguma action como json existe um aprimoramento na classe de `Response` do Kohana que possibilita a entrega por json do código, para isso dentro da action utilize o metodo json:

~~~
  public function action_index()
  {
    $this->response->json(array('foo' => 'bar'));
  }

  // Content-type: application/json
  // {"foo":"bar"}
~~~
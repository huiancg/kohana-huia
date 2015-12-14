<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_Response extends Kohana_Response {

  /**
   * Set hearders and change the body to encoded json
   * 
   * @param mixed $data
   */
  public function json($data)
  {
    $this->headers('Content-Type', 'application/json; charset=utf-8');
    $this->body(json_encode($data));
  }

}
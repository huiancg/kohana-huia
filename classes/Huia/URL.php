<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_URL extends Kohana_URL {
  
  /**
   * Encode to slug
   * 
   * @param string $text
   * @return string Valid slug
   */
  public static function slug($text)
  {
    // replace non letter or digits by -
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
    
    // trim
    $text = trim($text, '-');

    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // lowercase
    $text = strtolower($text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    if (empty($text))
    {
      return 'n-a';
    }

    return $text;
  }
  
  /**
   * Get full url by current request
   * 
   * @see Request::current()
   * @return string
   */
  public static function current()
  {
    return URL::site(Request::current()->uri() . '/' . http_build_query(Request::current()->query()));
  }

}

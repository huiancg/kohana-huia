<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_Valid extends Kohana_Valid {

  /**
   * Check if valid CPF.
   * 
   * @param mixed $value
   * @return bool
   */
  public static function cpf($value)
  {
    $cpf = preg_replace("/[^0-9]/", "", $value);
    $one = 0;
    $two = 0;
    
    if (strlen($cpf.'') !== 11)
      return FALSE;

    for($i = 0, $x = 10; $i <= 8; $i++, $x--)
    {
      $one += $cpf[$i] * $x;
    }

    for($i = 0, $x = 11; $i <= 9; $i++, $x--)
    {
      if(str_repeat($i, 11) == $cpf)
        return FALSE;
      $two += $cpf[$i] * $x;
    }

    $one  = (($one%11) < 2) ? 0 : 11-($one%11);
    $two = (($two%11) < 2) ? 0 : 11-($two%11);

    if($one <> $cpf[9] || $two <> $cpf[10])
      return FALSE;

    return TRUE;
  }

  /**
   * Checks if a field is CEP.
   * 
   * @param midex $value
   * @return bool
   */
  public static function cep($value)
  {
    $cep = preg_replace("/[^0-9]/", "", $value);
    return (bool)(strlen($cep)===8);
  }
  
  /**
   * Checks if field has HTML tags.
   * 
   * @param midex $value
   * @return bool
   */
  public static function not_tags($value)
  {
    return ! preg_match('/<([^>]+)>/i', $value);
  }
  
  /**
   * Checks if a field has required Age.
   * 
   * @param midex $value
   * @return bool
   */
  public static function over_age($value, $compare = 18)
  {
    $today = date("d-m-Y");
    $birth = explode("-", $value); //separa a data de nascimento em array, utilizando o símbolo de - como separador
    $actual = explode("-", $today); //separa a data de hoje em array
    
    if($birth[0] < '1900')
    {
      return FALSE;
    }

    $birth = array_reverse($birth);//invert a ordem pq o formato é outro...
    
    if(count($actual) < 2 OR count($birth) < 2)
    {
      return FALSE;
    }

    $age = $actual[2] - $birth[2];

    if($birth[1] > $actual[1]) //verifica se o mês de nascimento é maior que o mês atual
    {
      $age--; //tira um ano, já que ele não fez aniversário ainda
    }
    elseif($birth[1] == $actual[1] && $birth[0] > $actual[0]) //verifica se o dia de hoje é maior que o dia do aniversário
    {
      $age--; //tira um ano se não fez aniversário ainda
    }
    
    return (bool)($age >= $compare);
  }

}

<?php 

namespace Mateodioev\Senku\Config;

class Utils
{
  /**
   */
  public static function removeLetters(string $str): string
  {
    return preg_replace('/[^0-9]/', '', $str ?? '');
  }
}

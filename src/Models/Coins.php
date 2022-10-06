<?php 

namespace Mateodioev\Senku\Models;

use Mateodioev\Request\Request;
use Mateodioev\Utils\fakeStdClass;

use function http_build_query, strtoupper;

class Coins
{
  private const API_URL = 'https://production.api.coindesk.com/v2/tb/price/ticker?';

  protected static function getUrl(string $coin): string
  {
    return self::API_URL . http_build_query(['assets' => strtoupper($coin)]);
  }


  public static function getInfo(string $coin): fakeStdClass
  {
    return Request::get(self::getUrl($coin))->Run()
      ->toJson(true)
      ->getBody();
  }
}


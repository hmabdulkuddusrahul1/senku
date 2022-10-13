<?php 

namespace Senku\Commands\Plugins;

use Mateodioev\Request\Request;
use Mateodioev\Utils\fakeStdClass;

use function http_build_query;

class Ip
{
  public const IPINFO = 'https://ipinfo.io/';
  public const IPDATA = 'https://api.ipdata.co/';

  /**
   * Get data from ipinfo.io
   * @see https://ipinfo.io/
   */
  public static function Info(string $ip): fakeStdClass
  {
    $req = Request::get(self::IPINFO)->addHeaders([
      'Accept: application/json',
      'Authorization: Bearer ' . $_ENV['IP2_TOKEN']
    ])->addOpt(CURLOPT_HTTPAUTH, CURLAUTH_BEARER);

    $res = $req->Run($ip);
    return $res->toJson(true)->getBody();
  }

  /**
   * Get data from ipdate.co
   * @see https://docs.ipdata.co/docs
   */
  public static function Ipdata(string $ip): fakeStdClass
  {
    $res = Request::get(self::IPDATA)
      ->Run($ip . '?' . http_build_query([ 'api-key' => $_ENV['IP1_TOKEN'] ]));

    return $res
      ->toJson(true)
      ->getBody();
  }
}

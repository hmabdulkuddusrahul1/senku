<?php 

namespace Senku\Commands\Plugins;

use Mateodioev\Request\Request;
use Mateodioev\Utils\Strings;

use function http_build_query;

class Wiki
{
  public const wikipediaAPI_URL = 'https://en.wikipedia.org/w/api.php';

  protected static function wikipediaGetUrl(string $query): string
  {
    return self::wikipediaAPI_URL . '?' . http_build_query([
      'action'   => 'query',
      'list'     => 'search',
      'srprop'   => 'snippet',
      'format'   => 'json',
      'srsearch' => $query
    ]);
  }

  public static function wikipediaSearch(string $query): array
  {
    $res = Request::GET(self::wikipediaGetUrl($query))
    ->addOpts([
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => 0
    ])->Run()
      ->toJson(true, true)
      ->getBody();

    $response = [];

    foreach ($res['query']['search'] as $r) {
      $response[] = [
        'title'   => $r['title'],
        'pageid'  => $r['pageid'],
        'snippet' => Strings::RemoveNoSpace(strip_tags($r['snippet'])),
        'article' => str_replace(' ', '_', $r['title']),
        'link'    => 'https://en.wikipedia.org/wiki/' . str_replace(' ', '_', $r['title']),
      ];
    }
    
    return $response;
  }
}

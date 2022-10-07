<?php 

namespace Senku\Commands\Plugins;

use Mateodioev\Request\{Request, RequestResponse};
use Mateodioev\Utils\fakeStdClass;

use function urlencode;

class GithubApi
{
  public const GITHUB_API = 'https://api.github.com/';
  protected static $instance;

  public $response;

  public static function getInstance(): GithubApi
  {
    if (!self::$instance instanceof GithubApi) {
      self::$instance = new GithubApi;
    }
    return self::$instance;
  }

  protected function send(string $endpoint): RequestResponse
  {
    $req = (new Request)->init(self::GITHUB_API)->addHeaders([
      'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
      'Host: api.github.com',
      'User-Agent:Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:85.0) Gecko/20100101 Firefox/85.0'
    ]);

    $this->response = $req->Run($endpoint);
    return $this->response;
  }

  public function getUser(string $username): fakeStdClass
  {
    return $this->send('users/' . $username)
      ->toJson(true)
      ->getBody();
  }

  public function getRepos(string $user): array
  {
    return $this->send('users/' . urlencode($user) . '/repos')
      ->toJson(true, true)
      ->getBody();
  }
}

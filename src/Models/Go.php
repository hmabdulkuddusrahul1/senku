<?php 

namespace Mateodioev\Senku\Models;

use Mateodioev\Request\Request;
use Mateodioev\Request\RequestResponse;

class Go
{
  public const URL = 'https://go.dev/_/compile';

  private Request $req;

  public string $code;

  public function __construct() {
    $this->req = Request::create(self::URL);
  }

  public function setCode(string $code): Go
  {
    $this->code = $code;
    return $this;
  }

  public function compile(): RequestResponse
  {
    $this->req->setMethod('POST');
    $this->req->addOpt(CURLOPT_POSTFIELDS, http_build_query([
      'version' => 2,
      'body' => $this->code,
      'withVet' => true
    ]));

    return $this->req->Run();
  }
}

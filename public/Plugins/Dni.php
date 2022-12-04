<?php

namespace Senku\Commands\Plugins;

use Mateodioev\Request\Request;
use Mateodioev\Senku\Exceptions\{DniErrorException, DniNotFoundException};

use function json_encode;

class Dni
{
  public const URL = 'https://api.municallao.gob.pe/';
  protected Request $req;
  public string $dni;

  public function __construct() {
    $this->req = new Request;
  }

  private function makeRequest()
  {
    $this->req->init(self::URL)
      ->addHeaders([
        'Accept: */*',
        'User-Agent:Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:85.0) Gecko/20100101 Firefox/85.0',
        'Content-Type: application/json',
      ])->setMethod('POST')
      ->addOpt(CURLOPT_POSTFIELDS, json_encode([
        'usuario' => 0,
        'app' => 33,
        'dni' => $this->dni,
        'strNumDocumento' => null
      ]));

    return $this->req->Run('pide/public/v1/reniec/dni/buscar');
  }

  public function getDni(string $dni): array
  {
    $this->dni = $dni;
    $res = $this->makeRequest();
    $header = $res->getHeaderResponse('X-RateLimit-Remaining');

    if ($header[0] == 0 || $header[0] == '0') {
      throw new DniErrorException('Se ha alcanzado el limite de peticiones', 429);
    }
    $body = (array) $res->toJson(true, true)->getBody()['consultarResponse']['return'] ?? null;

    if ($body === null) {
      throw new DniErrorException('Error al obtener el DNI', 500);
    } elseif ($body['coResultado'] != '0000') {
      throw new DniNotFoundException($body['deResultado']??'Not found', 404);
    } else {
      return $body['datosPersona'];
    }
  }
}

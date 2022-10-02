<?php 

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Buttons;
use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\Request\Request;
use Mateodioev\TgHandler\Commands;
use Mateodioev\Utils\fakeStdClass;

use function Mateodioev\Senku\{b, code, i, n, u, xQuit};

class Clima extends Message
{
  public const API_LINK = 'http://api.openweathermap.org/data/2.5/weather';

  public static function getInfo(string $city, string $lang = 'es'): fakeStdClass
  {
    $res = Request::get(self::API_LINK)->Run('?'.http_build_query([
      'q'     => $city,
      'appid' => $_ENV['OPEN_WEATHER_MAP'],
      'lang'  => $lang
    ]))->toJson(true)->getBody();

    return $res;
  }

  private function onEmpty(Methods $bot, Commands $cmd): fakeStdClass
  {
    return $bot->sendMessage(
      $cmd->getChatId(),
      b('PLease add a city to search') .
        n() . i('Format: ') . code('/' . $cmd->getCmdFromString($cmd->getText()) . ' City')
    );
  }

  private function notFound(string $city, Methods $bot, Commands $cmd): fakeStdClass
  {
    return $bot->sendMessage(
      $cmd->getChatId(),
      b('City ' . u($city) . ' not found')
    );
  }

  protected function getText(fakeStdClass $c): string
  {
    return b('El clima en ' . i($c->city) . ': ' . $c->weather->main) .
        n() . b('Status: ' . code($c->weather->description) .
        n() . 'Temperatura: ' . code($c->temp . '°C') .
        n() . 'Sensación: ' . code($c->sensacion . '°C') .
        n() . 'Humedad: ' . code($c->humedad) .
        n() . 'Pais: ') . i($c->country);
  }
  private function cityFound(fakeStdClass $c, Methods $bot, Commands $cmd): fakeStdClass
  {
    $txt = $this->getText($c);

    return $bot->sendMessage($cmd->getChatId(), $txt);
  }

  public function send(Methods $bot, Commands $cmd): fakeStdClass
  {
    $bot->sendAction($cmd->getChatId(), 'typing');
    $this->addReply($bot, $cmd);

    $city = $cmd->getPayload();

    if (strlen($city) < 2) {
      return $this->onEmpty($bot, $cmd);
    }

    $info = self::getInfo($city);

    // Not found
    if ($info->cod != 200) {
      return $this->notFound(xQuit($city), $bot, $cmd);
    } else {
      $this->addReloadAndReply($city, $bot, $cmd);
      $info = $this->parseInfo($info);
      return $this->cityFound($info, $bot, $cmd);
    }
  }

  protected function parseInfo(fakeStdClass $res): fakeStdClass
  {
    $toCelcius = function (float $k): float {
      return round($k - 273.15, 2);
    };

    $info = new fakeStdClass();
    $info->coord = $res->coord;
    $info->country = $res->sys->country;
    $info->city = $res->name;
    $info->weather = $res->weather[0];
    $info->temp = $toCelcius($res->main->temp);
    $info->sensacion = $toCelcius($res->main->feels_like);
    $info->humedad = $res->main->humidity . '%';

    return $info;
  }

  protected function addReloadAndReply(string $city, Methods $bot, Commands $cmd)
  {
    $this->addReply($bot, $cmd);
    $bot->AddOpt([
      'reply_markup' => (string) Buttons::create()
        ->addCeil(['text' => '🔄 Reload', 'callback_data' => 'clima ' . $city])
    ]);
  }
}

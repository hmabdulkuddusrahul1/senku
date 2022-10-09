<?php 

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\TgHandler\Commands;
use Mateodioev\Utils\fakeStdClass;

use function strlen, http_build_query;

class Write extends Message
{
  public const API_URL = 'https://apis.xditya.me/write';

  protected function getUrl(string $txt): string
  {
    if (strlen($txt) >= 4094) {
      $txt = substr($txt, 0, 4093);
    }

    return self::API_URL . '?' . http_build_query(['text' => $txt]);
  }

  public function send(Methods $bot, Commands $cmd): fakeStdClass
  {
    $this->addReply($bot, $cmd);
    $payload = $cmd->getPayload();

    if (empty($payload)) {
      return $this->sendDefaultEmpty($bot, $cmd, 'text to image', 'text..');
    }

    $res = $bot->sendPhoto($cmd->getChatId(), $this->getUrl($payload));

    if (!$res->ok) {
      $this->addReply($bot, $cmd);
      return $bot->sendMessage($cmd->getChatId(), '<b>Fail to send photo</b>');
    }
    return $res;
  }
}

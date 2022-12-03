<?php 

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\TgHandler\Commands;
use Mateodioev\Utils\fakeStdClass;
use Mateodioev\Utils\Numbers;

use function Mateodioev\Senku\{b, code, n};

class CardGen extends Message
{
  private function onEmpty(Methods $bot, Commands $cmd): fakeStdClass
  {
    $txt = b('Usage:')
      .n().code('/' . $cmd->getCmdFromString($cmd->getText()) . ' '.Numbers::genRandom(6).'XXXXXX|mm|yy|cvv');
    return $bot->sendMessage($cmd->getChatId(), $txt);
  }

  public function start(Methods $bot, Commands $cmd): fakeStdClass
  {
    $this->addReply($bot, $cmd);

    $payload = $cmd->getPayload();

    if (empty($payload) || strlen($payload) < 6) {
      return $this->onEmpty($bot, $cmd);
    }

    return $bot->sendMessage($cmd->getChatId(), 'Coming soon');
  }
}

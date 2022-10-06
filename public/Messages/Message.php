<?php 

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\Request\Request;
use Mateodioev\TgHandler\Commands;
use Mateodioev\Utils\fakeStdClass;

use function Mateodioev\Senku\{b, code, i, n};

class Message
{

  protected static function engineSearch(string $query, int $limit = 10): fakeStdClass
  {
    $query = http_build_query(['query' => $query, 'limit' => $limit]);

    return Request::get(static::getApiEndpoint())
      ->addOpts([CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => false])
      ->Run($query)
      ->toJson(true)
      ->getBody();
  }

  protected function getCommand(Commands $cmd): string
  {
    $type = $cmd->getType();

    return $type == 'message'
      ? $cmd->getCmdFromString($cmd->getText())
      : $cmd->getCmdOnCallback($cmd->getText());
  }

  protected function sendDefaultEmpty(Methods $bot, Commands $cmd, string $label, string $use = ''): fakeStdClass {
    $txt = i(b($label)) .
      n() . i('Example: ') . code('/' . $this->getCommand($cmd) . ' ' . $use);
    
    return $bot->sendMessage($cmd->getChatId(), $txt);
  }

  protected function addReply(Methods &$bot, Commands $cmd) {
    $bot->AddOpt([
      'reply_to_message_id' => $cmd->getMsgId(),
      'chat_id' => $cmd->getChatId(),
      'parse_mode' => 'html'
    ]);
  }
}

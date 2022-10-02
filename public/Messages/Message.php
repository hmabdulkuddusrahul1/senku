<?php 

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\TgHandler\Commands;

class Message
{
  protected function addReply(Methods &$bot, Commands $cmd) {
    $bot->AddOpt([
      'reply_to_message_id' => $cmd->getMsgId(),
      'chat_id' => $cmd->getChatId(),
      'parse_mode' => 'html'
    ]);
  }
}

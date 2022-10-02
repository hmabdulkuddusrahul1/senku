<?php

namespace Senku\Commands\Callbacks;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\TgHandler\Commands;
use Mateodioev\Utils\fakeStdClass;
use Senku\Commands\Messages\Clima;

class reloadClima extends Clima
{
  public function edit(Methods $bot, Commands $cmd): fakeStdClass
  {
    $city = $cmd->getPayload();
    
    $info = self::getInfo($city);
    $info = $this->parseInfo($info);
    
    $bot->answerCallbackQuery([
      'callback_query_id' => $cmd->getCallbackId(),
      'text' => 'updating info'
    ]);
    
    $this->addReloadAndReply($city, $bot, $cmd);
    return $bot->editMessageText($cmd->getChatId(), $cmd->getMsgId(), $this->getText($info));
  }
}

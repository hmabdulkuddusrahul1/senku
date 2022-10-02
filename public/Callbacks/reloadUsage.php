<?php 

namespace Senku\Commands\Callbacks;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\TgHandler\Commands;
use Mateodioev\Utils\fakeStdClass;
use Senku\Commands\Messages\Usage;

class reloadUsage extends Usage
{
  public function edit(Methods $bot, Commands $cmd): fakeStdClass
  {
    $this->addButtonAndReply($bot, $cmd);

    $bot->editMessageText($cmd->getChatId(), $cmd->getMsgId(), $this->createText());

    return $bot->answerCallbackQuery([
      'callback_query_id' => $cmd->getCallbackId(),
      'text' => 'Max usage: ' . self::convert(memory_get_peak_usage(true)),
      'show_alert' => true
    ]);
  }
}

<?php

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Buttons;
use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\TgHandler\Commands;
use Mateodioev\Utils\fakeStdClass;
use Senku\Commands\Messages\Message;

use function Mateodioev\Senku\{code, i, n};
use function memory_get_usage;

class Usage extends Message
{
  protected function addButtonAndReply(Methods &$bot, Commands $cmd)
  {
    $this->addReply($bot, $cmd);
    $bot->AddOpt([
      'reply_markup' => (string) Buttons::create()
      ->addCeil(['text' => '🔄 Reload', 'callback_data' => 'usage'])
    ]);
  }

  protected function createText(): string
  {
    return i('Usage: ') . code(self::convert(memory_get_usage())).n().
    i('Real usage: ') . code(self::convert(memory_get_usage())).n().
    i('Peak usage: ') . code(self::convert(memory_get_peak_usage(true)));
  }

  public static function convert($size): string
  {
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
  }

  public function getMemory(Methods $bot, Commands $cmd): fakeStdClass
  {
    $this->addButtonAndReply($bot, $cmd);

    return $bot->sendMessage(
      $cmd->getChatId(),
      $this->createText()
    );
  }

  public function onReload(Methods $bot, Commands $cmd)
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

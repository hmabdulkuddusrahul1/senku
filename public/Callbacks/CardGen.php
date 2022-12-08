<?php

namespace Senku\Commands\Callbacks;

use Mateodioev\Bots\Telegram\Buttons;
use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\Senku\Models\CardGen as ModelsCardGen;
use Mateodioev\TgHandler\Commands;

use function Mateodioev\Senku\code;

class CardGen
{
  private function unAuth(Methods $bot, Commands $cmd)
  {
    return $bot->answerCallbackQuery([
      'callback_query_id' => $cmd->getCallbackId(),
      'text'              => 'You are not authorized to use this command',
      'show_alert'        => true
    ]);
  }

  private function addButton(Methods &$bot, Commands $cmd, array $gen)
  {
    $bot->AddOpt([
      'parse_mode' => 'HTML',
      'reply_markup' => (string) Buttons::create()
        ->addCeil(['text' => '🔄 Gen again', 'callback_data' => 'gen ' . $cmd->getUserId() . ' ' . implode('|', $gen)])
        ->addCeil(['text' => 'ℹ️ Bin info', 'callback_data' => 'bin search ' . \substr($gen[0], 0, 6)])
    ]);
  }
  public function start(Methods $bot, Commands $cmd)
  {
    $payload = $cmd->getPayload();
    $components = explode(' ', $payload);

    if ($components[0] != $cmd->getUserId()) {
      return $this->unAuth($bot, $cmd);
    } unset($components[0]);

    $bot->answerCallbackQuery(['callback_query_id' => $cmd->getCallbackId(), 'text' => 'Generating...']);

    $components = explode ('|', $components[1]);

    $gen = new ModelsCardGen;
    $result = $gen->Gen(...$components);

    $cards = \array_map(function ($item) {
      return code($item);
    }, $result);

    $this->addButton($bot, $cmd, $components);
    $bot->editMessageText($cmd->getChatId(), $cmd->getMsgId(), implode(PHP_EOL, $cards));
  }
}

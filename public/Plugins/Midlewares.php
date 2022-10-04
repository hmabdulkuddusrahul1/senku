<?php 

namespace Senku\Commands\Plugins;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\Db\Connection;
use Mateodioev\TgHandler\Commands;
use Mateodioev\TgHandler\Runner;

class Midlewares
{
  private function getCommand(Commands $cmd): string
  {
    $text = $cmd->getText();

    return $cmd->getType() == 'message' 
      ? $cmd->getCmdFromString($text) 
      : $cmd->getCmdOnCallback($text);
  }

  /**
   * Manage messages, inline, callback updates
   */
  public function onUpdate(Commands $cmd)
  {
    if ($this->getCommand($cmd) != '') {
      Connection::PrepareFromEnv(__DIR__);
      Connection::addCharset();
    }
  }

  public function leaveAll(Runner &$run, Methods $bot, Commands $cmd)
  {
    echo 'Hola' . "\n";
    $run->log('Leaving chat: ' . $cmd->getChatId());
    return $bot->leaveChat(['chat_id' => $cmd->ChatId]);
  }
}

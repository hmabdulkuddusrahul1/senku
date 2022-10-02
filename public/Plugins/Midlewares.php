<?php 

namespace Senku\Commands\Plugins;

use Mateodioev\Db\Connection;
use Mateodioev\TgHandler\Commands;

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
}

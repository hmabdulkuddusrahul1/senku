<?php

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\TgHandler\Commands;

class Reload
{

  private string $service = 'senkubot';

  public function start(Methods $bot, Commands $cmd)
  {
    if ($cmd->getUserId() != $_ENV['ADMIN']) return 'Unauthorized';

    $msg = $bot->sendMessage($cmd->getChatId(), 'Reloading...');

    \shell_exec('systemctl restart ' . $this->service);

    $bot->editMessageText($cmd->getChatId(), $msg->result->message_id, 'Done');
  }
}

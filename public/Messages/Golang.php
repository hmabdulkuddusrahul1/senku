<?php 

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\Senku\Models\Go;
use Mateodioev\TgHandler\Commands;

class Golang extends Message
{
  private Go $go;

  public function __construct() {
    $this->go = new Go();
  }

  public function start(Methods $bot, Commands $cmd)
  {
    $this->addReply($bot, $cmd);

    if (empty($cmd->getPayload())) {
      return $this->sendDefaultEmpty($bot, $cmd, 'Go compiler', 'code');
    }

    $payload = $cmd->getPayload();
    
    $res = $this->go->setCode($payload)->compile()->toJson(true)->getBody();

    if ($res->Errors != '') {
      return $bot->sendMessage($cmd->getChatId(), '<code>' . $res->Errors . '</code>');
    }

    foreach ($res->Events as $i => $event) {
      if ($i == 0) {
        $r = $bot->sendMessage($cmd->getChatId(), '<code>' . $event->Message . '</code>');
      } else {
        usleep($event->Delay / 100);
        $r = $bot->AddOpt([
          'parse_mode' => 'HTML'
        ])->editMessageText($cmd->getChatId(), $r->result->message_id, '<code>' . $event->Message . '</code>');
      }
      echo $r . PHP_EOL;
    }

    return $r;
  }
}

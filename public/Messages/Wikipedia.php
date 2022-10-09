<?php 

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\{Methods, Buttons};
use Mateodioev\TgHandler\Commands;
use Senku\Commands\Plugins\Wiki;

use function Mateodioev\Senku\{a, b, i, n};

class Wikipedia extends Message
{
  protected function getText(array $res): string
  {
    return a($res['link'], $res['title']) . n() . i('~ ' . $res['snippet']);
  }

  protected function getButton(array $res): string
  {
    $b = Buttons::create();

    foreach ($res as $r) {
      $b->addCeil(['text' => $r['title'], 'url' => $r['link']])->AddLine();
    }

    return $b;
  }

  public function start(Methods $bot, Commands $cmd)
  {
    $this->addReply($bot, $cmd);
    $payload = $cmd->getPayload();

    if (empty($payload)) {
      return $this->sendDefaultEmpty($bot, $cmd, 'Wikipedia search', 'query...');
    }

    $wiki = Wiki::wikipediaSearch($payload);

    if (empty($wiki)) {
      return $bot->sendMessage($cmd->getChatId(), b('Not results found'));
    }
    $first = array_shift($wiki);

    return $bot->AddOpt([
      'reply_markup' => $this->getButton($wiki)
    ])->sendMessage($cmd->getChatId(), $this->getText($first));
  }
}

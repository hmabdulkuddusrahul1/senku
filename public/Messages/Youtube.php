<?php 

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\{Buttons, Methods};
use Mateodioev\TgHandler\Commands;
use Mateodioev\Utils\fakeStdClass;

use function Mateodioev\Senku\{a, b, i, n};
use function count, array_shift, urldecode;

class Youtube extends Message
{
  protected static function getApiEndpoint(): string
  {
    return $_ENV['SEARCH_ENGINE'] . 'youtube?';
  }

  public function start(Methods $bot, Commands $cmd): fakeStdClass
  {
    $this->addReply($bot, $cmd);
    $query = $cmd->getPayload();

    if (empty($query)) {
      return $this->sendDefaultEmpty($bot, $cmd, 'Youtube search', 'query');
    }

    $search = self::engineSearch($query);

    if (count($search->results) < 1) {
      return $bot->sendMessage($cmd->getChatId(), b(i('Not results found')));
    }

    $res = $search->results;
    $button = Buttons::create();
    
    $first = array_shift($res);

    $description = '';

    foreach ($first->descriptionSnippet as $t) {
      if (isset($t->bold) && $t->bold == true) {
        $description .= b($t->text);
      } else {
        $description .= $t->text;
      }
    }

    $txt = a(urldecode($first->link), $first->title) .
      n() . i('~ '.$description);

    foreach ($res as $i => $row) {
      $button->addCeil(['text' => $row->title, 'url' => urldecode($row->link)]);
      
      if ($i % 2 == 0) $button->AddLine();
    }

    return $bot->AddOpt(['reply_markup' => (string) $button])
      ->sendMessage($cmd->getChatId(), $txt);
  }
}

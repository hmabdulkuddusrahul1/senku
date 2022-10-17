<?php 

namespace Senku\Commands\Inline;

use Mateodioev\Bots\Telegram\{Methods, Inline, Buttons};
use Mateodioev\TgHandler\Commands;
use Senku\Commands\Messages\Wikipedia;
use Senku\Commands\Plugins\Wiki as PluginsWiki;

use function Mateodioev\Senku\{code, b, i, n};

class Wiki extends Wikipedia
{
  private Inline $in;

  private function onEmpty(Methods $bot, Commands $cmd)
  {
    $text = b('Please put your query') . n() . i('Example: ') . code('@' . $_ENV['BOT_USER'] . 'wiki hello world');
    $button = Buttons::create()->addCeil(['text' => 'Try yourself', 'switch_inline_query_current_chat' => 'wiki hello world']);

    return $bot->answerInlineQuery($cmd->getInlineId(), [
      $this->in->Article([
        'title'                 => 'Wikipedia search',
        'input_message_content' => $this->in->InputMessageContent($text),
        'reply_markup'          => $button->get()
      ])
    ]);
  }

  private function notFound(Methods $bot, Commands $cmd)
  {
    return $bot->answerInlineQuery($cmd->getInlineId(), [
      $this->in->Article([
        'title' => 'Not found results',
        'input_message_content' => $this->in->InputMessageContent('Not found results for your query')
      ])
    ]);
  }

  public function start(Methods $bot, Commands $cmd)
  {
    $bot->AddOpt(['cache_time' => 1]);
    $this->in = new Inline;

    $payload = $cmd->getPayload();
    if (empty($payload)) return $this->onEmpty($bot, $cmd);

    $wikis = PluginsWiki::wikipediaSearch($payload);
    if (empty($wikis)) return $this->notFound($bot, $cmd);

    $res = [];
    foreach ($wikis as $wiki) {
      $text = $this->getText($wiki);
      $res[] = $this->in->Article([
        'title'                 => $wiki['title'],
        'description'           => urldecode(substr($wiki['snippet'], 0, 50)),
        'input_message_content' => $this->in->InputMessageContent($text, 'HTML', [], false),
        'reply_markup'          => Buttons::create()->addCeil([
          'text' => 'Go to Wikipedia',
          'url' => $wiki['link']
        ])->get()
      ]);
    }

    return $bot->answerInlineQuery($cmd->getInlineId(), $res);
  }
}

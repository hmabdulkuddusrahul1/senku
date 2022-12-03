<?php 

namespace Senku\Commands\Inline;

use Mateodioev\Bots\Telegram\{Buttons, Inline, Methods};
use Mateodioev\Senku\Config\Utils;
use Mateodioev\TgHandler\Commands;
use Senku\Commands\Messages\BinInfo;
use Senku\Commands\Plugins\Bin as PluginsBin;

use function Mateodioev\Senku\{code, b, i, n};

class Bin extends BinInfo
{
  private Inline $in;

  protected function onEmpty(Methods $bot, Commands $cmd)
  {
    $text = b('Please put one bin') . n() . i('Example: ') . code('@' . $_ENV['BOT_USER'] . ' bin xxxxxx');
    $button = Buttons::create()->addCeil([
      'text' => 'Try yourself',
      'switch_inline_query_current_chat' => 'bin 510805'
    ]);

    return $bot->answerInlineQuery($cmd->getInlineId(), [
      $this->in->Article([
        'title' => 'Please put one bin ❌',
        'input_message_content' => $this->in->InputMessageContent($text),
        'reply_markup' => $button->get()
      ])
    ]);
  }

  protected function noMore(Methods $bot, Commands $cmd)
  {
    return $bot->answerInlineQuery($cmd->getInlineId(), [
      $this->in->Article([
        'title' => 'No more results found',
        'input_message_content' => $this->in->InputMessageContent(b('No more results found')),
      ])
    ]);
  }

  public function start(Methods $bot, Commands $cmd)
  {
    $bot->AddOpt(['cache_time' => 1]);
    $this->in = new Inline;
    $payload = Utils::removeLetters($cmd->getPayload());

    if (empty($payload) || strlen($payload) < 3) {
      return $this->onEmpty($bot, $cmd);
    }

    if ($cmd->getInlineOffset() == 'false') {
      return $this->noMore($bot, $cmd);
    }

    $offset = (int) $cmd->getInlineOffset() ?? 0;
    $res = [];

    foreach ((new PluginsBin)->getSimilar($payload, 50, $offset) as $fim) {
      $res[] = $this->in->Article([
        'title' => $fim->bin . ' (' . $fim->flag . ')',
        'description' => $fim->brand . ' - ' . $fim->type .  ' - ' . $fim->level,
        'input_message_content' => $this->in->InputMessageContent($this->parseInfo($fim))
      ]);
    }

    $total = count($res);
    if ($total == 0) {
      return $this->noMore($bot, $cmd);
    }

    $nexOffset = $total < 50 ? 'false' : $offset + 50;

    return $bot->AddOpt([
      'next_offset' => $nexOffset
    ])->answerInlineQuery($cmd->getInlineId(), $res);
  }
}

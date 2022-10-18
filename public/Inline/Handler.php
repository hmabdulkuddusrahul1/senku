<?php 

namespace Senku\Commands\Inline;

use Mateodioev\Bots\Telegram\{Methods, Inline, Buttons};
use Mateodioev\TgHandler\Commands;

use function array_keys, in_array;
use function Mateodioev\Senku\b;

class Handler
{
  protected function getCommands(Commands $cmd): array
  {
    $cmds = $cmd->getCommands()['inline'] ?? [];
    return array_keys($cmds);
  }

  protected function createResults(array $cmds): array
  {
    $inline = new Inline;

    $res = [];
    foreach ($cmds as $cmd) {
      $res[] = $inline->Article([
        'title' => 'Call ' . $cmd,
        'input_message_content' => $inline->InputMessageContent(b($cmd . ' command')),
        'reply_markup' => Buttons::create()->addCeil([
          'text' => 'Try yourself',
          'switch_inline_query_current_chat' => $cmd . ' your query'
        ])->get()
      ]);
    }
    return $res;
  }

  public function start(Methods $bot, Commands $cmd)
  {
    $bot->AddOpt(['cache_time' => 1]);
    $payload = $cmd->getCmdOnCallback($cmd->getText());
    $cmds = $this->getCommands($cmd);

    if (in_array($payload, $cmds) === false) {
      return $bot->answerInlineQuery(
        $cmd->getInlineId(),
        $this->createResults($cmds)
      );
    }
  }
}

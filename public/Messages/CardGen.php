<?php 

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\{Methods, Buttons};
use Mateodioev\Senku\Models\CardGen as ModelsCardGen;
use Mateodioev\TgHandler\Commands;
use Mateodioev\Utils\{fakeStdClass, Numbers};
use Senku\Commands\Plugins\Bin;

use function Mateodioev\Senku\{b, code, i, n};

class CardGen extends Message
{
  private function onEmpty(Methods $bot, Commands $cmd): fakeStdClass
  {
    $txt = b('Usage:')
      .n().code('/' . $cmd->getCmdFromString($cmd->getText()) . ' '.\mt_rand(3,6).Numbers::genRandom(5).'XXXXXX|mm|yy|cvv');
    return $bot->sendMessage($cmd->getChatId(), $txt);
  }

  protected function validatePrefix(int $prefix)
  {
    if (\in_array($prefix, [3,4,5,6]) === false) {
      throw new \UnexpectedValueException('Card not supported', 400);
    }
    return true;
  }
  public function start(Methods $bot, Commands $cmd): fakeStdClass
  {
    $this->addReply($bot, $cmd);

    $payload = $cmd->getPayload();

    if (empty($payload) || strlen(preg_replace('/[^0-9]/', '', $payload)) < 6) {
      return $this->onEmpty($bot, $cmd);
    }

    $toGen = ModelsCardGen::extract($payload);
    if ($toGen === null) {
      return $this->onEmpty($bot, $cmd);
    }

    try {
      $card = $toGen[0];
      $this->validatePrefix($card[0]);
      $gen = $this->gen($toGen);
      
      (new Bin)->search(\substr($card, 0, 6));
    } catch (\UnexpectedValueException $e) {
      return $bot->sendMessage($cmd->getChatId(), i('Invalid input ⚠️').n().'Error: ' . b($e->getMessage()));
    } catch (\RuntimeException $e) {
      return $bot->sendMessage($cmd->getChatId(), i(b('Use another extra')).n().i($e->getMessage()));
    }

    $result = \array_map(function ($item) {
      return code($item);
    }, $gen[0]);

    $bot->AddOpt([
      'reply_markup' => (string) Buttons::create()
        ->addCeil(['text' => '🔄 Gen again', 'callback_data' => 'gen ' . $cmd->getUserId() . ' ' . implode('|', $gen[1])])
        ->addCeil(['text' => 'ℹ️ Bin info', 'callback_data' => 'bin search ' . \substr($gen[1][0], 0, 6)])
    ]);
    return $bot->sendMessage($cmd->getChatId(), \implode(n(), $result));
  }

  protected function gen(array $genInput): array
  {
    $gen = new ModelsCardGen();

    while (\count($genInput) < 4) {
      $genInput[] = 'rnd';
    }

    if (Numbers::isNumber($genInput[2]) && \strlen($genInput[2]) === 2) {
      $genInput[2] = '20' . $genInput[2];
    }

    return [$gen->Gen(...$genInput), $genInput];
  }
}

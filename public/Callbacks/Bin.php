<?php 

namespace Senku\Commands\Callbacks;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\TgHandler\Commands;
use Mateodioev\Utils\fakeStdClass;
use Mateodioev\Utils\Numbers;
use Senku\Commands\Messages\BinInfo;

class Bin extends BinInfo
{
  public function start(Methods $bot, Commands $cmd)
  {
    $payload = explode(' ', $cmd->getPayload());

    if ($payload[0] == 'gen') {
      return $this->gen($bot, $cmd, $payload[1]);
    }
  }

  private function gen(Methods $bot, Commands $cmd, int $type): fakeStdClass
  {
    $bin = (int) $type . Numbers::genRandom(5);
    
    $fim = $this->getInstance()->search($bin);

    if ($fim === false) {
      return $bot->answerCallbackQuery([
        'callback_query_id' => $cmd->getCallbackId(),
        'text'              => 'Try again, bin generate is invalid' . PHP_EOL . 'Bin: ' . $bin,
        'show_alert'        => true
      ]);
    }
    $this->addReply($bot, $cmd);
    return $bot->AddOpt([
      'reply_markup' => (string) $this->getGBinButtons()
    ])->editMessageText($cmd->getChatId(), $cmd->getMsgId(), $this->parseInfo($fim));
  }
}

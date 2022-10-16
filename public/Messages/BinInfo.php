<?php 

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Buttons;
use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\TgHandler\Commands;
use Mateodioev\Utils\fakeStdClass;
use Senku\Commands\Plugins\Bin;

use function Mateodioev\Senku\{b, i, n, u};

class BinInfo extends Message
{
  private $bin;

  protected function getInstance(): Bin
  {
    if (!$this->bin instanceof Bin) {
      $this->bin = new Bin;
    }

    return $this->bin;
  }

  private function notFound(string $bin, Methods $bot, Commands $cmd): fakeStdClass
  {
    return $bot->sendMessage($cmd->getChatId(), b('Bin ' . i($bin) . ' not found'));
  }

  protected function parseInfo(fakeStdClass $fim): string
  {
    return b('Valid bin: ') . u($fim->bin) .
      n() . i(b('Country: ') . $fim->country_name . ' ' . $fim->flag) .
      n() . i(b('Datas: ') . $fim->brand . ' - ' . $fim->type .  ' - ' . $fim->level) .
      n() . i(b('Bank: ') . $fim->bank_name);
  }

  public function send(Methods $bot, Commands $cmd): fakeStdClass
  {
    $this->addReply($bot, $cmd);
    $payload = $cmd->getPayload();

    if (strlen($payload) < 6) {
      return $this->sendDefaultEmpty($bot, $cmd, 'Bin search', '5108005');
    }

    $fim = $this->getInstance()->search($payload);

    if ($fim === false) {
      return $this->notFound($payload, $bot, $cmd);
    }

    return $bot->sendMessage($cmd->getChatId(), $this->parseInfo($fim));
  }

  public function gBin(Methods $bot, Commands $cmd)
  {
    $this->addReply($bot, $cmd);

    return $bot->AddOpt([
      'reply_markup' => (string) $this->getGBinButtons()
    ])->sendMessage($cmd->getChatId(), 'Please select a type of bin to generate');
  }

  protected function getGBinButtons(): Buttons
  {
    return Buttons::create(others_params: ['resize_keyboard' => true])
      ->addCeil(['text' => 'Amex', 'callback_data' => 'bin gen 3'])
      ->addCeil(['text' => 'Visa', 'callback_data' => 'bin gen 4'])->AddLine()
      ->addCeil(['text' => 'Mastercard', 'callback_data' => 'bin gen 5'])
      ->addCeil(['text' => 'Discover', 'callback_data' => 'bin gen 6']);
  }
}

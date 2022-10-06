<?php 

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Buttons;
use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\Senku\Models\Coins;
use Mateodioev\TgHandler\Commands;
use Mateodioev\Utils\fakeStdClass;

use function Mateodioev\Senku\b;
use function Mateodioev\Senku\code;
use function Mateodioev\Senku\i;
use function Mateodioev\Senku\n;
use function Mateodioev\Senku\u;
use function strtoupper;

class Crypto extends Message
{

  protected function getButton(string $coin): string
  {
    return Buttons::create()->addCeil(['text' => '🔄 Reload', 'callback_data' => 'coin ' . $coin]);
  }

  protected function getText($info): string
  {
    $price = $info->ohlc;
    $change = round($info->change->percent, 3);

    return i(b('Current price of ' . u($info->name))) .
    n() . i('Price') .
    n() . "\t- " . i("Open:\t") . code('$' . round($price->o, 3)) .
    n() . "\t- " . i("High:\t") . code('$' . round($price->h, 3)) .
    n() . "\t- " . i("Low:\t") . code('$' . round($price->l, 3)) .
    n() . "\t- " . i("Close:\t") . code('$' . round($price->c, 3)) .
    n() . i('Change: ') . code($change . '%');
  }

  public function start(Methods $bot, Commands $cmd)
  {
    $this->addReply($bot, $cmd);
    $payload = strtoupper($cmd->getPayload());

    if (empty($payload)) {
      return $this->sendDefaultEmpty($bot, $cmd, 'Get Crypto prices', 'btc');
    }

    $info = Coins::getInfo($payload);

    if ($info->statusCode != 200 || $info->message != 'OK') {
      return $bot->sendMessage($cmd->getChatId(), 'Unknown cryptocurrency (' . $payload . ')');
    }

    $info = $info->data->$payload;
    
    return $bot
      ->AddOpt(['reply_markup' => $this->getButton($payload)])
      ->sendMessage($cmd->getChatId(), $this->getText($info));
  }
}

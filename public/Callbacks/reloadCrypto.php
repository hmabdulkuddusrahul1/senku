<?php 

namespace Senku\Commands\Callbacks;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\Senku\Models\Coins;
use Senku\Commands\Messages\Crypto;
use Mateodioev\TgHandler\Commands;
use Mateodioev\Utils\fakeStdClass;

class reloadCrypto extends Crypto
{
  public function edit(Methods $bot, Commands $cmd): fakeStdClass
  {
    $coin = $cmd->getPayload();
    
    $info = Coins::getInfo($coin);

    if ($info->statusCode != 200 || $info->message != 'OK') {
      return $bot->answerCallbackQuery([
        'callback_query_id' => $cmd->getCallbackId(),
        'text' => 'Fail to update info',
        'show_alert' => true
      ]);
    }
    
    $bot->answerCallbackQuery(['callback_query_id' => $cmd->getCallbackId(), 'text' => 'updating info']);
    
    $this->addReply($bot, $cmd);
    return $bot->AddOpt([
      'reply_markup' => $this->getButton($coin)
    ])->editMessageText($cmd->getChatId(), $cmd->getMsgId(), $this->getText($info->data->$coin));
  }
}

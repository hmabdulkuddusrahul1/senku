<?php 

namespace Senku\Commands\Callbacks;

use Mateodioev\Bots\Telegram\{Buttons, Methods};
use Mateodioev\TgHandler\Commands;
use Mateodioev\TranslateException;
use Senku\Commands\Messages\Traduct;

use function explode, unlink;

class TranslateService extends Traduct
{
  protected function auth(Methods $bot, Commands $cmd)
  {
    $data = explode(' ', $cmd->getPayload());

    if ($data[0] != $cmd->getUserId()) {
      $bot->answerCallbackQuery(['callback_query_id' => $cmd->getCallbackId(), 'text' => 'Use your own command!!', 'show_alert' => true ]);
      return false;
    }
    return true;
  }

  public function alternate(Methods $bot, Commands $cmd)
  {
    if (!$this->auth($bot, $cmd)) return 'not authorized to call translated command';
    $bot->AddOpt(['callback_query_id' => $cmd->getCallbackId(), 'show_alert' => true]);

    $payload = explode(' ', $cmd->getPayload());

    $this->read($payload[1]);
    $oldService = $this->service;
    $this->service = $this->service == 'google' ? 'yandex' : 'google';

    try {
      $bot->AddOpt([
        'show_alert' => false
      ])->answerCallbackQuery(['text' => 'Translating, please wait...']);
      $tr = $this->translate($_ENV['YANDEX_TR']);

      if ($tr->error) return $bot->answerCallbackQuery(['text' => $tr->error_msg]);
    } catch (TranslateException $e) {
      return $bot->answerCallbackQuery(['text' => $e->getMessage()]);
    }

    $text = $this->getText($tr, $this->service);
    $this->save($payload[1]);
    $this->addReply($bot, $cmd);
    return $bot->AddOpt([
      'reply_markup' => (string) Buttons::create()->addCeil([
        'text' => 'Use ' . $oldService,
        'callback_data' => 'tr ' . $cmd->getUserId() . ' ' . $payload[1]
      ])
    ])->editMessageText($cmd->getChatId(), $cmd->getMsgId(), $text);
  }
}

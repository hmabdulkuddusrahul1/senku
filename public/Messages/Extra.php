<?php

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\Request\Request;
use Mateodioev\TgHandler\Commands;
use Mateodioev\Utils\fakeStdClass;
use Mateodioev\Utils\Files;
use Senku\Commands\Messages\Message;
use stdClass;

use function strlen, implode, file_put_contents, unlink;

class Extra extends Message
{
  private const URL = 'http://46.101.31.22/api';

  private function downMode(Methods $bot, Commands $cmd): fakeStdClass
  {
    return $bot->sendMessage($cmd->getChatId(), '⚠️ <b>Down mode</b> ⚠️');
  }

  public function start(Methods $bot, Commands $cmd)
  {
    $this->addReply($bot, $cmd);
    return $this->downMode($bot, $cmd);

    $payload = $cmd->getPayload();

    if (empty($payload) || strlen($payload) < 6) {
      return $this->onEmpty($bot, $cmd);
    }

    $data = self::search($payload);

    if (!$data->ok) {
      return $this->onInvalid($bot, $cmd);
    }

    $file = $_ENV['PWD_DIR'] . 'files\extra-' . substr($payload, 0, 6) . '.txt';
    $ccs = '';

    foreach ($data->data->ccs as $cc) {
      $ccs .= implode('|', $cc->card) . "\n";
    }

    file_put_contents($file, $ccs);

    $res = $bot->sendDocument([
      'document' => Files::tryOpen($file),
      'caption' => 'Total cards: ' . $data->data->total
    ]);
    unlink($file);
    return $res;
  }

  private function onEmpty(Methods $bot, Commands $cmd): fakeStdClass
  {
      return $bot->sendMessage($cmd->getChatId(), 'Please put one bin to search');
  }

  private function onInvalid(Methods $bot, Commands $cmd): fakeStdClass
  {
    return $bot->sendMessage($cmd->getChatId(), 'Not found');
  }

  public static function search(string $bin): fakeStdClass
  {
      return Request::get(self::URL . '/get')
          ->Run('/'.$bin)
          ->toJson(true)
          ->getBody();
  }


}

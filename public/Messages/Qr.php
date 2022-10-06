<?php 

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\Request\Request;
use Mateodioev\TgHandler\Commands;
use Mateodioev\Utils\fakeStdClass;
use Mateodioev\Utils\Network;

use function http_build_query, substr;

class Qr extends Message
{
  public const API_URL = 'https://api.qrserver.com/v1/';

  /**
   * Create QR codes
   */
  public function start(Methods $bot, Commands $cmd): fakeStdClass
  {
    $this->addReply($bot, $cmd);
    $payload = $cmd->getPayload();

    if (empty($payload)) {
      return $this->sendDefaultEmpty($bot, $cmd, 'Create qr codes', 'text');
    }

    $url = self::API_URL . 'create-qr-code/?' . http_build_query(['data' => substr($payload, 0, 900), 'format' => 'png', 'size' => '1000x1000']);

    return $bot->sendDocument([
      'document' => $url
    ]);
  }

  public function read(Methods $bot, Commands $cmd)
  {
    $this->addReply($bot, $cmd);
    $doc = $cmd->getDocument()->file_id
      ?? $cmd->getUpdate()->reply_to_message->photo[0]->file_id
      ?? $cmd->getUpdate()->reply_to_message->photo[1]->file_id
      ?? $cmd->getPayload();

    if (empty($doc)) {
      return $bot->sendMessage($cmd->getChatId(), 'Please reply to a message with photo or document to decode the qr');
    }

    $file_url = $doc;
    if (!Network::IsValidUrl($doc)) {
      // Get document data from telegram servers
      $file = $bot->getFile(['file_id' => $doc]);
      if (!$file->ok) {
        return $bot->sendMessage($cmd->getChatId(), '<b>Fail to fetch file data</b>');
      }
      $file_url = $bot->getFileLink() . $file->result->file_path;;
    }

    $this->addReply($bot, $cmd);
    $url = self::API_URL . 'read-qr-code/?' . http_build_query(['fileurl' => $file_url, 'outputformat' => 'json']);

    // Json response
    $res = (new Request)->init($url)->addOpts([
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_SSL_VERIFYPEER => false
    ])->Run()->toJson(true, true)->getBody();
    $res = new fakeStdClass((object) $res[0]);

    return $bot->sendMessage($cmd->getChatId(), $res->symbol[0]['data'] ?? 'Fail to decode <i>qr</i>');
  }
}

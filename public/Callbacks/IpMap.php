<?php

namespace Senku\Commands\Callbacks;

use Mateodioev\Bots\Telegram\Buttons;
use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\TgHandler\Commands;
use Senku\Commands\Messages\IpInfo;
use Senku\Commands\Plugins\Ip;

class IpMap extends IpInfo
{
  public function edit(Methods $bot, Commands $cmd)
  {
    $payload = explode(' ', $cmd->getPayload());
    
    if ($payload[0] == 'map') {
      return $this->sendMap($payload[1], $bot, $cmd);
    }
  }

  protected function sendMap(string $ip, Methods $bot, Commands $cmd)
  {
    $info = Ip::Ipdata($ip);

    $bot->editMessageReplyMarkup([
      'chat_id' => $cmd->getChatId(),
      'message_id' => $cmd->getMsgId()
    ]);
    return $bot->sendVenue([
      'latitude' => $info->latitude,
      'longitude' => $info->longitude,
      'title' => $info->asn->name . ' - ' . $info->asn->domain,
      'address' => $info->city . ' - ' . $info->region . ' - ' . $info->continent_name,
      'chat_id' => $cmd->getChatId()
    ]);
  }
}

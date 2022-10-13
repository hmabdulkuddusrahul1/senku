<?php 

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Buttons;
use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\TgHandler\Commands;
use Mateodioev\Utils\fakeStdClass;
use Mateodioev\Utils\Network;
use Senku\Commands\Plugins\Ip;

use function Mateodioev\Senku\b;
use function Mateodioev\Senku\i;
use function Mateodioev\Senku\n;

class IpInfo extends Message
{

  protected function parseInfo(fakeStdClass $i1, fakeStdClass $i2): string
  {
    $i1->setReturnValue('-');
    $i2->setReturnValue('-');

    $txt = b('✅ Valid ip ➜ ' . $i1->ip . ' ' . $i2->emoji_flag) .
      n() . b('Country: ') . i($i2->country_name . ' / ' . $i2->continent_name) .
      n() . b('Org: ') . i($i1->org) .
      n() . b('Type: ') . i(ucfirst($i2->asn->type)) .
      n() . b('Time/Zone: ') . i($i1->timezone) .
      n() . b('Zip code: ') . i($i1->postal) .
      n() . n() . b('Threat:') . n();

    foreach($i2->threat as $n => $v) {
      if ($n == 'blocklists') continue;

      $n = trim(str_replace(['is_', '_'], ['', ' '], $n));
      $v = $v === true ? 'True' : 'False';
      $txt .= "\t - " . b(ucfirst($n) . ': ') . i($v) . n();
    }
    return $txt;
  }

  public function start(Methods $bot, Commands $cmd)
  {
    $this->addReply($bot, $cmd);
    $ip = $cmd->getPayload();

    if (empty($ip) || !Network::IsValidIp($ip)) {
      return $this->sendDefaultEmpty($bot, $cmd, 'Ip search', '1.1.1.1');
    }
    
    $i1 = Ip::Info($ip);

    if ($i1->status > 200) {
      return $bot->sendMessage(
        $cmd->getChatId(),
        i(b($i1->error->title)) . n() . b($i1->error->message)
      );
    }

    $i2 = Ip::Ipdata($ip);

    return $bot->AddOpt([
      'reply_markup' => (string) Buttons::create()->addCeil([
        'text' => 'Get map',
        'callback_data' => 'ip map ' . $i1->ip
      ])
    ])->sendMessage($cmd->getChatId(), $this->parseInfo($i1, $i2));
  }
}

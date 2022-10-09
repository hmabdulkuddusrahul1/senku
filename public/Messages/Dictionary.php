<?php

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\Request\Request;
use Mateodioev\Request\RequestResponse;
use Mateodioev\TgHandler\Commands;

use function Mateodioev\Senku\b;
use function Mateodioev\Senku\i;
use function Mateodioev\Senku\n;
use function urlencode;

class Dictionary extends Message
{
  public const API_URL = 'https://api.dictionaryapi.dev/api/v2/entries/en/';
  
  /**
   * Search words
   */
  public static function search(string $word): RequestResponse
  {
    return Request::get(self::API_URL)
      ->addOpts([CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => false])
      ->Run(urlencode($word))
      ->toJson(true, true);
  }

  public function start(Methods $bot, Commands $cmd)
  {
    $this->addReply($bot, $cmd);
    $word = $cmd->getPayload();

    if (empty($word)) {
      return $this->sendDefaultEmpty($bot, $cmd, 'Dictionary api', 'word');
    }

    $info = self::search($word);

    if ($info->getInfo('http_code') != 200) {
      return $bot->sendMessage($cmd->getChatId(), '<b>Could not get meaning</b>');
    }

    $dic = $info->getBody()[0];
    $phonetic = $this->getPhonetic($dic['phonetics']);

    $txt = i('Meanings: ') . n();
    foreach ($dic['meanings'] as $val) {
      $txt .= b(' - ' . $val['partOfSpeech'] . ': ') . i($val['definitions'][0]['definition']) . n();
    }

    $method = 'sendMessage';
    if ($phonetic !== false) {
      $method = 'sendAudio';

      $txt .= b('Phonetic: '). i($phonetic['text']) .n();
      $bot->AddOpt([
        'audio' => $phonetic['audio'],
        'caption' => $txt,
      ]);
    } else {
      $bot->AddOpt(['text' => $txt]);
    }

    return $bot->request($method);
    
  }

  protected function getPhonetic(array $phonetics): array|false
  {
    foreach ($phonetics as $i) {
      if (isset($i['text']) && isset($i['audio'])) {
        return $i;
      }
    }
    return false;
  }
}

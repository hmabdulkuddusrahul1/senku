<?php

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\TgHandler\Commands;
use Mateodioev\{Translate, TranslateException};
use Mateodioev\Bots\Telegram\Buttons;
use Mateodioev\Utils\{Arrays, fakeStdClass, Numbers};

use function Mateodioev\Senku\{b, i, n, xQuit};
use function explode, trim, substr, strlen, ucfirst, sprintf;
use function file_put_contents, file_get_contents, json_encode, json_decode;

class Traduct extends Message
{
  /**
   * {basePath}files/tr_{name}.json
   */
  public string $filePath = '%s/files/tr_%s.json';

  protected string $txtSrc     = '';
  protected string $langInput  = 'output';
  protected string $langOutput = 'es';
  protected string $service    = 'google';

  private function extracText(Commands $cmd): void
  {
    $up = $cmd->getUpdate();

    if (empty($cmd->getPayload()) && !isset($up->message->reply_to_message->text)) {
      return;
    }
    // Langs code
    $lang = Arrays::MultiExplode([' ', "\n"], $cmd->getPayload())[0];
    $langs = explode('|', $lang);

    $this->langInput  = $langs[0] ?? 'auto';
    $this->langOutput = $langs[1] ?? 'es';
    // If only exist one key code set lang input to auto, and the key code if lang output
    if (!isset($langs[1])) {
      $this->langInput = 'auto';
      $this->langOutput = $langs[0];
    }

    $this->langOutput = empty($this->langOutput) ? 'es' : $this->langOutput;

    $this->txtSrc = $up->message->reply_to_message->caption
      ?? $up->message->reply_to_message->text
      ?? trim(substr($cmd->getPayload(), strlen($lang)));
    $this->txtSrc = substr($this->txtSrc, 0, 4000);
  }

  protected function translate(?string $apiKey = null): Translate
  {
    $tr = new Translate;
    $tr->setText($this->txtSrc)
      ->setInputLang($this->langInput)
      ->setOutputLang($this->langOutput);

    if ($this->service == 'google') {
      $tr->google();
      return $tr;
    }
    $tr->yandex($apiKey);
    return $tr;
  }

  protected function getText(Translate $tr, string $service = 'google'): string
  {
    return b(i(ucfirst($service) . ' Translate: ') . $tr->getLangName('input') . ' → ' . $tr->getLangName()) .
      n() . '~ ' . xQuit($tr->getText());
  }

  /**
   * return file id
   */
  protected function save(?int $id = null)
  {
    $file = $id ?? Numbers::genRandom(10);
    $payload = [
      'service' => $this->service,
      'text'    => $this->txtSrc,
      'langs'   => [
        'input'  => $this->langInput,
        'output' => $this->langOutput
      ]
    ];
    file_put_contents(sprintf($this->filePath, $_ENV['PWD_DIR'], $file), json_encode($payload));
    return $file;
  }

  protected function read(string $fileId): fakeStdClass
  {
    $file = $this->getFileName($fileId);
    $json = json_decode(file_get_contents($file));
    $response = new fakeStdClass($json);

    $this->service = $response->service;
    $this->txtSrc  = $response->text;
    $this->langInput = $response->langs->input;
    $this->langOutput = $response->langs->output;
    return $response;
  }

  protected function getFileName(string $fileId): string
  {
    return sprintf($this->filePath, $_ENV['PWD_DIR'], $fileId);
  }

  public function start(Methods $bot, Commands $cmd)
  {
    $this->addReply($bot, $cmd);
    $this->extracText($cmd);

    if (empty($this->txtSrc)) {
      return $this->sendDefaultEmpty($bot, $cmd, 'Translate message with google', 'lang_code text');
    }

    try {
      $tr = $this->translate();
    } catch (TranslateException $e) {
      return $bot->sendMessage($cmd->getChatId(), i($e->getMessage()));
    }

    if ($tr->error) {
      return $bot->sendMessage($cmd->getChatId(), b(i($tr->error_msg)));
    }

    $fileId = $this->save();

    return $bot->AddOpt([
      'reply_markup' => (string) Buttons::create()->addCeil([
        'text' => 'Use yandex',
        'callback_data' => 'tr ' . $cmd->getUserId() . ' ' . $fileId
      ])
    ])->sendMessage($cmd->getChatId(), $this->getText($tr));
  }
}

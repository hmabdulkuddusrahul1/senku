<?php 

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\TgHandler\Commands;
use Mateodioev\TgHandler\Runner;
use Mateodioev\Utils\fakeStdClass;
use Mateodioev\Utils\Files;

class Logs extends Message
{
  protected function getLogs(Runner &$runner): string
  {
    $logs = '';

    foreach ($runner->logs as $type => $types) {
      foreach ($types as $log) {
        $logs .= '[' . $type . '] ' . $log . "\n";
      }
    }

    $runner->logs = [];
    return $logs;
  }

  public function send(Runner &$runner, Methods $bot, Commands $cmd): fakeStdClass
  {
    $this->addReply($bot, $cmd);

    $logs = $this->getLogs($runner);
    $runner->logs = [];

    $file = $_ENV['PWD_DIR'] . '/files/logs.log';
    file_put_contents($file, $logs);

    $res = $bot->sendDocument(['document' => Files::tryOpen($file)]);

    unlink($file);
    return $res;
  }
}

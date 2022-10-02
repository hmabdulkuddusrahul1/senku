<?php 

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\TgHandler\Commands;

use function Mateodioev\Senku\{b, code, i, n, xQuit};

class Start extends Message
{
  public function send(Methods $bot, Commands $cmd)
  {
    $this->addReply($bot, $cmd);

    $bot->sendMessage($cmd->getChatId(), 'Hola!');
    return 1;
  }

  public function myCommands(Methods $bot, Commands $cmd)
  {
    $this->addReply($bot, $cmd);

    $commands = $cmd->getCommands();

    $txt = 'Hola ' . i(xQuit($cmd->getFullName())) . ' estos son todos mis mensajes' . n().n().b(i('Comandos de texto:')).n();

    foreach (array_keys($commands['message']) as $c) {
      $txt .= code($c) . ', ';
    } $txt = substr($txt, 0, -2) . n().n() . b(i('Comandos inline: ')).n();

    foreach (array_keys($commands['inline']) as $c) {
      $txt .= code($c) . ', ';
    } $txt = substr($txt, 0, -2);

    $bot->AddOpt(['parse_mode' => 'html'])->sendMessage($cmd->getChatId(), $txt);
    return 1;
  }
}

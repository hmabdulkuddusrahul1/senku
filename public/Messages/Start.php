<?php 

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Buttons;
use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\TgHandler\Commands;
use Mateodioev\Utils\fakeStdClass;

use function Mateodioev\Senku\{b, code, i, n, xQuit};

class Start extends Message
{
  public function send(Methods $bot, Commands $cmd): fakeStdClass
  {
    $this->addReply($bot, $cmd);

    $bot->AddOpt([
      'reply_markup' => (string) Buttons::create(others_params: ['resize_keyboard' => true])
        ->addCeil(['text' => 'Author', 'url' => 'https://github.com/Mateodioev'])
    ]);
    return $bot->sendMessage($cmd->getChatId(), 'Hola!, escribe ' . b('/help') . ' para ver los comandos disponibles');
  }

  public function myCommands(Methods $bot, Commands $cmd)
  {
    $this->addReply($bot, $cmd);

    $commands = $cmd->getCommands();

    $txt = 'Hola ' . i(xQuit($cmd->getFullName())) . ' estos son todos mis comandos' . n().n().b(i('Comandos de texto:')).n();

    foreach (array_keys($commands['message']) as $c) {
      $txt .= code($c) . ', ';
    } $txt = substr($txt, 0, -2) . n().n() . b(i('Comandos inline: ')).n();

    foreach (array_keys($commands['inline']) as $c) {
      $txt .= code($c) . ', ';
    } $txt = substr($txt, 0, -2);

    return $bot->AddOpt(['parse_mode' => 'html'])->sendMessage($cmd->getChatId(), $txt);
  }
}

<?php

use Dotenv\Dotenv;
use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\PhpEasyCli\App as CliApp;
use Mateodioev\TgHandler\{Commands, Runner};

require __DIR__ . '/vendor/autoload.php';

Dotenv::createMutable(__DIR__)->load();
$bot = (new Methods($_ENV['BOT_TOKEN']))->setTestEnviroment(true);

$commands = (new Commands('\Senku\Commands', ['!', '.', '/']))->setBotUsername($_ENV['BOT_USER']);
$cli = new CliApp;
$runner = new Runner($commands);

$commands->on('message|callback|inline', 'Plugins\Midlewares@onUpdate');

$commands->CmdMessage('start', 'Messages\Start@send', [$bot])
  ->CmdMessage(['cmds', 'help'], 'Messages\Start@myCommands', [$bot])
  ->CmdMessage('extra', 'Messages\Extra@start', [$bot])
  ->CmdMessage('usage', 'Messages\Usage@getMemory', [$bot])
  ->CmdMessage('log', 'Messages\Logs@send', [&$runner, $bot])
  ->CmdMessage(['clima', 'wheater'], 'Messages\Clima@send', [$bot]);

$commands->CmdCallback('clima', 'Callbacks\reloadClima@edit', [$bot])
  ->CmdCallback('usage', 'Callbacks\reloadUsage@edit', [$bot]);

// ->CmdMessage(['gen', 'ccgen'], 'Messages\CardGen@start', [$bot])
// ->CmdMessage(['crypto', 'p'], '', [])
// ->CmdMessage(['dicc', 'diccionario', 'meaning'], '', [])
// ->CmdMessage(['animales'], '', [])
// ->CmdMessage(['wiki', 'wikipedia'], '', [])
// ->CmdMessage(['write'], '', [])
// ->CmdMessage(['gbin'], '', [])
// ->CmdMessage(['g', 'google'], '', [])
// ->CmdMessage(['y', 'youtube'], '', [])
// ->CmdMessage(['qrgen'], '', [])
// ->CmdMessage(['qrread'], '', [])
// ->CmdMessage(['tr'], '', [])
// ->CmdMessage(['ip'], '', [])
// ->CmdMessage(['git'], '', [])
// ->CmdMessage(['bin'], '', []);

$runner->setCliApp($cli)->setBot($bot)->activateLog(true)->longPolling();

<?php

use Dotenv\Dotenv;
use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\Db\Connection;
use Mateodioev\PhpEasyCli\App as CliApp;
use Mateodioev\TgHandler\{Commands, Runner};

require __DIR__ . '/vendor/autoload.php';

Dotenv::createMutable(__DIR__)->load();
Connection::PrepareFromEnv(__DIR__);
Connection::addCharset();
$bot = (new Methods($_ENV['BOT_TOKEN']));#->setTestEnviroment(true);
$bot->timeout = 10;

$commands = (new Commands('\Senku\Commands', ['!', '.', '/']))->setBotUsername($_ENV['BOT_USER']);
$cli = new CliApp;
$runner = new Runner($commands);

$commands->on('message|callback|inline', 'Plugins\Midlewares@onUpdate')
  ->on('message', 'Plugins\Midlewares@chatBot', [$bot]);

$commands#->CmdMessage('start', 'Messages\Start@send', [$bot])
  ->CmdMessage('extra', 'Messages\Extra@start', [$bot])
  ->CmdMessage('usage', 'Messages\Usage@getMemory', [$bot])
  # ->CmdMessage('log', 'Messages\Logs@send', [&$runner, $bot])
  ->CmdMessage('tests', 'Messages\Tests@start', [$bot])
  ->CmdMessage('qr', 'Messages\Qr@start', [$bot])
  ->CmdMessage('ip', 'Messages\IpInfo@start', [$bot])
  ->CmdMessage('qread', 'Messages\Qr@read', [$bot])
  ->CmdMessage('bin', 'Messages\BinInfo@send', [$bot])
  ->CmdMessage('write', 'Messages\Write@send', [$bot])
  ->CmdMessage(['clima', 'wheater'], 'Messages\Clima@send', [$bot])
  ->CmdMessage(['google', 'g'], 'Messages\Google@start', [$bot])
  ->CmdMessage(['cmds', 'help'], 'Messages\Start@myCommands', [$bot])
  ->CmdMessage(['youtube', 'yt'], 'Messages\Youtube@start', [$bot])
  ->CmdMessage(['git', 'github'], 'Messages\Github@start', [$bot])
  ->CmdMessage(['wiki', 'wikipedia'], 'Messages\Wikipedia@start', [$bot])
  ->CmdMessage(['crypto', 'coin', 'p'], 'Messages\Crypto@start', [$bot])
  ->CmdMessage(['dicc', 'diccionario', 'meaning'], 'Messages\Dictionary@start', [$bot]);

$commands->CmdCallback('clima', 'Callbacks\reloadClima@edit', [$bot])
  ->CmdCallback('usage', 'Callbacks\reloadUsage@edit', [$bot])
  ->CmdCallback('coin', 'Callbacks\reloadCrypto@edit', [$bot])
  ->CmdCallback('ip', 'Callbacks\IpMap@edit', [$bot]);

$commands->CmdInline('bin', 'Inline\Bin@start', [$bot]);

// ->CmdMessage(['gen', 'ccgen'], 'Messages\CardGen@start', [$bot])
// ->CmdMessage(['write'], '', [])
// ->CmdMessage(['gbin'], '', [])
// ->CmdMessage(['tr'], '', [])
// ->CmdMessage(['ip'], '', [])

$runner->setCliApp($cli)->setBot($bot)->activateLog(true)->longPolling();

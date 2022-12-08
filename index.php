<?php

use Dotenv\Dotenv;
use Mateodioev\Bots\Telegram\{Methods, TelegramLogger};
use Mateodioev\Db\Connection;
use Mateodioev\PhpEasyCli\App as CliApp;
use Mateodioev\TgHandler\{Commands, Runner};

require __DIR__ . '/vendor/autoload.php';

TelegramLogger::Activate(__DIR__ . '/logs');
Dotenv::createMutable(__DIR__)->load();
Connection::PrepareFromEnv(__DIR__);
Connection::addCharset();

$bot = (new Methods($_ENV['BOT_TOKEN']));#->setTestEnviroment(true);
$bot->timeout = 50;

$commands = (new Commands('\Senku\Commands', ['!', '.', '/']))->setBotUsername($_ENV['BOT_USER']);
$cli = new CliApp;
$runner = new Runner($commands);

require __DIR__.'/boostrap.php';

$runner->setCliApp($cli)->setBot($bot)->activateLog(true)->longPolling();

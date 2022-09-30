<?php

use Mateodioev\Senku\Models\Response;

require __DIR__ . '/vendor/autoload.php';

$res = new Response;

$code = (int) Response::getKey('code', 403);

$res->sendHeader('X-message', 'Uri not found');
$res->sendError('Not found', $code);

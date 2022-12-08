<?php 

$commands->on('message|callback|inline', 'Plugins\Midlewares@onUpdate')
  ->on('message', 'Plugins\Midlewares@chatBot', [$bot])
  ->on('inline', 'Inline\Handler@start', [$bot]);

$commands->CmdMessage('start', 'Messages\Start@send', [$bot])
  ->CmdMessage('extra', 'Messages\Extra@start', [$bot])
  ->CmdMessage('usage', 'Messages\Usage@getMemory', [$bot])
  ->CmdMessage('qr', 'Messages\Qr@start', [$bot])
  ->CmdMessage('ip', 'Messages\IpInfo@start', [$bot])
  ->CmdMessage('go', 'Messages\Golang@start', [$bot])
  ->CmdMessage('qread', 'Messages\Qr@read', [$bot])
  ->CmdMessage('bin', 'Messages\BinInfo@send', [$bot])
  ->CmdMessage('dni', 'Messages\Dni@start', [$bot])
  ->CmdMessage('gbin', 'Messages\BinInfo@gBin', [$bot])
  ->CmdMessage('write', 'Messages\Write@send', [$bot])
  ->CmdMessage(['clima', 'wheater'], 'Messages\Clima@send', [$bot])
  ->CmdMessage(['google', 'g'], 'Messages\Google@start', [$bot])
  ->CmdMessage(['gen', 'ccgen'], 'Messages\CardGen@start', [$bot])
  ->CmdMessage(['cmds', 'help'], 'Messages\Start@myCommands', [$bot])
  ->CmdMessage(['youtube', 'yt'], 'Messages\Youtube@start', [$bot])
  ->CmdMessage(['tr', 'traduct'], 'Messages\Traduct@start', [$bot])
  ->CmdMessage(['git', 'github'], 'Messages\Github@start', [$bot])
  ->CmdMessage(['wiki', 'wikipedia'], 'Messages\Wikipedia@start', [$bot])
  ->CmdMessage(['crypto', 'coin', 'p'], 'Messages\Crypto@start', [$bot])
  ->CmdMessage(['dicc', 'diccionario', 'meaning'], 'Messages\Dictionary@start', [$bot]);

$commands->CmdCallback('clima', 'Callbacks\reloadClima@edit', [$bot])
  ->CmdCallback('usage', 'Callbacks\reloadUsage@edit', [$bot])
  ->CmdCallback('coin', 'Callbacks\reloadCrypto@edit', [$bot])
  ->CmdCallback('bin', 'Callbacks\Bin@start', [$bot])
  ->CmdCallback('ip', 'Callbacks\IpMap@edit', [$bot])
  ->CmdCallback('tr', 'Callbacks\TranslateService@alternate', [$bot])
  ->CmdCallback('gen', 'Callbacks\CardGen@start', [$bot]);

$commands->CmdInline('bin', 'Inline\Bin@start', [$bot])
  ->CmdInline('wiki', 'Inline\Wiki@start', [$bot]);

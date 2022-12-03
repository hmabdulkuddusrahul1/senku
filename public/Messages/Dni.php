<?php 

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\TgHandler\Commands;
use Senku\Commands\Plugins\Dni as SearchDni;

use function Mateodioev\Senku\{b, n};
use function file_put_contents, base64_decode, unlink;

class Dni extends Message
{
  public static function search(string $dni)
  {
    $searcher = new SearchDni;
    return $searcher->getDni($dni);
  }

  protected function parseResult(array $data): string
  {
    return b('Nombre:') . ' ' . $data['prenombres']
      . n() . b('Ap. Paterno:') . ' ' . $data['apPrimer']
      . n() . b('Ap. Materno:') . ' ' . $data['apSegundo']
      . n() . b('Dirección:') . ' ' . $data['direccion']
      . n() . b('Estado Civil:') . ' ' . $data['estadoCivil']
      . n() . b('Restricción:') . ' ' . $data['restriccion']
      . n() . b('Ubigeo:') . ' ' . $data['ubigeo'];
  }

  public function start(Methods $bot, Commands $cmd)
  {
    $this->addReply($bot, $cmd);
    $dni = $cmd->getPayload();

    if (empty($dni)) {
      return $this->sendDefaultEmpty($bot, $cmd, 'DNI search 🇵🇪', 'XXXXXXXX');
    }

    try {
      $data = self::search($dni);
    } catch (\Throwable $th) {
      return $bot->sendMessage($cmd->getChatId(), b($th->getMessage()) );
    }

    $foto_file = $_ENV['PWD_DIR'] . 'files/dni_' . $dni . '.jpg';
    file_put_contents($foto_file, base64_decode($data['foto']));

    $toReturn = $bot->sendPhoto($cmd->getChatId(), $foto_file, $this->parseResult($data));
    unlink($foto_file);
    return $toReturn;
  }
}

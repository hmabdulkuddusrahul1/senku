<?php

namespace Mateodioev\Senku\Models;

use Mateodioev\Utils\{Luhn, Numbers};
use RuntimeException, UnexpectedValueException;

class CardGen
{
  public int $mount = 10;
  private array $card = ['cc' => '', 'mm' => '', 'yy' => '', 'cvv' => ''];
  private array $details = [
    'length' => ['cc' => 15, 'cvv' => 4],
    'onEmpty' => 'rnd'
  ];

  private array $gen_cards = [];

  public function SetMount(int $mount): CardGen
  {
    if ($mount < 1) throw new UnexpectedValueException('Invalid mount (' . $mount . '), min mount is 1');
    
    $this->mount = $mount;
    return $this;
  }

  public function SetCard(string $cc): CardGen
  {
    $cc = \str_replace(' ', '', $cc);
    $length = (int) \strlen($cc);
    if ($length < 6 OR $length > 16) throw new UnexpectedValueException('Invalid card length ('.$length.')');

    $this->card['cc'] = $cc;
    $prex = \substr($cc, 0, 1);
    $this->details['length'] = [
      'cc' => $prex == 3 ? 15 : 16,
      'cvv' => $prex == 3 ? 4 : 3
    ];
    return $this;
  }

  public function SetMonth(string $mes): CardGen
  {
    $this->card['mm'] = $mes;

    if (!empty($mes) && ($mes < 1 || $mes > 12)) {
      throw new UnexpectedValueException('Invalid mounth');
    }
    return $this;
  }

  public function SetYear(string $year): CardGen
  {
    $this->card['yy'] = $year;

    if (!empty($year) && ($year < \date('Y') || $year > \date('Y') + 10)) {
      throw new UnexpectedValueException('Invalid year');
    } elseif (\strlen($year) != 4 && !empty($year)) {
      throw new UnexpectedValueException('Invalid year length, do you mean 20' . $year . '?');
    }
    return $this;
  }
  
  public function SetCVV(string $cvv): CardGen
  {
    $this->card['cvv'] = $cvv;

    if (!empty($cvv) && (($cvv < 100 || $cvv > 9999) || \strlen($cvv) != $this->details['length']['cvv'])) {
      throw new UnexpectedValueException('Invalid cvv');
    }
    return $this;
  }

  public function Gen(?string $cc = null, ?string $mes = null, ?string $year = null, ?string $cvv = null): array
  {
    $this->SetCard($cc ?? $this->card['cc'])->SetMonth($mes ?? $this->card['mm'])->SetYear($year ?? $this->card['yy'])->SetCVV($cvv ?? $this->card['cvv']);

    $cards = [];
    for ($i=0; $i < 100; $i++) {
      $cc = $this->completCard();
      $this->gen_cards[] = $cc;
      $cards[] = [$cc, $this->genMonth(), $this->genYear(), $this->genCvv()];
    }
    $this->validate();
    $unique = [];
    $cc = [];
    foreach ($cards as $ccs) {
      if (in_array($ccs[0], $unique) === false) {
        $unique[] = $ccs[0];
        $cc[] = implode('|', $ccs);
      }
    }
    return array_slice($cc, 0, $this->mount);
  }

  /**
   * - Input: `1x223x`
   * - Output: `132236`
   */
  private function replaceX(string $str, string $replace = ''): string
  {
    $length = \strlen($str);
    $newStr = '';
    for ($i = 0; $i < $length; $i++) {
      if ($str[$i] == 'x') {
        $newStr .= Numbers::genRandom(1);
      } else {
        $newStr .= $str[$i];
      }
    }
    return $this->quitString($newStr, $replace);
  }

  /**
   * Return only numeric characters
   */
  private function quitString(string $str, string $replace = '')
  {
    return \preg_replace('/[^0-9]/', $replace, $str);
  }

  /**
   * Complete card with luhn
   */
  private function completCard(): int
  {
    $card = $this->card['cc'];
    $length = $this->details['length']['cc'];
    $subject_length = (\strlen($card) == $length) ? $length-1 : $length;
    $prefix = \substr(\str_replace(' ', '', $card), 0, $subject_length);
    $prefix = $this->replaceX($prefix);

    while (\strlen($prefix) < $length-1) {
      $prefix .= Numbers::genRandom();
    }
    return (int) ($prefix . Luhn::calculateCheckDigit($prefix));
  }

  private function genCvv(): string
  {
    $cvv = $this->replaceX($this->card['cvv']);
    $length = $this->details['length']['cvv'];

    while (\strlen($cvv) < $length) {
      $cvv .= Numbers::genRandom();
    }
    return $cvv;
  }

  private function genMonth(): string
  {
    $month = $this->quitString($this->card['mm']);

    if (!empty($month)) {
      return str_pad($month, 2, '0', STR_PAD_LEFT);
    } elseif ($this->card['yy'] == \date('Y')) {
      $month = \mt_rand(\date('m'), 12);
    } else {
      $month = \mt_rand(0, 12);
    }
    return str_pad($month, 2, '0', STR_PAD_LEFT);
  }

  private function genYear(): string
  {
    $year = $this->quitString($this->card['yy']);
    if (!empty($year)) {
      return $year;
    } else {
      return \mt_rand(\date('Y'), \date('Y') + 10);
    }
  }

  /**
   * @throws RuntimeException
   */
  private function validate(int $porcentage = 90)
  {
    // 10 ccs generate
    $total = count($this->gen_cards); // 10
    $ccs = count(array_unique($this->gen_cards)); // 9
    $unique_ccs = ($ccs * 100) / $total;

    $luhn = 0;
    foreach ($this->gen_cards as $cc) {
      if (Luhn::isValid($cc)) $luhn++;
    }
    $luhn = ($luhn*100) / $total;

    if ($unique_ccs < $this->mount || $luhn < $porcentage) {
      throw new RuntimeException('Invalid input, all cards are the same or no pass luhn');  
    }
  }
}

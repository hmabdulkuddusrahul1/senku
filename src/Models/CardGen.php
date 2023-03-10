<?php

namespace Mateodioev\Senku\Models;

use Mateodioev\Utils\{Luhn, Numbers};
use RuntimeException, UnexpectedValueException;

class CardGen
{
  protected static string $regex = "[\[\dxrand\]\{6,16\}]*[\[\d\]\[x\]\[rnd\]\[rand\]]{1,4}";

  public int $mount = 10;
  private array $card = ['cc' => '', 'mm' => '', 'yy' => '', 'cvv' => ''];
  private array $details = [
    'length' => ['cc' => 15, 'cvv' => 4],
    'onEmpty' => 'rnd'
  ];

  private array $gen_cards = [];


  public static function extract(string $input): ?array
  {
    $cc = \str_replace(['|', '/', ':', "\n", "\t", ' '], ' ', \strtolower($input));
    \preg_match_all("/" . self::$regex . "/i", $cc, $matches);

    $card = [];
    foreach ($matches[0] as $cc) {
      if (\preg_match("/[\dxrand]{6,16}/i", $cc)) {
        $card[] = $cc;
      } elseif (\preg_match("/\d/", $cc)) {
        $card[] = $cc;
      } elseif (\in_array($cc, ['rnd', 'rand', 'random', 'xxxx', 'xxx', 'xx', 'x'])) {
        $card[] = $cc;
      } else {
        continue;
      }
    }
    if (empty($card))
      return null;
    return $card;
  }

  public function SetMount(int $mount): CardGen
  {
    if ($mount < 1) throw new UnexpectedValueException('Invalid mount (' . $mount . '), min mount is 1');
    
    $this->mount = $mount;
    return $this;
  }

  public function SetCard(string $cc): CardGen
  {
    $length = (int) \strlen($cc);
    if ($length < 6 OR $length > 16) {
      throw new UnexpectedValueException('Invalid card length (' . $length . ')');
    }

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

    if (!Numbers::isNumber($mes)) {
      $this->card['mm'] = 'rnd';
      return $this;
    }

    $mes = (int) $mes;
    $this->card['mm'] = $mes;

    if (!empty($mes) && ($mes < 1 || $mes > 12)) {
      throw new UnexpectedValueException('Invalid month');
    }
    if (!empty($this->card['yy']) && $this->card['yy'] == date("Y") && $mes < date("n")) {
      throw new UnexpectedValueException('Expired month');
    }
    return $this;
  }

  public function SetYear(string $year): CardGen
  {
    $this->card['yy'] = $year;

    if (!Numbers::isNumber($year)) {
      $this->card['yy'] = 'rnd';
      return $this;
    }
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

    if (!Numbers::isNumber($cvv))
      return $this;

    if (!empty($cvv) && (($cvv < 100 || $cvv > 9999) || \strlen($cvv) != $this->details['length']['cvv'])) {
      throw new UnexpectedValueException('Invalid cvv');
    }
    return $this;
  }

  public function Gen(?string $cc = null, ?string $mes = null, ?string $year = null, ?string $cvv = null): array
  {
    $this->SetCard($cc ?? $this->card['cc'])->SetYear($year ?? $this->card['yy'])->SetMonth($mes ?? $this->card['mm'])->SetCVV($cvv ?? $this->card['cvv']);

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
      if (\in_array($ccs[0], $unique) === false) {
        $unique[] = $ccs[0];
        $cc[] = \implode('|', $ccs);
      }
    }
    return \array_slice($cc, 0, $this->mount);
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

  private function complete(string $input, int $length, int $min = 0)
  {
    $input = $this->quitString($input);
    
    while (\strlen($input) < $length) {
      $input .= \mt_rand($min, 9);
    }

    return $input;
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
      $month = \mt_rand(1, 12);
    }
    return str_pad($month, 2, '0', STR_PAD_LEFT);
  }

  private function genYear(): string
  {
    $year = $this->quitString($this->card['yy']);
    $actualYear = \date('Y');
    if (!empty($year) && \strlen($year) > 2) {
      return $this->complete($year, 4, substr($actualYear, -1));
    } else {
      return \mt_rand($actualYear, $actualYear + 10);
    }
  }

  /**
   * @throws RuntimeException
   */
  private function validate(int $porcentage = 90)
  {
    // 10 ccs generate
    $total = \count($this->gen_cards); // 10
    $ccs = \count(\array_unique($this->gen_cards)); // 9
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

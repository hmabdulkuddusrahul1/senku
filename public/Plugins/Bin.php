<?php 

namespace Senku\Commands\Plugins;

use Mateodioev\Senku\Config\Utils;
use Mateodioev\Utils\fakeStdClass;
use Senku\Commands\Plugins\QueryMaker;

class Bin extends QueryMaker
{
  protected string $tableName = 'bins_info';
  protected string $id = 'bin';

  public function __construct() {
    $this
      ->setTableName($this->tableName)
      ->setId($this->id);
  }

  /**
   * @param string $bin Bin to search
   * @return boolean|fakeStdClass Return array on success, bool on failure
   */
  public function search(string $bin): bool|fakeStdClass
  {
    $bin = substr(Utils::removeLetters($bin), 0, 6);
    $query = (string) $this->select()->where(':number', '=')->getQuery();

    $res = $this->Exec($query, ['number' => $bin])['data'];

    return $res === false 
      ? false 
      : new fakeStdClass((object) $res);
  }

  public function getSimilar(string $bin, int $limit = 0, int $offset = 0)
  {
    $bin = substr(Utils::removeLetters($bin), 0, 6);
    $query = (string) $this->select()->like($this->id, ':number')->limit($limit, $offset)->getQuery();
    
    $res = $this->GetAll($query, ['number' => '%' . $bin])['rows'];

    foreach ($res as $row) {
      yield new fakeStdClass((object) $row);
    }
  }
}

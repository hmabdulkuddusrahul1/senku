<?php 

namespace Senku\Commands\Plugins;

use Mateodioev\Db\Query;

use function array_merge, array_keys;

/**
 * SQL Query maker
 */
class QueryMaker extends Query
{
  private string $id = '';
  private bool $callWhere = false;
  
  public string $sqlQuery = '';
  
  protected string $table = '';
  protected array $keys = [];

  public function __construct(?string $table=null, ?string $id=null) {
    if ($table && !empty($table)) {
      $this->setTableName($table);
    }

    if ($id && !empty($id)) {
      $this->setId($id);
    }
  }

  /**
   * set table name
   */
  public function setTableName(string $tableName): static
  {
    $this->table = $tableName;
    return $this;
  }

  /**
   * set id of the table
   */
  public function setId(string $id): static
  {
    $this->id = $id;
    return $this;
  }

  public function __set($name, $value)
  {
    $this->keys[$name] = $value;
  }
  
  /**
   * get table keys
   */
  private function getKeys(array $params = [], bool $removeId = false): array
  {
    $keys = array_merge(array_keys($this->keys), array_keys($params));
    if ($removeId) unset($keys[$this->id]);
    return $keys;
  }
  
  /**
   * get table values
   */
  private function getValues(array $params = [], bool $removeId = false): array
  {
    $values = array_merge(array_values($this->keys), array_values($params));
    if ($removeId) unset($values[$this->id]);
    return $values;
  }

  /**
   * Add where clause to the query
   */
  public function where(string $value, string $condition = '=', string $after = '&'): static
  {
    if (!$this->callWhere) {
      $this->sqlQuery .= ' WHERE `' . $this->id . '` ' . $condition . ' ' . $value;
    } else {
      $this->sqlQuery .= ' ' . $after . ' `' . $this->id . '` ' . $condition . ' ' . $value;
    }
    $this->callWhere = true;
    return $this;
  }

  /**
   * LIKE clause to the query
   */
  public function like(string $columnName, string $pattern): static
  {
    $this->sqlQuery .= ' WHERE `' . $columnName . '` LIKE ' .$pattern;
    return $this;
  }

  /**
   * Limit the numbers of results
   */
  public function limit(int $rowCount, ?int $offset = 0): static
  {
    $this->sqlQuery .= ' LIMIT ' . $rowCount;
    $this->sqlQuery .= $offset ? ' OFFSET ' . $offset : '';
    return $this;
  }

  /**
   * Order the results
   * 
   * @param string|null $sorted ASC|DESC order
   */
  public function orderBy(array $columns = [], ?string $sorted = null): static
  {
    $this->sqlQuery .= ' ORDER BY ' . implode(', ', $columns);
    $this->sqlQuery .= $sorted ? ' ' . $sorted : '';
    return $this;
  }
  
  /**
   * create insert Query
   */
  public function insert(array $params = []): static
  {
    $keys = $this->getKeys($params);
    $values = $this->getValues($params);

    $this->sqlQuery = 'INSERT INTO ' . $this->table . ' (' . implode(', ', $keys) . ') VALUES (' . implode(', ', $values) . ')';
    return $this;
  }

  /**
   * create update Query sql
   */
  public function update(array $params = []): static
  {
    $keys = $this->getKeys($params);
    $values = $this->getValues($params);

    $this->sqlQuery = 'UPDATE `' . $this->table . '` SET ';

    foreach ($keys as $i => $key) $this->sqlQuery .= '`' . $key . '`' . ' = ' . $values[$i] . ', ';

    if (strpos($this->sqlQuery, ', ') !== false) {
      $this->sqlQuery = substr($this->sqlQuery, 0, -2);
    } else {
      $this->sqlQuery = str_replace('SET ', '', $this->sqlQuery);
    }

    return $this;
  }

  /**
   * Select values from a table
   */
  public function select(array $columns = []): static
  {
    $keys = $this->getValues($columns);

    if (empty($keys)) $keys = ['*'];

    $newKeys = [];

    foreach ($keys as $key) {
      $key = explode(' ', $key, 3);
      if (count($key) == 3) {
        $newKeys[] = '`' . $key[0] . '` as `' . $key[2] . '`';
      } else {
        $newKeys[] = $key[0] == '*' ? '*' : '`' . $key[0] .'`';
      }
    }

    $this->sqlQuery = 'SELECT ' . implode(', ', $newKeys) . ' FROM `' . $this->table . '`';
    return $this;
  }

  /**
   * Delete values from a table
   * 
   * *Note:* Use `static::where()` or `static::like()` after calling this method!!
   */
  public function remove(): static
  {
    $this->sqlQuery = 'DELETE FROM `' . $this->table . '`';
    return $this;
  }

  public function getQuery(): string
  {
    return $this->sqlQuery;
  }
}

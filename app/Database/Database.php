<?php

namespace App\Database;

use \PDO;
use \PDOException;

class Database {

  /**
   * Instância de PDO
   * @var ?PDO
   */
  private $pdo;

  /**
   * Tabela a ser manipulada
   * @var string
   */
  private $table;

  /**
   * Alias para a tebela
   * @var string
   */
  private $as;

  /**
   * Recebe o nome do campo da condição where
   * @var string
   */
  private $whereField;

  /**
   * Array contendo os parâmetros para a execução do where
   * @var array
   */
  private $where = [];

  /**
   * Inicia a classe e instância de PDO
   */
  public function __construct() {
    $this->connection();
  }

  /**
   * Cria uma instância de Database
   * @return self
   */
  public static function connect() {
    return new self;
  }

  /**
   * Cria uma conexão com o banco de dados
   */
  private function connection() {
    $host   = getenv('DBHOST');
    $dbname = getenv('DBNAME');
    $user   = getenv('DBUSER');
    $pass   = getenv('DBPASS');

    try {
      $this->pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
      $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      die('Error: '.$e->getMessage());
    }
  }

  /**
   * Define a tabela que será usada no comando
   * @param string $table
   * @return self
   */
  public function table($table, $as = null) {
    $this->table = $table;
    $this->as = $as;
    return $this;
  }

  /**
   * Define uma operação SELECT
   * @param string $fields;
   * @return self
   */
  public function select($fields = '*') {
    $this->sql = "SELECT $fields FROM $this->table";

    $this->as? $this->sql .= " as $this->as" : null;
    return $this;
  }

  /**
   * Define uma operação INSERT
   * @param string $fields
   * @return self
   */
  public function insert($fields) {
    $binds = $this->binds($fields, ':{bind}');

    $this->sql = "INSERT INTO $this->table ($fields) VALUES ($binds)";

    return $this;
  }

  /**
   * Define uma operação UPDATE
   * @param string $fields
   * @return self
   */
  public function update($fields) {
    $binds = $this->binds($fields, '{bind} = :{bind}');

    $this->sql = "UPDATE $this->table SET $binds";

    return $this;
  }

  /**
   * Define uma operação DELETE
   * @return self
   */
  public function delete() {
    $this->sql = "DELETE FROM $this->table";
    return $this;
  }

  /**
   * Define um INNER JOIN
   * @param string $table
   * @param string $as
   * @return self
   */
  public function innerJoin($table, $as = null) {
    $this->sql .= " INNER JOIN $table";

    $as? $this->sql .= " as $as" : null;

    return $this;
  }

  /**
   * Declaração ON para JOINS
   * @param string $params
   * @return self
   */
  public function on($params) {
    $this->sql .= " ON $params";
    return $this;
  }

  /**
   * Declaração LIMIT
   * @param int $limit
   * @param int $begin
   * @return self
   */
  public function limit($limit, $begin = 0) {
    $this->sql .= " LIMIT $begin, $limit";
    return $this;
  }

  /**
   * Declaração ORDER BY
   * @param string $field,
   * @param string $direction
   * @return self
   */
  public function orderBy($field, $direction = 'ASC') {
    $this->sql .= " ORDER BY $field $direction";
    return $this;
  }

  /**
   * Define uma condição para o comando
   * @param string $field
   * @return self
   */
  public function where($field) {
    $this->sql .= " WHERE $field";
    $this->whereField = $field;
    return $this; 
  }

  /**
   * Define um AND para um WHERE
   * @param string $field
   */
  public function and($field) {
    $this->sql .= " AND $field";
    $this->whereField = $field;
    return $this;
  }

  /**
   * Define uma condição EQUAL para um WHERE
   * @param mixed $value
   * @return self
   */
  public function equal($value) {
    $this->sql .= " = :$this->whereField";
    $this->where[$this->whereField] = $value; 
    return $this;
  }

  /**
   * Executa o comando SQL
   * @param array $data
   * @return PDOStatement|int
   */
  public function execute($data = []) {
    $data = array_merge($data, $this->where);

    try {
      $stmt = $this->pdo->prepare("$this->sql;");
      $stmt->execute($data);

      if(preg_match('/insert/i', $this->sql)) {
        return $this->pdo->lastInsertId();
      }

      $this->pdo = null;

      return $stmt;
    } catch (PDOException $e) {
      die('Error. '.$e->getMessage());
    }
  }

  /**
   * Cria os binds para as comandos
   * @return string
   */
  private function binds($fields, $format) {
    $fields = array_map('trim', explode(',', $fields));

    return implode(', ', array_map(function($item) use($format) {
      return preg_replace('/{bind}/', $item, $format);
    }, $fields));
  }
}
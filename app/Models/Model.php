<?php

namespace App\Models;

use App\Database\Database;

class Model {

  const TABLE = '';

  /**
   * Retorna o nome da classe que foi instanciada
   * @return string
   */
  private static function class() {
    return get_called_class();
  }

  /**
   * Insere dados no banco de dados
   * @param array $data
   * @return int
   */
  public static function create($data) {
    $fields = implode(', ', array_keys($data));
    
    return Database::connect()
      ->table(self::class()::TABLE)
      ->insert($fields)
      ->execute($data);
  }

  /**
   * Retorna um dado pelo ID
   * @param array $id
   * @param string $fields
   * @return object
   */
  public static function findById($id, $fields) {
    $field = array_keys($id)[0];
    $value = array_values($id)[0];

    return Database::connect()
      ->table(self::class()::TABLE)
      ->select($fields)
      ->where($field)->equal($value)
      ->execute()
      ->fetchObject(self::class());
  }

  /**
   * Retorna um registro
   * @param string $field
   * @param string $value
   * @param string $fields
   * @return object
   */
  public static function findOne($field, $value, $fields) {
    return Database::connect()
      ->table(self::class()::TABLE)
      ->select($fields)
      ->where($field)->equal($value)
      ->execute()
      ->fetchObject(self::class());
  }

  /**
   * Retorna múltiplos registros
   * @param array $param
   * @param string $fields
   * @return array
   */
  public static function findMany($param, $fields) {
    $field = array_keys($param)[0];
    $value = array_values($param)[0];

    return Database::connect()
      ->table(self::class()::TABLE)
      ->select($fields)
      ->where($field)->equal($value)
      ->execute()
      ->fetchAll(\PDO::FETCH_CLASS, self::class());
  }

  /**
   * Delete um registro pelo id
   * @param array $params
   * @return int
   */
  public static function deleteById($param) {
    $field = array_keys($param)[0];
    $value = array_values($param)[0];

    return Database::connect()
      ->table(self::class()::TABLE)
      ->delete()
      ->where($field)->equal($value)
      ->execute()
      ->rowCount();
  }

  /**
   * Deleta vários registros
   * @param array $params
   * @return int
   */
  public static function deleteMany($param) {
    $field = array_keys($param)[0];
    $value = array_values($param)[0];

    return Database::connect()
      ->table(self::class()::TABLE)
      ->delete()
      ->where($field)->equal($value)
      ->execute()
      ->rowCount();
  }

  /**
   * Salva as alterações no registro
   * @param string $idField
   * @return int
   */
  public function save($idField) {
    $params = get_object_vars($this);
    $fields = implode(', ', array_keys($params));

    return Database::connect()
      ->table(self::class()::TABLE)
      ->update($fields)
      ->where($idField)->equal($this->getId())
      ->execute($params)
      ->rowCount();
  }

  /**
   * Retorna a quantidade de registros salvos
   * @param string $field
   */
  public static function count($field) {
    return Database::connect()
      ->table(self::class()::TABLE)
      ->select("COUNT($field) as total")
      ->execute()
      ->fetch(\PDO::FETCH_ASSOC)['total'];
  }
}
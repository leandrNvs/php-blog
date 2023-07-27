<?php

namespace App\Models;

use App\Database\Database;

class User extends Model {

  const TABLE = 'user';

  /**
   * ID do usuário
   * @var int
   */
  private $iduser;

  /**
   * Nome do usuário
   * @var string
   */
  public $name;

  /**
   * Email do usuário
   * @var string
   */
  public $email;

  /**
   * Biografia do usuário
   * @var string
   */
  public $bio;

  /**
   * Foto de perfil do usuário
   * @var string
   */
  public $profile;

  /**
   * Senha do usuário
   * @var string
   */
  public $password;

  /**
   * Retorna o id do usuário
   * @return string
   */
  public function getId() {
    return $this->iduser;
  }

  /**
   * Procura um usuário pelo email
   * @param array $params
   * @param string $fields
   * @return self;
   */
  public static function getUserByEmail($params, $fields) {
    $field = array_keys($params)[0];
    $value = array_values($params)[0];

    return parent::findOne($field, $value, $fields);
  }

  /**
   * Adiciona um post como favorito
   * @return int
   */
  public static function addFavorite($params) {
    $fields = implode(', ', array_keys($params));

    return Database::connect()
      ->table('user_favorites')
      ->insert($fields)
      ->execute($params);
  }

  /**
   * Adiciona um post como favorito
   * @return int
   */
  public static function addUnfavorite($params) {
    $fields = implode(', ', array_keys($params));

    return Database::connect()
      ->table('user_favorites')
      ->delete()
      ->where('idpost')->equal($params['idpost'])
      ->and('iduser')->equal($params['iduser'])
      ->execute();
  }

  /**
   * Retorna os items favoritos do usuário
   */
  public static function getFavorites($userId) {
    return Database::connect()
      ->table('user_favorites')
      ->select('idpost')
      ->where('iduser')->equal($userId)
      ->execute()
      ->fetchAll(\PDO::FETCH_COLUMN, 0);
  }

  public static function getUserFavorites($userId) {
    return Database::connect()
      ->table('user_favorites', 'uf')
      ->select('p.idpost, p.title')
      ->innerJoin('user', 'u')
      ->on('u.iduser = uf.iduser')
      ->innerJoin('post', 'p')
      ->on('p.idpost = uf.idpost')
      ->execute()
      ->fetchAll(\PDO::FETCH_ASSOC);
  }
}
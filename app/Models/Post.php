<?php

namespace App\Models;

use App\Database\Database;

class Post extends Model {

  const TABLE = 'post';

  /**
   * ID do post
   * @var int
   */
  private $idpost;

  /**
   * ID do autor
   * @var int
   */
  private $iduser;

  /**
   * Título do post
   * @var string 
   */
  public $title;

  /**
   * Texto do post
   * @var string
   */
  public $text;

  /**
   * Retorna o ID do post
   * @return int
   */
  public function getId() {
    return $this->idpost;
  }

  /**
   * Retorna o ID do autor do post
   * @return int
   */
  public function getAuthor() {
    return $this->iduser;
  }

  /**
   * Retorna todos os posts registrados
   * @return array
   */
  public static function getPosts($begin, $limit) {
    return Database::connect()
      ->table(self::TABLE, 'p')
      ->select('p.idpost, p.title, p.text, p.createdAt, u.iduser, u.name')
      ->innerJoin('user', 'u')
      ->on('p.iduser = u.iduser')
      ->orderBy('createdAt', 'DESC')
      ->limit($limit, $begin)
      ->execute()
      ->fetchAll(\PDO::FETCH_ASSOC);
  }
}
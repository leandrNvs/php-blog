<?php

namespace App\Common;

class Enviroment {

  /**
   * Carrega as variáveis de ambiente
   * @param string $dir
   */
  public static function load($dir) {
    $variables = self::getDotEnv($dir);

    foreach($variables as $var) {
      $var = trim($var);
      $var = str_replace(' ', '', $var);

      putenv($var);
    }
  }

  /**
   * Procura pelo arquivo .env e retorna as variáveis em um array
   * @param string $dir
   * @return array
   */
  public static function getDotEnv($dir) {
    $file = $dir."/.env";

    if(!file_exists($file)) die('File .env not fund');

    return file($file);
  }
}
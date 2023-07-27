<?php 

namespace App\Utils;

class View {

  /**
   * Renderiza uma view
   * @param string $view
   * @param array $params
   * @return string
   */
  public static function render($view, $params = []) {
    $view = self::getView($view);

    $fields = array_map(function($items) {
      return '{{'.$items.'}}';
    }, array_keys($params));

    return str_replace($fields, array_values($params), $view);
  }

  /**
   * Retorna uma view
   * @param string $view
   * @return string
   */
  private static function getView($view) {
    $view = dirname(dirname(__DIR__))."/resources/views/$view.html";

    if(!file_exists($view)) die("A view <strong>$view</strong> não existe.");

    return file_get_contents($view);
  }

}
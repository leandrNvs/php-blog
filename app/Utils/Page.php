<?php 

namespace App\Utils;

class Page {

  /**
   * Renderiza uma página
   * @param string $view
   * @param array $params
   * @return string
   */
  public static function render($view, $title, $params = []): string {
    return View::render("layout", [
      "url"           => getenv('URL'),
      "title"         => $title,
      "header"        => self::getHeader(),
      "content"       => $view,
      "footer"        => self::getFooter(),
      "toast-message" => $params["toast-message"] ?? "",
      "toast-status"  => $params["toast-status"] ?? "none"
    ]);
  }

  /**
   * Retorna o header da página
   * @return string
   */
  private static function getHeader() {
    $userIsLogged = isset($_SESSION['user']['isLogged']);

    return View::render("components/header", [
      "userIsNotLogged" => $userIsLogged? 'none' : 'flex',
      "userIsLogged" => $userIsLogged? 'flex' : 'none'
    ]);
  }

  /**
   * Retorna o footer da página
   * @return string
   */
  private static function getFooter() {
    return View::render("components/footer");
  }
}
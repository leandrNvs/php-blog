<?php

namespace App\Http;

class Request {

  /**
   * Método HTTP da requisição
   * @var string
   */
  private $httpMethod;

  /**
   * URI da requisição
   * @var string
   */
  private $uri;

  /**
   * Parâmetros da requisição
   * @var array
   */
  public $params = [];

  /**
   * Variáveis $_POST
   * @var array
   */
  public $body = [];

  /**
   * Variáveis $_FILES
   * @var array
   */
  public $files = [];

  public function __construct() {
    $this->setHttpMethod();
    $this->setUri();
    $this->setBody();

    $this->files = $_FILES;
  }

  /**
   * Configura a URI da requisição
   */
  private function setUri() {
    $this->uri = $_SERVER['REQUEST_URI'];
  }

  /**
   * Configura o método HTTP da requisição
   */
  private function setHttpMethod() {
    $this->httpMethod = $_POST['_method'] ?? $_SERVER['REQUEST_METHOD'];
    unset($_POST['_method']);
  }

  /**
   * Configura o corpo da requisição
   */
  private function setBody() {
    foreach($_POST as $key => $value) {
      $value = trim($value);
      $value = stripslashes($value);
      $value = htmlspecialchars($value);

      if(strlen($value)) {
        $this->body[$key] = $value;
      }
    }
  }

  /**
   * Retorna a URI da requisição
   * @return string
   */
  public function getUri() {
    return $this->uri;
  }

  /**
   * Retorna o método HTTP da requisição
   * @return string
   */
  public function getHttpMethod() {
    return strtolower($this->httpMethod);
  }
}
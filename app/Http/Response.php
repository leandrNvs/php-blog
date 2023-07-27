<?php

namespace App\Http;

class Response {

  /**
   * Constante para o tipo de resposta application/json
   */
  const JSON = 'application/json';

  /**
   * URL do sistema
   * @var string
   */
  private $url;

  /**
   * Código de status HTTP da resposta
   * @var int
   */
  private $statusCode;

  /**
   * Conteúdo da resposta
   * @var mixed
   */
  private $content;

  /**
   * Inicia a classe e configura a URL
   * @param string $url
   */
  public function __construct() {
    $this->url = getenv('URL');
  }

  /**
   * Configura o código de status da resposta
   * @param int $code
   */
  public function status($code) {
    $this->statusCode = $code;
    return $this;
  }

  /**
   * Configura o conteúdo da resposta
   * @param mixed $content
   */
  public function content($content) {
    $this->content = $content;
    return $this;
  }

  /**
   * Envia a resposta para o usuário
   * @param string $contentType
   */
  public function sendResponse($contentType = 'text/html') {
    header('Content-Type: '.$contentType);

    http_response_code($this->statusCode);

    switch($contentType) {
      case 'text/html':
        echo $this->content;
        break;
      case 'application/json':
        echo json_encode($this->content);
        break;
    }

    die();
  }

  /**
   * Método de redirecionamento de páginas
   * @param ?string $url
   */
  public function redirect($url = null) {
    $url = $url? "/$url" : null;
    return header('location: '.$this->url.$url);
  }
}
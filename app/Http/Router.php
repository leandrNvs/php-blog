<?php

namespace App\Http;

use \Exception;

class Router {

  /**
   * Prefixo da rota
   * @var string
   */
  private $prefix;

  /**
   * Índice de rotas
   * @var array
   */
  private $routes = [];

  /**
   * Instância de Request
   * @var Request
   */
  private $request;

  /**
   * Instância de Response
   * @var Response
   */
  private $response;

  public function __construct() {
    $this->setPrefix(getenv('URL'));

    $this->request  = new Request;
    $this->response = new Response;
  }

  /**
   * Instancia a classe Router
   * @return self
   */
  public static function start() {
    return new self;
  }

  /**
   * Configura o prefixo de rota
   * @param string $url
   */
  private function setPrefix($url) {
    $this->prefix = parse_url($url)['path'] ?? '';
  }

  /**
   * Cria uma rota de GET
   * @param string $route
   * @param array $params
   * @return self
   */
  public function get($route, $params) {
    $this->addRoute('get', $route, $params);
    return $this;
  }

  /**
   * Cria uma rota de POST
   * @param string $route
   * @param array $params
   * @return self
   */
  public function post($route, $params) {
    $this->addRoute('post', $route, $params);
    return $this;
  }

  /**
   * Cria uma rota de PUT
   * @param string $route
   * @param array $params
   * @return self
   */
  public function put($route, $params) {
    $this->addRoute('put', $route, $params);
    return $this;
  }

  /**
   * Cria uma rota de DELETE
   * @param string $route
   * @param array $params
   * @return self
   */
  public function delete($route, $params) {
    $this->addRoute('delete', $route, $params);
    return $this;
  }

  /**
   * Adiciona items ao índice de rotas
   * @param string $httpMethod
   * @param string $route
   * @param array $params
   */
  private function addRoute($httpMethod, $route, $params) {
    $route = str_replace('/', '\/', $route);
    $route = '/^'.$route.'$/';

    $args = [];

    if(preg_match_all('/:(\w+)/', $route, $matches)) {
      $route = preg_replace('/:\w+/', '(\w+)', $route);
      $args = $matches[1];
    }

    $this->routes[$route][$httpMethod]['params'] = $args;
    $this->routes[$route][$httpMethod]['methods'] = 
      is_array($params[0])? $params : [$params] ;
  }

  /**
   * Retorn a URI da rota sem o prefixo
   * @return string
   */
  private function getUri() {
    return str_replace($this->prefix, '', $this->request->getUri());
  }

  /**
   * Retorna o método da rota corrente
   * @return array
   */
  private function getRoute() {
    $httpMethod = $this->request->getHttpMethod();
    $uri = $this->getUri();

    foreach($this->routes as $pattern => $items) {
      if(preg_match($pattern, $uri, $matches)) {
        unset($matches[0]);

        if(isset($items[$httpMethod])) {
          $items[$httpMethod]['params'] = array_combine($items[$httpMethod]['params'], $matches);
          return $items[$httpMethod];
        }

        throw new Exception('Method not allowed', 405);
      }
    }

    throw new Exception('URL not found', 404);
  }

  /**
   * Executa o roteamento
   */
  public function listen() {
    try {
      $methods = $this->getRoute();

      $this->request->params = $methods['params'] ?? [];
      unset($methods['params']);

      $methods['request']  = $this->request;
      $methods['response'] = $this->response;

      Middleware::start($methods);
    } catch (Exception $e) {
      $this->response
        ->status($e->getCode())
        ->content($e->getMessage())
        ->sendResponse();
    }
  }
}
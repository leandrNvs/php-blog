<?php

namespace App\Http;

use \ReflectionMethod;

class Middleware {

  /**
   * Índica de middlewares que serão aplicados a todas as rotas
   * @param array
   */
  private static $globals = [];

  /**
   * Índice de elementos a serem executados
   * @param array
   */
  private $middlewares = [];

  /**
   * Inicia a classe e mescla os middlewares globais com os métodos e middlewares de rota
   * @param array $methods
   */
  public function __construct($methods) {
    $methods['methods'] = array_merge(self::$globals, $methods['methods']);
    $this->middlewares = $methods; 
  }

  /**
   * Cria uma instância de Middleware
   * @param array $methods
   * @return self
   */
  public static function start($methods) {
    return (new self($methods))->next();
  }

  /**
   * Configura os middlewares globais
   * @param array $middlewares
   */
  public static function config($middlewares) {
    self::$globals = $middlewares;
  }

  /**
   * Executa o próximo item da fila de execução
   */
  public function next() {
    $method = array_shift($this->middlewares['methods']);

    $m = $this;
    $next = function() use($m) {
      $m->next();
    };

    $this->middlewares['next'] = $next;

    $args = [];

    $reflection = new ReflectionMethod($method[0], $method[1]);

    foreach($reflection->getParameters() as $param) {
      $args[$param->name] = $this->middlewares[$param->name];
    }

    return call_user_func_array($method, $args);
  }
}
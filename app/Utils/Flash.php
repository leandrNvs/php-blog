<?php

namespace App\Utils;

class Flash {

  const ERR = "error";
  const OK = "success";
  const FORM_ERR = "form-error";
  const FORM_DATA = "form-data";

  /**
   * Define o status da resposta HTTP
   * @param int $status
   */
  public static function setStatus($status) {
    $_SESSION["statusHttp"] = $status;
  }
 
  /**
   * Retorna o status HTTP e apaga a $_SESSION correspondente
   * @return ?int
   */
  public static function getStatus() {
    $status = $_SESSION["statusHttp"] ?? null;

    unset($_SESSION["statusHttp"]);

    return $status;
  }

  /**
   * Define o status e a mensagem de um toast
   * @param string $stauts (success|error)
   * @param string $message
   */
  public static function setToast($status, $message) {
    $_SESSION["toast"]["status"] = $status;
    $_SESSION["toast"]["message"] = $message;
  }

  /**
   * Retorna os dados para serem usados no toast
   * @return array
   */
  public static function getToast() {
    $status = $_SESSION["toast"]["status"] ?? null;
    $message = $_SESSION["toast"]["message"] ?? null;

    unset($_SESSION["toast"]);

    return ["toast-status" => $status, "toast-message" => $message];
  }

  /**
   * Define um flash para casos genéricos
   * @param string $flashName
   * @param mixed $content
   */
  public static function setFlash($flashName, $content) {
    $_SESSION[$flashName] = $content;
  }

  /**
   * Retorna os dados de um flash e apaga a $_SESSION correspondente
   * @param string
   * @return mixed
   */
  public static function getFlash($flashName) {
    $data = $_SESSION[$flashName] ?? null;
    unset($_SESSION[$flashName]);
    return $data;
  }
}
<?php

namespace App\Utils;

use \App\Database\Database;

class Validate {

  /**
   * Mensagens de erro
   * @var array
   */
  const ERROR_MESSAGES = [
    "required"     => "O campo %s é obrigatório",
    "letters"      => "O campo %s deve conter apenas letras",
    "numeric"      => "O campo %s deve conter apenas números",
    "email"        => "o email não é valido",
    "min"          => "O campo %s dever conter no mínimo %s caracteres",
    "max"          => "O campo %s deve conter no máximo %s caracteres",
    "between"      => "O campo %s deve estar entre %s e %s caracteres",
    "range"        => "O campo %s deve estar entre %s e %s",
    "same"         => "O campo %s deve ser igual ao campo %s",
    "alphanumeric" => "O campo %s deve conter apenas letras e números",
    "unique"       => "%s já está em uso"
  ];

  /**
   * Inicia a validação dos dados
   * @param array $rules
   * @param array $data
   * @return array
   */
  public static function validate($rules, $data) {
    $errors = [];

    foreach($rules as $ruleField => $ruleValue)  {
      $ruleValues = self::split("|", $ruleValue);

      if(!in_array("required", $ruleValues) && !isset($data[$ruleField])) {
        continue;
      }

      foreach($ruleValues as $rule) {
        self::verify($errors, $rule, $ruleField, $data);
      }
    }

    return count($errors)
      ? ["status" => false, "errors" => $errors]
      : ["status" => true, "data" => $data];
  }

  /**
   * Verifica se os dados estão de acordo com as regras
   * @param array &$erros
   * @param string $rule
   * @param string $field
   * @param array $data
   */
  private static function verify(&$errors, $rule, $field, $data) {
    if(array_key_exists($field, $errors)) return;

    $args = strpos($rule, ":")? self::split(":", $rule) : [];

    $rule = $args? $args[0] : $rule;

    $args = end($args);
    $args = strpos($args, ",")? self::split(",", $args) : $args;

    $params = [];
    $params["value"] = $data[$field] ?? null;
    $params["field"] = $field;
    $params["args"]  = $args;
    $params["data"]  = $data;

    $err = call_user_func([self::class, $rule], $params);

    if($err) {
      $paramsErr = [$field];
      $args = is_array($args)? $args : [$args];
      $paramsErr = array_merge($paramsErr, $args);

      $errors[$field] = sprintf(self::ERROR_MESSAGES[$rule], ...$paramsErr);
    }
  }

  /**
   * Verifica se o valor foi preenchido
   * @param array $args
   * @return bool
   */
  private static function required($args) {
    $field = $args["field"];

    return !isset($args["data"][$field]);
  }

  /**
   * Verifica se o valor contém apenas letras
   * @param array $args
   * @return bool
   */
  private static function letters($args) {
    $value = $args["value"];

    return !preg_match("/^[a-z\ ]*$/i", $value);
  }

  /**
   * Verifica se o valor é numérico
   * @param array $args
   * @return bool
   */
  private static function numeric($args) {
    $value = $args["value"];

    return !is_numeric($value)? true : false;
  }

  /**
   * Verifica se o valor possui letras e números
   * @param array $args
   * @return bool
   */
  private static function alphanumeric($args) {
    $value = $args["value"];

    return !preg_match("/^[\w\s]*$/i", $value);
  }

  /**
   * Verifica se o email é válido
   * @param array $args
   * @return bool
   */
  private static function email($args) {
    $email = $args["value"];
    return !filter_var($email, FILTER_VALIDATE_EMAIL)? true : false;
  }

  /**
   * Verifica se a quantidade de caracteres respeita os limites especificados
   * @param array $args
   * @return bool
   */
  private static function between($args) {
    $params = $args["args"];

    $min = (int) $params[0];
    $max = (int) $params[1];
    $value = strlen($args["value"]);

    return $value < $min || $value > $max;
  }

  /**
   * Verifica se o valor esta dentro de uma faixa
   * @param array $args
   * @return bool
   */
  private static function range($args) {
    $params = $args["args"];

    $min = (int) $params[0];
    $max = (int) $params[1];
    $value = (int) $args["value"];

    return $value < $min || $value > $max;
  }

  /**
   * Verifica se o valor tem a quantidade de caracteres dentro do valor mínimo especificado
   * @param array $args
   * @return bool
   */
  private static function min($args) {
    $min = (int) $args["args"];
    $value = strlen($args["value"]);

    return $value < $min;
  }

  /**
   * Verifica se o valor tem a quantidade de caracteres dentro do valor máximo especificado
   * @param array $args
   * @return bool
   */
  private static function max($args) {
    $max = (int) $args["args"];
    $value = strlen($args["value"]);

    return $value > $max;
  }

  /**
   * Verifica se um campo possui valor igual a outro campo
   * @param array $args
   * @return bool
   */
  private static function same( $args) {
    $value        = $args["value"];
    $compareValue = $args["data"][$args["args"]] ?? null;

    return $value !== $compareValue;
  }

  /**
   * Verifica se o valor é único
   * @param array $args
   * @return bool;
   */
  private static function unique($args) {
    $table = $args["args"][0];
    $field = $args["args"][1];
    $value = $args["value"];

    $res = Database::connect()
      ->table($table)
      ->select($field)
      ->where($field)->equal($value)
      ->execute()
      ->fetch(\PDO::FETCH_ASSOC);

    return $res? true : false;
  }

  /**
   * Cria um array com os parâmetros da validação e limpa caracteres em branco
   * @param string $separator
   * @param string $str
   * @return array
   */
  private static function split($separator, $str) {
    return array_map("trim", explode($separator, $str));
  }
}
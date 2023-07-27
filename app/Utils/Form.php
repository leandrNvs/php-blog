<?php

namespace App\Utils;

class Form {

  /**
   * Renderiza um formulário
   * @param string $form
   * @param array $params
   * @return string
   */
  public static function render($form, $params = []) {
    $form = View::render($form, $params);

    if(preg_match("/@\(\"(put|delete|patch)\"\)/", $form, $match)) {
      $input = '<input type="hidden" name="_method" value="'.$match[1].'" />';

      return str_replace($match[0], $input, $form);
    }

    return $form;
  }

}
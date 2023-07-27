<?php

namespace App\Controllers;

use App\Utils\Flash;
use App\Utils\Validate;
use App\Models\User as UserModel;

class Auth {

  const LOGIN_RULES = [
    'email'     => 'required | email',
    'password'  => 'required | alphanumeric | between: 4,10',
  ];

  /**
   * Autentica o usuário
   */
  public static function login($request, $response) {
    $data = Validate::validate(self::LOGIN_RULES, $request->body);

    if(!$data['status']) {
      Flash::setStatus(400);
      Flash::setFlash(Flash::FORM_ERR, $data['errors']);
      Flash::setFlash(Flash::FORM_DATA, $request->body);
      Flash::setToast(Flash::ERR, 'Error on sign in');

      return $response->redirect('sign-in');
    }

    $data = $data['data'];

    $user = UserModel::getUserByEmail(['email' => $data['email']], 'iduser, password');

    if(!$user || $user->password !== $data['password']) {
      Flash::setStatus(400);
      Flash::setFlash(Flash::FORM_ERR, $data['errors']);
      Flash::setFlash(Flash::FORM_DATA, $request->body);
      Flash::setToast(Flash::ERR, 'Email and/or password incorrect(s)');

      return $response->redirect('sign-in');
    }

    self::setLogin($user->getId());
    
    Flash::setStatus(200);
    Flash::setToast(Flash::OK, 'You logged in');
    return $response->redirect();
  }

  /**
   * Cria as variáveis de sessão para armazenar dados de login do usuário
   */
  private static function setLogin($id) {
    $_SESSION['user']['id'] = $id;
    $_SESSION['user']['isLogged'] = true;
  }

  /**
   * Destroi as variáveis de sessão do usuário
   */
  public static function logout() {
    unset($_SESSION['user']);
  }
}
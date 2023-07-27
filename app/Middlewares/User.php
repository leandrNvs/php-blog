<?php

namespace App\Middlewares;

use App\Utils\Flash;

class User {
  
  /**
   * Permite acesso apenas com login
   */
  public static function loginIsRequired($request, $response, $next) {
    if(isset($_SESSION['user']['isLogged'])) {
      return $next();
    }

    Flash::setStatus(401);
    Flash::setToast(FLASH::ERR, 'You need to be logged in');

    return $response->redirect('sign-in');
  }

  /**
   * Verifica se o usuário está realizado a operação sobre seu próprio ID
   */
  public static function verifyUserPermission($request, $response, $next) {
    $userId = $_SESSION['user']['id'];

    if(isset($request->params['userId']) && $userId != $request->params['userId']) {
      Flash::setStatus(403);
      Flash::setToast(FLASH::ERR, 'Permission denied');
      return $response->redirect();
    }

    if(isset($request->body['iduser']) && $userId != $request->body['iduser']) {
      Flash::setStatus(403);
      Flash::setToast(FLASH::ERR, 'Permission denied');
      return $response->redirect();
    }

    return $next();
  }

  /**
   * Verifica se o arquivo enviado pelo usuário bate com os requisitos
   */
  public static function handleUserProfile($request, $response, $next) {
    $file = $request->files['profile'];
    $type = strrchr($file['name'], '.');
    $dir = dirname(dirname(__DIR__)).'/resources/images/users/';

    $filename = md5(time());
    $filename = $filename.$type;

    if(move_uploaded_file($file['tmp_name'], $dir.$filename)) {
      $request->body['profile'] = $filename;
    }

    $next();
  }
}
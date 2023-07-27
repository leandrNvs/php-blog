<?php

namespace App\Middlewares;

use App\Utils\Flash;

use App\Models\Post as PostModel;

class Post {

  /**
   * Verifica se o usuário logado é dono do post a ser manipulado
   */
  public static function verifyOwner($request, $response, $next) {
    $postId = $request->params['postId'] ?? $request->body['idpost'];
    $userId = $_SESSION['user']['id'];

    $post = PostModel::findById(['idpost' => $postId], 'iduser');

    if($post->getAuthor() === $userId) {
      return $next();
    }

    Flash::setStatus(403);
    Flash::setToast(FLASH::ERR, 'Permission denied');

    return $response->redirect();
  }

}
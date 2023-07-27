<?php

namespace App\Controllers;

use App\Utils\Flash;
use App\Utils\Validate;
use App\Models\Post as PostModel;

class Post {

  const INSERT_RULES = [
    'iduser' => 'required | numeric',
    'title'  => 'required | alphanumeric | max: 30',
    'text'   => 'required |  max: 2000'
  ];

  const UPDATE_RULES = [
    'iduser' => 'required | numeric',
    'idpost' => 'required | numeric',
    'title'  => 'alphanumeric | max: 30',
    'text'   => 'max: 2000'
  ];

  /**
   * Cria um novo post
   */
  public static function create($request, $response) {
    $data = Validate::validate(self::INSERT_RULES, $request->body);

    if(!$data["status"]) {
      Flash::setStatus(400);
      Flash::setToast(Flash::ERR, "Error on create post");
      Flash::setFlash(Flash::FORM_ERR, $data["errors"]);
      Flash::setFlash(Flash::FORM_DATA, $request->body);

      return $response->redirect('write');
    }

    $post = PostModel::create($data["data"]);

    Flash::setToast(Flash::OK, "Your post has been created");

    return $response->redirect();
  }

  /**
   * Altera os dados de um post
   */
  public static function update($request, $response) {
    $data = Validate::validate(self::UPDATE_RULES, $request->body);
;
    if(!$data['status']) {
      Flash::setStatus(400);
      Flash::setFlash(Flash::FORM_ERR, $data['errors']);
      Flash::setFlash(Flash::FORM_DATA, $request->body);
      Flash::setToast(Flash::ERR, 'Error on update');

      return $response->redirect('post/update/'.$request->body['idpost']);
    }

    $data = $data['data'];

    $post = PostModel::findById(['idpost' => $data['idpost']], 'idpost, title, text');

    $post->title = $data['title'] ?? $post->title;
    $post->text  = $data['text']  ?? $post->text;
    
    if($post->save('idpost')) {
      Flash::setStatus(200);
      Flash::setToast(Flash::OK, 'Your post has been updated');

      return $response->redirect('account');
    }

    Flash::setStatus(200);
    Flash::setToast(Flash::OK, 'There\'are no changes');

    return $response->redirect('account');
  }

  /**
   * Apaga um post
   */
  public static function delete($request, $response) {
    $data = Validate::validate(['idpost' => 'required | numeric'], $request->body);

    if(!$data['status']) {
      Flash::setStatus(500);
      Flash::setToast(Flash::ERR, 'Error on delete your post');

      return $response->redirect();
    }

    $post = PostModel::deleteById($data['data']);

    if(!$post) {
      Flash::setStatus(500);
      Flash::setToast(Flash::ERR, 'Error on delete your post');

      return $response->redirect('account');
    }

    Flash::setStatus(200);
    Flash::setToast(Flash::OK, 'Your post has been deleted');

    return $response->redirect('account');
  }
}
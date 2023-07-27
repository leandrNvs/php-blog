<?php

namespace App\Controllers\Pages;

use App\Utils\Page;
use App\Utils\Flash;
use App\Utils\Validate;
use App\Utils\View;
use App\Utils\Form;

use App\Models\Post as PostModel;
use App\Models\User as UserModel;

class Post {

  /**
   * Pagina de criação de post
   */
  public static function register($response) {
    $autorId = $_SESSION["user"]["id"];

    $formErr  = Flash::getFlash(Flash::FORM_ERR);
    $formData = Flash::getFlash(Flash::FORM_DATA);
    $toast    = Flash::getToast(Flash::ERR);
    $status   = Flash::getStatus() ?? 200;

    $form = Form::render("post/forms/create", [
      "err-title" => $formErr["title"] ?? "",
      "err-text"  => $formErr["text"]  ?? "",
      "authorId" => $autorId,
      "title"    => $formData["title"] ?? "",
      "text"     => $formData["text"]  ?? "",
    ]);

    $postCreate = View::render("post/pages/register", [
      "form" => $form
    ]);

    $page = Page::render($postCreate, "Create new post", $toast);

    $response->status($status)->content($page)->sendResponse();
  }

  /**
   * Página de alteração de post
   */
  public static function updatePost($request, $response) {
    $data = Validate::validate(["postId" => "required | numeric"], $request->params);

    if(!$data["status"]) {
      Flash::setStatus(400);
      Flash::setToast(Flash::ERR, 'Error on post update');

      return $response->redirect('account');
    }

    $postId = $data["data"]["postId"];

    $post = PostModel::findById(['idpost' => $postId], 'idpost, iduser, title, text');

    if(!$post) {
      Flash::setStatus(500);
      Flash::setToast(Flash::ERR, 'Something went wrong');
    
      return $response->redirect();
    }

    $formErr  = Flash::getFlash(Flash::FORM_ERR);
    $formData = Flash::getFlash(Flash::FORM_DATA);
    $toast    = Flash::getToast(Flash::ERR);
    $status   = Flash::getStatus() ?? 200;

    $form = Form::render("post/forms/update", [
      "err-title" => $formErr["title"] ?? "",
      "err-text"  => $formErr["text"]  ?? "",
      "authorId"  => $post->getAuthor(),
      "postId"    => $post->getId(),
      "title"     => $formData["title"] ?? $post->title,
      "text"      => $formData["text"]  ?? $post->text,
    ]);

    $updatePostView = View::render("post/pages/updatePost", [
      "update-form" => $form
    ]);

    $page = Page::render($updatePostView, "Update account", $toast);

    $response->status($status)->content($page)->sendResponse(); 
  }

  /**
   * Página de exclusão de post
   */
  public static function deletePost($request, $response) {
    $data = Validate::validate(['postId' => 'required | numeric'], $request->params);

    if(!$data['status']) {
      $toast  = Flash::setToast(Flash::ERR, 'Something went wrong');
      $status = Flash::setStatus(500);

      return $response->redirect();
    }

    $deleteForm = Form::render("post/forms/delete", [
      "postId" => $data['data']['postId']
    ]);

    $confirmText = "Lorem Ipsum is simply dummy text of the printing
    and typesetting industry. Lorem Ipsum has been the industry's
    standard dummy text ever since the 1500s, when an unknown printer
    took a galley of type and scrambled it to make a type specimen book.
    It has survived not only five centuries, but also the leap into electronic
    typesetting, remaining essentially unchanged. It was popularised in the 1960s with
    the release of Letraset sheets containing Lorem Ipsum passages, and more recently
    with desktop publishing software like Aldus PageMaker including
    versions of Lorem Ipsum.";

    $box = View::render("components/confirm-box", [
      "box-title"   => "Are you sure you want to delete your post?",
      "box-text"    => $confirmText,
      "box-confirm" => $deleteForm,
      "cancel-link" => "account"
    ]); 

    $deletePostView = View::render("post/pages/deletePost", [
      "delete-box" => $box
    ]);

    $page = Page::render($deletePostView, "Delete post");

    $response->status(200)->content($page)->sendResponse();    
  }

  /**
   * Exibe um post
   */
  public static function getPost($request, $response) {
    $data = Validate::validate(['postId' => 'required | numeric'], $request->params);
    $toast = Flash::getToast(Flash::OK);

    if(!$data['status']) {
      $toast  = Flash::setToast(Flash::ERR, 'Something went wrong');
      $status = Flash::setStatus(500);

      return $response->redirect();
    }

    $post = PostModel::findById(['idpost' => $data['data']['postId']], 'idpost, iduser, title, text');

    $userId = $_SESSION['user']['id'] ?? null;

    $userFavorites = UserModel::getFavorites($userId);

    $favoriteForm = 
    in_array($post->getId(), $userFavorites)
    ? Form::render('/post/forms/unfavorite', [
      'postId' => $post->getId(),
      'userId' => $userId
    ])
    : Form::render('post/forms/favorite', [
      'postId' => $post->getId(),
      'userId' => $userId
    ]);


    $postView = View::render('post/components/post', [
      'postId' => $post->getId(),
      'title' => $post->title,
      'favorite-button' => isset($_SESSION['user']['isLogged'])
        ? $post->getAuthor() === $userId ? null : $favoriteForm
        : null,
      'text' => $post->text
    ]);

    $page = Page::render($postView, $post->title, $toast);

    $response->status(200)->content($page)->sendResponse();    
  }
}
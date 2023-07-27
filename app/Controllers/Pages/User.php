<?php

namespace App\Controllers\Pages;

use App\Utils\Page;
use App\Utils\Flash;
use App\Utils\Validate;
use App\Utils\View;
use App\Utils\Form;

use App\Models\User as UserModel;
use App\Models\Post as PostModel;

class User {

  /**
   * Página de registro do usuário
   */
  public static function register($response) {
    $toast    = Flash::getToast();
    $status   = Flash::getStatus() ?? 200;
    $formData = Flash::getFlash(Flash::FORM_DATA);
    $formErr  = Flash::getFlash(Flash::FORM_ERR);

    $form = Form::render('user/forms/create', [
      'err-name'  => $formErr['name']      ?? '',
      'err-email' => $formErr['email']     ?? '',
      'err-pass'  => $formErr['password']  ?? '',
      'err-cpass' => $formErr['cpassword'] ?? '',
      'name'  => $formData['name']      ?? '',
      'email' => $formData['email']     ?? '',
      'pass'  => $formData['password']  ?? '',
      'cpass' => $formData['cpassword'] ?? '',
    ]);

    $register = View::render('/user/pages/register', [
      'form' => $form
    ]);

    $page = Page::render($register, 'Create an account', $toast);

    $response->status($status)->content($page)->sendResponse();
  }

  /**
   * Página de login do usuário
   */
  public static function login($response) {
    $toast    = Flash::getToast();
    $status   = Flash::getStatus() ?? 200;
    $formData = Flash::getFlash(Flash::FORM_DATA);
    $formErr  = Flash::getFlash(Flash::FORM_ERR);

    $form = Form::render('user/forms/login', [
      'err-email' => $formErr['email']     ?? '',
      'err-pass'  => $formErr['password']  ?? '',
      'email' => $formData['email']     ?? '',
    ]);

    $login = View::render('user/pages/login', [
      'form' => $form
    ]);

    $page = Page::render($login, 'Sign in', $toast);

    $response->status($status)->content($page)->sendResponse();
  }

  /**
   * Página do usuário
   */
  public static function user($response) {
    $id = $_SESSION["user"]["id"];

    $user = UserModel::findById(['iduser' => $id], "iduser, name, email, bio, profile");

    $toast = Flash::getToast(Flash::OK);

    $header = View::render("user/components/header", [
      "user-id"      => $user->getId(),
      "user-name"    => $user->name,
      "user-email"   => $user->email,
      "user-bio"     => $user->bio     ?? "nothing to say yet",
      "user-profile" => $user->profile ?? "default.png"
    ]);

    $userPosts = PostModel::findMany(['iduser' => $user->getId()], 'idpost, title');

    $userPostsArr = [];

    foreach($userPosts as $post) {
      $userPostsArr[] = View::render("user/components/user-post", [
        "post-id"    => $post->getId(),
        "post-title" => $post->title
      ]);
    }

    $posts = View::render("user/components/posts", [
      "post-list" => implode(" ", $userPostsArr)
    ]);

    $userFavorites = UserModel::getUserFavorites($user->getId());

    $userFavsArr = [];

    foreach($userFavorites as $fav) {
      $userFavsArr[] = View::render("user/components/user-favorite", [
        "postId"    => $fav['idpost'],
        "post-title" => $fav['title'],
        'unfavorite-form' => Form::render('post/forms/unfavorite', [
          'postId' => $fav['idpost'],
          'userId' => $user->getId()
        ])
      ]);
    }

    $favorites = View::render("user/components/favorites", [
      'favorites' => implode('', $userFavsArr)
    ]);

    $userPage = View::render("user/pages/user", [
      "user-header"    => $header,
      "user-posts"     => $posts,
      "user-favorites" => $favorites
    ]);

    $page = Page::render($userPage, "Account", $toast);

    $response->status(200)->content($page)->sendResponse();
  }

  /**
   * Página de confirmação da exclusão da conta de usuário
   */
  public static function deleteAccount($request, $response) {
    $data = Validate::validate(['userId' => 'required | numeric'], $request->params);

    if(!$data['status']) {
      $toast  = Flash::setToast(Flash::ERR, 'Something went wrong');
      $status = Flash::setStatus(500);

      return $response->redirect();
    }

    $deleteForm = Form::render("user/forms/delete", [
      "userId" => $data['data']['userId']
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
      "box-title"   => "Are you sure you want to delete your account?",
      "box-text"    => $confirmText,
      "box-confirm" => $deleteForm,
      "cancel-link" => "account"
    ]); 

    $deleteUserView = View::render("user/pages/deleteAccount", [
      "delete-box" => $box
    ]);

    $page = Page::render($deleteUserView, "Delete account");

    $response->status(200)->content($page)->sendResponse();    
  }

  /**
   * Página de alteração de dados do usuário
   */
  public static function updateAccount($request, $response) {
    $data = Validate::validate(['userId' => 'required | numeric'], $request->params);

    if(!$data['status']) {
      $toast  = Flash::setToast(Flash::ERR, 'Something went wrong');
      $status = Flash::setStatus(500);

      return $response->redirect();
    }

    $data = $data['data'];

    $user = UserModel::findById(['iduser' => $data['userId']], 'iduser, name, email, password, bio, profile');

    $formErr  = Flash::getFlash(Flash::FORM_ERR);
    $formData = Flash::getFlash(Flash::FORM_DATA);
    $toast    = Flash::getToast(Flash::ERR);
    $status   = Flash::getStatus() ?? 200;

    $form = Form::render("user/forms/update", [
      "err-name"  => $formErr["name"]      ?? "",
      "err-email" => $formErr["email"]     ?? "",
      "err-pass"  => $formErr["password"]  ?? "",
      "err-cpass" => $formErr["cpassword"] ?? "",
      "err-bio"   => $formErr["bio"]       ?? "",
      "userId" => $data["userId"]       ?? "",
      "name"  => $formData["name"]      ?? "",
      "email" => $formData["email"]     ?? "",
      "pass"  => $formData["password"]  ?? "",
      "cpass" => $formData["cpassword"] ?? "",
      "bio"   => $formData["bio"]       ?? "",
      "name-placeholder"  => $user->name,
      "email-placeholder" => $user->email,
      "pass-placeholder"  => preg_replace("/./", "*", $user->password),
      "bio-placeholder"   => $user->bio ?? "Nothing to say yet"
    ]);

    $updateUserView = View::render("user/pages/updateAccount", [
      "update-form" => $form
    ]);

    $page = Page::render($updateUserView, "Update account", $toast);

    $response->status($status)->content($page)->sendResponse();
  }
}
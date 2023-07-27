<?php

namespace App\Controllers\Pages;

use App\Utils\Page;
use App\Utils\Flash;
use App\Utils\Validate;
use App\Utils\View;
use App\Utils\Form;

use App\Controllers\Auth;

use App\Models\Post as PostModel;
use App\Models\User as UserModel;

class Pages {

  /**
   * Renderiza a página inicial
   */
  public static function home($request, $response) {
    $page = $request->params['page'] ?? 1;
    $page = (int) $page;
    $limit = 6;

    $posts = PostModel::getPosts(($page - 1) * $limit, $limit);
    $totalPosts = PostModel::count('idpost');

    $postArr = [];

    foreach($posts as $post) {
      $postArr[] = View::render('components/home-post', [
        'post-id'     => $post['idpost'],
        'post-title'  => $post['title'],
        'post-author' => $post['name'],
        'post-date'   => $post['createdAt'],
        'post-text'   => substr($post['text'], 0, 300).'...'
      ]);
    }

    $paginate = [];
    $qtdPages = ceil($totalPosts / $limit);

    for($x = 0; $x < $qtdPages; $x++) {
      $v = $x + 1;
      $active = $page == $v? 'active' : null;
      $paginate[] = "<a href='page/$v' class='home__paginate__button $active'>$v</a>";
    }

    $toast  = Flash::getToast();
    $status = Flash::getStatus() ?? 200;

    $userId = $_SESSION['user']['id'] ?? null;

    $user = UserModel::findById(['iduser' => $userId], 'profile, bio');
    
    $home = View::render('pages/home', [
      'posts'    => implode('', $postArr),
      'paginate' => implode('', $paginate),
      'bio'      => isset($_SESSION['user']['isLogged']) ? $user->bio ?? 'nothing to say yet' : 'You need to log in',
      'profile'  => $user->profile ?? 'default.png'
    ]);

    $page = Page::render($home, 'Home', $toast);

    $response->status($status)->content($page)->sendResponse();
  }

  /**
   * Realiza o logout do sistema
   */
  public static function logout($response) {
    Auth::logout();
    return $response->redirect();
  }

}
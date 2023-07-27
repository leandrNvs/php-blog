<?php

namespace App\Controllers;

use App\Utils\Flash;
use App\Utils\Validate;

use App\Models\User as UserModel;
use App\Models\Post as PostModel;

class User {

  const INSERT_RULES = [
    'name'      => 'required | letters',
    'email'     => 'required | email | unique: user,email',
    'password'  => 'required | alphanumeric | between: 4,10',
    'cpassword' => 'required | alphanumeric | between: 4,10 | same: password',
  ];

  const UPDATE_RULES = [
    'iduser'    => 'required | numeric',
    'name'      => 'letters',
    'email'     => 'email | unique: user,email',
    'password'  => 'alphanumeric | between: 4,10 | same: cpassword',
    'cpassword' => 'alphanumeric | between: 4,10 | same: password',
  ];

  const FAVORITE_RULES = [
    'iduser' => 'required | numeric',
    'idpost' => 'required | numeric',
  ];

  public static function create($request, $response) {
    $data = Validate::validate(self::INSERT_RULES, $request->body);

    if(!$data['status']) {
      Flash::setStatus(400);
      Flash::setFlash(Flash::FORM_ERR, $data['errors']);
      Flash::setFlash(Flash::FORM_DATA, $request->body);
      Flash::setToast(Flash::ERR, 'Error on register');

      return $response->redirect('register');
    }

    unset($data['data']['cpassword']);

    $user = UserModel::create($data['data']);
    
    if($user) {
      Flash::setStatus(201);
      Flash::setToast(Flash::OK, 'Your account has been created');
      return $response->redirect('sign-in');
    }
  }

  /**
   * Apaga um usuário
   */
  public static function delete($request, $response) {
    $data = Validate::validate(['iduser' => 'required | numeric'], $request->body);

    if(!$data['status']) {
      Flash::setStatus(500);
      Flash::setToast(Flash::ERR, 'Error on delete your account');

      return $response->redirect();
    }

    $userProfile = UserModel::findById($data['data'], 'profile');
    $profileDir = dirname(dirname(__DIR__)).'/resources/images/users/';

    unlink($profileDir.$userProfile->profile);

    $user = UserModel::deleteById($data['data']);

    if(!$user) {
      Flash::setStatus(500);
      Flash::setToast(Flash::ERR, 'Error on delete your account');

      return $response->redirect();
    }

    Flash::setStatus(200);
    Flash::setToast(Flash::OK, 'Your account has been deleted');

    return $response->redirect('logout');
  }

  /**
   * Altera os dados do usuário
   */
  public static function update($request, $response) {
    $data = Validate::validate(self::UPDATE_RULES, $request->body);

    if(!$data['status']) {
      Flash::setStatus(400);
      Flash::setFlash(Flash::FORM_ERR, $data['errors']);
      Flash::setFlash(Flash::FORM_DATA, $request->body);
      Flash::setToast(Flash::ERR, 'Error on update');

      return $response->redirect('account/update/'.$request->body['iduser']);
    }

    $data = $data['data'];

    $user = UserModel::findById(['iduser' => $data['iduser']], 'iduser, name, email, password, bio, profile');

    if(isset($data['profile'])) {
      $profileDir = dirname(dirname(__DIR__)).'/resources/images/users/';
      unlink($profileDir.$user->profile);
    }

    $user->name     = $data['name']     ?? $user->name;
    $user->email    = $data['email']    ?? $user->email;
    $user->password = $data['password'] ?? $user->password;
    $user->bio      = $data['bio']      ?? $user->bio;
    $user->profile  = $data['profile']  ?? $user->profile;
    
    if($user->save('iduser')) {
      Flash::setStatus(200);
      Flash::setToast(Flash::OK, 'Your account has been updated');

      return $response->redirect('account');
    }

    Flash::setStatus(200);
    Flash::setToast(Flash::OK, 'There\'are no changes');

    return $response->redirect('account');
  }

  /**
   * Favorita um post
   */
  public static function favorite($request, $response) {
    $data = Validate::validate(self::FAVORITE_RULES, $request->body);

    if(!$data['status']) {
      Flash::setStatus(400);
      Flash::setToast(Flash::ERR, 'Error on save favorite');

      return $response->redirect();
    }

    $data = $data['data'];

    $fav = UserModel::addFavorite($data);

    Flash::setStatus(200);
    Flash::setToast(Flash::OK, 'Post saved as favorite');

    return $response->redirect('post/'.$data['idpost']);
  }

  /**
   * Desfavorita um post
   */
  public static function unfavorite($request, $response) {
    $data = Validate::validate(self::FAVORITE_RULES, $request->body);

    if(!$data['status']) {
      Flash::setStatus(400);
      Flash::setToast(Flash::ERR, 'Error on remove favorite');

      return $response->redirect();
    }

    $data = $data['data'];

    $fav = UserModel::addUnfavorite($data);

    Flash::setStatus(200);
    Flash::setToast(Flash::OK, 'Post removed from favorites');

    return $response->redirect('post/'.$data['idpost']);
  }
}
<?php
session_start();

require __DIR__.'/vendor/autoload.php';

use App\Common\Enviroment;
Enviroment::load(__DIR__);

use App\Http;
use App\Middlewares;
use App\Controllers;
use App\Controllers\Pages;
use App\Database\Database;

Http\Middleware::config([]);

$router = Http\Router::start();

$router->get('/', [Pages\Pages::class, 'home']);

$router->get('/page/:page', [Pages\Pages::class, 'home']);

$router->get('/register', [Pages\User::class, 'register']);

$router->get('/sign-in', [Pages\User::class, 'login']);

$router->get('/logout', [Pages\Pages::class, 'logout']);

$router->get('/account', [
  [Middlewares\User::class, 'loginIsRequired'],
  [Pages\User::class, 'user']
]);

$router->get('/account/delete/:userId', [
  [Middlewares\User::class, 'loginIsRequired'],
  [Middlewares\User::class, 'verifyUserPermission'],
  [Pages\User::class, 'deleteAccount']
]);

$router->get('/account/update/:userId', [
  [Middlewares\User::class, 'loginIsRequired'],
  [Middlewares\User::class, 'verifyUserPermission'],
  [Pages\User::class, 'updateAccount']
]);

$router->get('/post/update/:postId', [
  [Middlewares\User::class, 'loginIsRequired'],
  [Middlewares\Post::class, 'verifyOwner'],
  [Pages\Post::class, 'updatePost']
]);

$router->get('/post/delete/:postId', [
  [Middlewares\User::class, 'loginIsRequired'],
  [Middlewares\Post::class, 'verifyOwner'],
  [Pages\Post::class, 'deletePost']
]);

$router->get('/post/:postId', [Pages\Post::class, 'getPost']);

$router->get('/write', [
  [Middlewares\User::class, 'loginIsRequired'],
  [Pages\Post::class, 'register']
]);

$router->post('/user', [Controllers\User::class, 'create']);

$router->post('/user/favorite', [
  [Middlewares\User::class, 'loginIsRequired'],
  [Controllers\User::class, 'favorite']
]);

$router->post('/auth/login', [Controllers\Auth::class, 'login']);

$router->post('/post', [
  [Middlewares\User::class, 'loginIsRequired'],
  [Controllers\Post::class, 'create']
]);

$router->delete('/user', [
  [Middlewares\User::class, 'loginIsRequired'],
  [Middlewares\User::class, 'verifyUserPermission'],
  [Controllers\User::class, 'delete']
]);

$router->delete('/post', [
  [Middlewares\User::class, 'loginIsRequired'],
  [Middlewares\Post::class, 'verifyOwner'],
  [Controllers\Post::class, 'delete']
]);

$router->delete('/user/unfavorite', [
  [Middlewares\User::class, 'loginIsRequired'],
  [Controllers\User::class, 'unfavorite']
]);

$router->put('/user', [
  [Middlewares\User::class, 'loginIsRequired'],
  [Middlewares\User::class, 'verifyUserPermission'],
  [Middlewares\User::class, 'handleUserProfile'],
  [Controllers\User::class, 'update']
]);

$router->put('/post', [
  [Middlewares\User::class, 'loginIsRequired'],
  [Middlewares\Post::class, 'verifyOwner'],
  [Controllers\Post::class, 'update']
]);

$router->listen();
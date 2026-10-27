<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

// Tantangan: GET /
$router->get('/', function () {
    return response()->json([
        'service_name' => 'PHP Service App',
        'status'       => 'Running',
    ]);
});

// Latihan routing
$router->get('/hello-lumen', function () {
    return "<h1>Lumen</h1><p>Hi good developer, thanks for using Lumen</p>";
});

$router->get('/hello-lumen/{name}', function ($name) {
    return "<h1>Lumen</h1><p>Hi <b>" . $name . "</b>, thanks for using Lumen</p>";
});

// Latihan middleware
$router->get('/scores', ['middleware' => 'login', function () {
    return "<h1>Selamat</h1><p>Nilai anda 100</p>";
}]);

// Tantangan: GET /users dan GET /users/{userId}
$router->get('/users', 'UsersController@index');
$router->get('/users/{userId}', 'UsersController@show');

// Rest CRUD (modul): Post
$router->get('/posts', 'PostsController@index');
$router->post('/posts', 'PostsController@store');
$router->get('/post/{id}', 'PostsController@show');
$router->put('/post/{id}', 'PostsController@update');
$router->delete('/post/{id}', 'PostsController@destroy');

// Rest CRUD (Tantangan): Student
$router->get('/students', 'StudentsController@index');
$router->post('/students', 'StudentsController@store');
$router->get('/students/{id}', 'StudentsController@show');
$router->put('/students/{id}', 'StudentsController@update');
$router->delete('/students/{id}', 'StudentsController@destroy');

// API (prefix /api, controller di folder Api)
$router->group(['prefix' => 'api', 'namespace' => 'Api'], function () use ($router) {

    // Posts
    $router->get('posts', 'PostsController@index');
    $router->get('posts/stats', 'PostsController@stats');
    $router->get('posts/{id}', 'PostsController@show');
    $router->post('posts', 'PostsController@store');
    $router->put('posts/{id}', 'PostsController@update');
    $router->delete('posts/{id}', 'PostsController@destroy');

    // Comments (nested di posts)
    $router->get('posts/{postId}/comments', 'CommentsController@index');
    $router->post('posts/{postId}/comments', 'CommentsController@store');
    $router->get('comments/{id}', 'CommentsController@show');
    $router->put('comments/{id}', 'CommentsController@update');
    $router->delete('comments/{id}', 'CommentsController@destroy');

    // Users
    $router->get('users', 'UsersController@index');
    $router->get('users/{id}', 'UsersController@show');
    $router->post('users', 'UsersController@store');
    $router->put('users/{id}', 'UsersController@update');
    $router->delete('users/{id}', 'UsersController@destroy');

    // Categories
    $router->get('categories', 'CategoriesController@index');
    $router->get('categories/{id}', 'CategoriesController@show');
    $router->post('categories', 'CategoriesController@store');
    $router->put('categories/{id}', 'CategoriesController@update');
    $router->delete('categories/{id}', 'CategoriesController@destroy');

    // Tags
    $router->get('tags', 'TagsController@index');
    $router->get('tags/{id}', 'TagsController@show');
    $router->post('tags', 'TagsController@store');
    $router->put('tags/{id}', 'TagsController@update');
    $router->delete('tags/{id}', 'TagsController@destroy');
});
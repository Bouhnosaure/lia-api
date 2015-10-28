<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {

    $api->group(['namespace' => 'App\Http\Controllers\Api'], function ($api) {

        $api->post('auth/signup', 'JwtController@signup');
        $api->post('auth/signin', 'JwtController@signin');
        $api->post('auth/refresh', 'JwtController@refresh');

        /*
         * Routes protected by JWT
         */
        $api->group(['middleware' => ['api.auth'], 'providers' => ['jwt'], 'protected' => true], function ($api) {

            /*
             * User Resources
             */
            //settings
            $api->get('users/settings', ['as' => 'users.equipments', 'uses' => 'UserController@getSettings']);
            $api->post('users/settings', ['as' => 'users.equipments', 'uses' => 'UserController@setSettings']);

            //user
            $api->get('users/me', ['as' => 'users.me', 'uses' => 'UserController@authenticatedUser']);
            $api->get('users', ['as' => 'users.index', 'uses' => 'UserController@index']);
            $api->post('users', ['as' => 'users.store', 'uses' => 'UserController@store']);
            $api->get('users/{id}', ['as' => 'users.show', 'uses' => 'UserController@show']);
            $api->put('users/{id}', ['as' => 'users.update', 'uses' => 'UserController@update']);
            $api->delete('users/{id}', ['as' => 'users.destroy', 'uses' => 'UserController@destroy']);

        });
    });
});

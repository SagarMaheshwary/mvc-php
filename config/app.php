<?php

/**
 * Config values for our application.
 * 
 * @return array
 */
return [

    /**
     * Application config details.
     */
    'app' => [
        'name' => 'My MVC App',
        'url'  => 'localhost'
    ],

    /**
     * Database Credentials.
     */
    'database' => [
        'host'     => '127.0.0.1',
        'name'     => 'myapp',
        'username' => 'root',
        'password' => '',
        'port'     => '3306',
        'options'  => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        ]
    ],

    /**
     * Cookies
     */
    'cookie' => [
        'csrf_token' => 'token'
    ],

    /**
     * Session
     */
    'session' => [
        'csrf_token' => 'csrf_token',
    ],

];
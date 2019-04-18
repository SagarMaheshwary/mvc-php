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
        'name'     => 'mvcblog',
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
        
    ],

    /**
     * Session
     */
    'session' => [
        'name' => 'my_session',
    ]

];
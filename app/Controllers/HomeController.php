<?php

namespace App\Controllers;

use App\Core\Http\Request;

class HomeController
{

    public function index()
    {
        echo '<ul>
            <li><a href="'.url('/').'">home</a></li>
            <li><a href="'.url('/user/21/name/johnjoe').'">user</a></li>
        </ul><hr/>HomeController index method
        <form method="post" action="'.url('/hello?data=hello').'">
            <input name="hello" value="hello world">
            <input type="submit">
        </form>';
    }

    public function show($id,$name)
    {
        echo '<ul>
            <li><a href="'.url('/').'">home</a></li>
            <li><a href="'.url('/user/21/name/johnjoe').'">user</a></li>
        </ul><hr/>HomeController show method';
        echo "<br> id: {$id} , name: {$name}";
    }

    public function hello()
    {
        echo 'From POST: '.Request::input('hello');
        echo '<br>From GET: '.Request::input('data');
        echo '<hr/>HomeController hello method';
    }
}
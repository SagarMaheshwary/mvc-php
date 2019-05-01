# MVC With Plain PHP
- An mvc app built on php without using any packages.

# Usage

- [Config](#config)
- [Routes, Controllers, and Views](#routes-controllers-and-views)
- [Models and Database](#models-and-database)
- [Request and Response](#request-and-response)
- [Session and Cookies](#session-and-cookies)
- [Helpers](#helpers)
- [Security](#security)

## Config
Config array is register in the app container inside the app/Core/init.php and the values are loaded from config/app.php file. This config file has all the credentials related to app, database, session, and cookies. To retrieve a certain config value you can use the Config class:

```php
    <?php

    use App\Core\Support\Config;

    Config::get('database.name');

    //you can also use this global helper.
    config('database.name');

```

You can use dots to go deep into the arrays. **database** is the name of the array and **name** is the key inside that array.

## Routes Controllers and Views
Router for this mvc also support dynamic uris and currently only two types of methods are allowed, GET and POST. Routes can be created in the **routes/routes.php** file and you can also specify a different routes file to **Router::load()** method inside **app/Core/init.php** file.

To create a route, you can do:
```php
    $router->method('/url/with/{dynamicvalue}','Controller@method');
```

When you pass dynamic values to the route then you need to inject the $request and $response objects in the controller method first otherwise they are optional. Here's an example:
```php
    $router->get('/users/{id}','UsersController@show');
```

Controller method:
```php
    public function show(Request $request,Response $response,$id)
    {
        echo "The user id is: {$id}";
    }
```

A route for creating a post request will be:
```php
    $router->post('/users','UsersController@store');
```

All controllers will be stored in **app/Controllers** directory. This directory already has a **Controller.php** file which has all the methods like view() and include(). This controller needs to be extended by every controller. Syntax for a simple controller is defined below:
```php
    <?php

    namespace App\Controllers;

    use App\Core\Http\{Request,Response};

    class SimpleController extends Controller
    {
        public function index()
        {
            //your code...
        }

        public function store(Request $request,Response $response)
        {
            //with request and response objects...
        }
    }
```

You can use **view()** method which takes in two parameters, first view name/path and second data array that needs to be passed to that view. Just like laravel you can use dot sytax e.g if the **profile** view is in the **users** directory then you will do:
```php
    public function index()
    {
        return $this->view('users.profile');
    }
```
Returning view with data:
```php
    $this->view('users.profile',[
        'username' => $username,
        'email' => $email,
    ]);
```

**views** directory will be used for storing views. inside a view, you can use include() method for including a view and php for loops and conditionals:

```php
    <?php $this->include('includes.header') ?>
        Username: <?= $username ?>
        Email: <?= $email ?>
    <?php $this->include('includes.footer') ?>
```
We are including header and footer view files from the includes directory. **$username** and **$email** variables are coming from the above controller method.

## Models and Database
**app/Models** directory will have all the models and all the models extend **Model** class from **app/Core/Database** directory:

```php
    <?php

    namespace App\Models;

    use App\Core\Database\Model;

    class Post extends Model
    {
        protected $table = "posts";

        protected $pk = "id";
    }
```
There are two protected properties **$table** and **$pk**. $table is used for specifying the table that needs to be queried and $pk is the primary key which optional and will be used by QueryBuilder class that has all the database methods (it's extended by Model class). By default $pk has the "id" value but if you have different primary key column then you can specify it inside your model.

With QueryBuilder, you can do quite a lot stuff:
```php
    Post::all(); //return all the rows.

    Post::find(1); //return one row matching the primary key value.
```

To create a row you can use **create()** method which takes in an array with column names are the keys and values for their values:
```php
    Post::create([
        'title' => 'post title one',
        'body' => 'this is body post',
    ]);
```

**update()** method is used for updating a row and it's second parameter is the primary key value:
```php
    Post::update([
        'title' => 'post title updated',
        'body' => 'post body updated',
    ],$id);
```

You can also use where clause(s):
```php
    Post::where('title','=','title one')->get();
```

you need to call **get()** or **first()** method after all these methods except **all()**. get() method will retreive multiple rows and first() will retrieve only first row from the results.
```php
    Post::where('title','=','title one')->first();
```

You can use certain operators in where clause(s):

- = (equals to)
- > (less than)
- < (less than)
- >= (less than or equals to)
- <= (greater than or equals to)
- <> (not equals to)
- LIKE (sql like equivalent)

Certain use cases for where:

```php

    // where(column,operator,value)
    Post::where('title','=','title one')->whereOr('title','=','title two')->get(); //SQL where OR

    Post::where('title','=','title one')->whereAnd('title','=','title two')->first(); //SQL where AND

    Post::where('title','LIKE','%t%')->get(); //SQL where LIKE

    //you can also use
    Post::whereLike('title','%t%')->first();

    // whereBetween(column,['value1',value2'])
    Post::whereBetween('created_at',['2019-03-01','2019-04-30'])->get(); //SQL where BETWEEN

```

> Note that you can chain multiple whereOr(), whereLike(), and whereAnd() methods if you need.

You can also specify certain columns instead of retrieving all the columns from the table:
```php

    Post::select(['title'])->get(); //all rows with only title column.

    Post::select(['title'])->where('title','=','title one')->get();
    
```

Sometimes you want to query the table without a model and for that you can use table() method for:
```php
    use App\Core\Database\QueryBuilder; // import the class.

    QueryBuilder::table('mytable')->all();
```
Just like above, you should specify table() first in every method chain. If you have a different primary key then you use primaryKey() method:
```php
    QueryBuilder::table('mytable')->primaryKey('id')->all();
```

## Request and Response
Will be added soon!

## Session and Cookies
Will be added soon!

## Helpers
Will be added soon!

## Security
Will be added soon!
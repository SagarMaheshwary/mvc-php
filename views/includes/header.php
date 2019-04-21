<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body{
            font-family: 'Montserrat', sans-serif !important;
        }
   
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }

        main {
            flex: 1 0 auto;
        }
    </style>
    <link rel="stylesheet" href="<?= url('/css/materialize.min.css') ?>">
    <title>MVC App</title>
</head>
<body class="grey-text text-darken-2">
    <header>
        <nav class="nav-wrapper purple lighten-1">
            <div class="container">
                <ul class="show-on-med-and-small">
                    <li class="waves-effect">
                        <a href="#" data-target="mobile-nav" class="sidenav-trigger waves-effect">
                            <i class="material-icons">menu</i>
                        </a>
                    </li>
                </ul>
                <a href="<?= url('/') ?>" class="brand-logo">MVC App</a>
                <ul class="right hide-on-med-and-down">
                    <li>
                        <a href="<?= url('/') ?>">Home</a>
                    </li>
                    <li>
                        <a href="<?= url('/contact') ?>">Contact</a>
                    </li>
                    <li>
                        <a href="<?= url('/about') ?>">About</a>
                    </li>
                </ul>
                <ul class="sidenav" id="mobile-nav">
                    <li>
                        <a href="<?= url('/') ?>" class="waves-effect">
                            <i class="material-icons">home</i>
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/contact') ?>" class="waves-effect">
                            <i class="material-icons">chrome_reader_mode</i>
                            Contact
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/about') ?>" class="waves-effect">
                            <i class="material-icons">info_outline</i>
                            About
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <main>
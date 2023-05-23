<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.1/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.3/js/bootstrap.min.js"></script>
    <title>Панель администратора</title>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #e2f2ff;">
    <div class="container-fluid">
        <a class="navbar-brand">Панель администратора</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            @if(session('isAuth'))
                <div class="navbar-nav">
                    <a class="nav-link active" aria-current="page" href="{{ route('index-restaurants') }}">Управление ресторанами</a>
                </div>
                <div class="navbar-nav">
                    <a class="nav-link active" aria-current="page" href="{{ route('index-users') }}">Управление пользователями</a>
                </div>
                <div class="navbar-nav">
                    <a class="nav-link active" aria-current="page" href="{{ route('index-menus') }}">Управление меню</a>
                </div>
                <div class="navbar-nav">
                    <a class="nav-link active" aria-current="page" href="{{ route('index-dishes') }}">Управление блюдами</a>
                </div>
                <div class="navbar-nav">
                    <a class="nav-link active" aria-current="page" href="{{ route('index-categories') }}">Управление категориями</a>
                </div>
            @endif
        </div>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            @if(session('isAuth'))
                <div class="navbar-nav ms-auto">
                    <a class="nav-link active" aria-current="page" href="{{ url('/admin/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Выйти</a>
                </div>
            @endif
        </div>
        <form id="logout-form" action="{{ url('/admin/logout') }}" class="d-none" method="POST">
            @csrf
        </form>
    </div>
</nav>
<div class="container mb-5">
    @yield('main')
</div>
</body>
</html>

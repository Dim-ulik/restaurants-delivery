@extends('layout')
@section('main')
    <form class="mt-5" method="POST" action="{{ route('login') }}">
        @csrf
        <div class="row g-3 ps-5 pe-5">
            <h1>
                Войдите в панель администратора
            </h1>
            <div class="col-12">
                <label for="login" class="visually-hidden">Login</label>
                <input type="text" class="form-control" id="login" placeholder="Login" name="login" required>
            </div>
            <div class="col-12">
                <label for="password" class="visually-hidden">Password</label>
                <input type="password" class="form-control" id="password" placeholder="Password" name="password" required>
            </div>
            @if($errors->any())
                <div class="alert alert-danger col-12">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="col-12">
                <button type="submit" class="btn btn-primary mb-3">Войти</button>
            </div>
        </div>
    </form>
@endsection


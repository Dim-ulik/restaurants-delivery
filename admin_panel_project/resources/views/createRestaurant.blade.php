@extends('layout')
@section('main')
    <div class="mt-5 card">
        <div class="card-header">
            Создание ресторана
        </div>
        <form method="post" action="{{ route('store-restaurant') }}" class="card-body">
            @csrf
            <div class="form-group">
                <label for="name">Название ресторана<span class="text-danger"> *</span></label>
                <input type="text" class="form-control mt-2" id="name" name="name" required>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Создать</button>
                <a href="{{ route('index-restaurants') }}" class="btn btn-danger ms-2">Отменить</a>
            </div>
            @if($errors->any())
                <div class="alert alert-danger col-12 mt-3">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </form>
    </div>
@endsection

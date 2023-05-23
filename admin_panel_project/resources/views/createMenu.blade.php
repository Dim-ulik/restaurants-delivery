@extends('layout')
@section('main')
    <div class="mt-5 card">
        <div class="card-header">
            Создание меню
        </div>
        <form method="post" action="{{ route('create-menu') }}" class="card-body">
            @csrf
            <div class="form-group">
                <label for="name">Название меню<span class="text-danger"> *</span></label>
                <input type="text" class="form-control mt-2" id="name" name="name" required>
                <label for="restaurantId" class="mt-3">Ресторан<span class="text-danger"> *</span></label>
                <select class="form-select mt-2" name="restaurantId" id="restaurantId">
                    <option value="0" selected>Выберите ресторан</option>
                    @foreach($restaurants as $restaurant)
                        <option value="{{ $restaurant->id }}">{{ $restaurant->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Создать</button>
                <a href="{{ route('index-menus') }}" class="btn btn-danger ms-2">Отменить</a>
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

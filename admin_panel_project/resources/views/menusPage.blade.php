@extends('layout')
@section('main')
    <div class="mt-3">
        @if(session('success'))
            <div class="alert alert-success col-12 mt-3">
                {{ session('success') }}
            </div>
        @endif
        <div class="mb-3">
            <a href="{{ route('store-menu') }}" class="btn btn-success">Создать меню</a>
        </div>
        <form class="card" method="GET" action="{{ route('index-menus') }}">
            @csrf
            <div class="card-body">
                <h5 class="text-success">Показать меню для ресторана:</h5>
                <select class="form-select mt-3" name="restaurantId" onchange="this.form.submit()">
                    <option value="0" selected>Выберите ресторан</option>
                    @foreach($restaurants as $restaurant)
                        <option @if($restaurantId) @if($restaurantId == $restaurant->id) selected @endif @endif value="{{ $restaurant->id }}">{{ $restaurant->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
        @foreach($menus as $menu)
            <div class="card mt-2">
                <div class="card-header">
                    id: {{ $menu->id }}
                </div>
                <div class="card-body">
                    <h5 class="card-title">{{ $menu->name }}</h5>
                    <div>Ресторан: <span class="text-success">{{ $menu->restaurantName }}</span></div>
                    <div class="mt-3">
                        <a href="{{ route('edit-menu', $menu->id) }}" class="btn btn-primary">Редактировать</a>
                        <form action="{{ route('destroy-menu', $menu->id) }}" method="POST" class="d-inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger ms-2" onClick="return confirm('Вы уверены, что хотите удалить меню?')">Удалить меню</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

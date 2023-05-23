@extends('layout')
@section('main')
    <div class="mt-5 card">
        <div class="card-header">
            Редактирование меню
            <form action="{{ route('destroy-menu', $menu->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-warning mt-3" onClick="return confirm('Вы уверены, что хотите удалить меню?')">Удалить меню</button>
            </form>
        </div>
        <form method="post" action="{{ route('update-menu', $menu->id) }}" class="card-body">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Название меню<span class="text-danger"> *</span></label>
                <input type="text" class="form-control mt-2" id="name" name="name" required value="{{ $menu->name }}">
                <label for="restaurantId" class="mt-3">Ресторан<span class="text-danger"> *</span></label>
                <select class="form-select mt-3" name="restaurantId" id="restaurantId">
                    <option value="0" selected>Выберите ресторан</option>
                    @foreach($restaurants as $restaurant)
                        <option value="{{ $restaurant->id }}" @if($menu->restaurant_id == $restaurant->id ) selected @endif>{{ $restaurant->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Применить</button>
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

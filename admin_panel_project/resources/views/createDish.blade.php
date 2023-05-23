@extends('layout')
@section('main')
    <div class="mt-5 card">
        <div class="card-header">
            Создание блюда
        </div>
        <form method="post" enctype="multipart/form-data" action="{{ route('create-dish') }}" class="card-body">
            @csrf
            <div class="form-group">
                <label for="name">Название блюда<span class="text-danger"> *</span></label>
                <input type="text" class="form-control mt-2" id="name" name="name" required>
                <label for="price" class="mt-3">Цена<span class="text-danger"> *</span></label>
                <input type="text" class="form-control mt-2" id="price" name="price" required>
                <label for="description" class="mt-3">Описание</label>
                <textarea class="form-control mt-2" id="description" name="description"></textarea>
                <div class="mt-3">
                    <label for="photo" class="form-label">Выберите обложку блюда</label>
                    <input class="form-control" type="file" id="photo" name="photo">
                </div>
                <div>
                    <input class="mt-3" id="isVegetarian" name="isVegetarian" value="1" type="checkbox">
                    <label for="isVegetarian" class="mt-3">Блюдо вегетарианское</label>
                </div>
                <div>
                    <input class="mt-3" id="isActive" name="isActive" value="1" type="checkbox">
                    <label for="isActive" class="mt-3">Блюдо активно</label>
                </div>
                <div>
                    <label class="mt-3" for="category">Категория</label>
                    <select class="form-select mt-2" name="category" id="category">
                        <option value="" selected>Выберите категорию</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->category }}">{{ $category->category }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mt-3" for="restaurantId">Ресторан<span class="text-danger"> *</span></label>
                    <select class="form-select mt-2" name="restaurantId" id="restaurantId">
                        <option value="" selected>Выберите ресторан</option>
                        @foreach($restaurants as $restaurant)
                            <option value="{{ $restaurant->id }}">{{ $restaurant->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Создать</button>
                <a href="{{ route('index-dishes') }}" class="btn btn-danger ms-2">Отменить</a>
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

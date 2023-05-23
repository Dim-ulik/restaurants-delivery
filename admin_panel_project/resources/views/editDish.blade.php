@extends('layout')
@section('main')
    <div class="mt-5 card">
        <div class="card-header">
            Редактирование блюда
            <form action="{{ route('destroy-dish', $dish->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-warning mt-3" onClick="return confirm('Вы уверены, что хотите удалить блюдо?')">Удалить блюдо</button>
            </form>
        </div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data" action="{{ route('update-dish', $dish->id) }}">
                @csrf
                @method('PUT')
                <img
                    src="{{ $dish->photo ? 'http://localhost:8000/storage/uploads/' . $dish->photo : 'https://avatars.mds.yandex.net/get-pdb/2800861/2b8a5ef7-141f-4f96-a398-c116a575bdc3/s1200' }}"
                    style="object-fit:cover; width:150px;height:150px; border-radius: 15px" class="mt-2 m-auto"
                    alt="photo">
                <div class="form-group">
                    <label for="name" class="mt-3">Название блюда<span class="text-danger"> *</span></label>
                    <input type="text" class="form-control mt-2" id="name" name="name" required value="{{ $dish->name }}">
                    <label for="price" class="mt-3">Цена<span class="text-danger"> *</span></label>
                    <input type="text" class="form-control mt-2" id="price" name="price" required value="{{ $dish->price }}">
                    <label for="description" class="mt-3">Описание</label>
                    <textarea class="form-control mt-2" id="description" name="description">{{ $dish->description }}</textarea>
                    <div class="mt-3">
                        <label for="photo" class="form-label">Выберите обложку блюда</label>
                        <input class="form-control" type="file" id="photo" name="photo">
                    </div>
                    <div>
                        <input class="mt-3" id="isVegetarian" name="isVegetarian" value="1" type="checkbox" @if($dish->isVegetarian == 1) checked @endif>
                        <label for="isVegetarian" class="mt-3">Блюдо вегетарианское</label>
                    </div>
                    <div>
                        <input class="mt-3" id="isActive" name="isActive" value="1" type="checkbox" @if($dish->isActive == 1) checked @endif>
                        <label for="isActive" class="mt-3">Блюдо активно</label>
                    </div>
                    <div>
                        <label class="mt-3" for="category">Категория</label>
                        <select class="form-select mt-2" name="category" id="category">
                            <option value="" selected>Выберите категорию</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->category }}" @if($category->category == $dish->category) selected @endif>{{ $category->category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mt-3" for="restaurantId">Ресторан<span class="text-danger"> *</span></label>
                        <input value="{{ $restaurantName->name }}" readonly id="restaurantId" class="form-control mt-2">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Применить</button>
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
                @if(session('success'))
                    <div class="alert alert-success col-12 mt-3">
                        {{ session('success') }}
                    </div>
                @endif
            </form>
            <div class="mt-3">
                <span>Блюдо находится в меню:</span>
                @if($currentMenus)
                    @foreach($currentMenus as $menu)
                        <form class="card mt-2" method="POST" action="{{ route('delete-dish-from-menu', [$menu->menu_id, $dish->id]) }}">
                            @csrf
                            @method('DELETE')
                            <div class="card-body row">
                                <h4 class="col-10">
                                    {{ $menu->name }}
                                </h4>
                                <div class="col-2">
                                    <button type="submit" class="btn btn-danger">
                                        Удалить из меню
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endforeach
                @endif
            </div>
        </div>
        <div class="card-footer">
            <form method="post" action="{{ route('add-dish-to-menu', $dish->id) }}">
                @csrf
                <select class="form-select mt-2" name="menuId" id="menuId">
                    <option value="" selected>Выберите меню</option>
                    @foreach($menus as $menu)
                        <option value="{{ $menu->id }}">{{ $menu->name }}</option>
                    @endforeach
                </select>
                <button class="mt-2 btn btn-primary" type="submit">Добавить</button>
            </form>
        </div>
    </div>
@endsection

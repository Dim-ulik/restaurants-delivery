@extends('layout')
@section('main')
    <div>
        <div class="mt-3">
            @if(session('success'))
                <div class="alert alert-success col-12 mt-3">
                    {{ session('success') }}
                </div>
            @endif
            <div class="mb-3">
                <a href="{{ route('store-dish') }}" class="btn btn-success">Создать блюдо</a>
            </div>
            <form class="card">
                <div class="card-body">
                    <div>
                        Показать блюда из ресторана:
                    </div>
                    <select class="form-select mt-1" name="restaurantId">
                        <option value="0" selected>Выберите ресторан</option>
                        @foreach($restaurants as $restaurant)
                            <option @if($restaurantId) @if($restaurantId == $restaurant->id) selected
                                    @endif @endif value="{{ $restaurant->id }}">{{ $restaurant->name }}</option>
                        @endforeach
                    </select>
                    <div class="mt-3">
                        Показывать блюда категорий:
                    </div>
                    <select class="form-select mt-1" multiple aria-label="multiple select example" name="category[]">
                        @foreach($categories as $category)
                            <option value="{{ $category->category }}">{{ $category->category }}</option>
                        @endforeach
                    </select>
                    <div class="mt-3">
                        <input type="checkbox" name="isActive" id="isActive" value="true" @if($checked) checked @endif>
                        <label for="isActive">Показывать только неактивные</label>
                    </div>
                    <div class="mt-3">
                        Сортировка:
                    </div>
                    <select class="form-select mt-1" name="sorting">
                        <option @if($sort == '0') selected @endif value="0">Без сортировки</option>
                        <option @if($sort == 'NameAsc') selected @endif value="NameAsc">А-Я</option>
                        <option @if($sort == 'NameDesc') selected @endif value="NameDesc">Я-А</option>
                        <option @if($sort == 'PriceDesc') selected @endif value="PriceDesc">По убыванию цены</option>
                        <option @if($sort == 'PriceAsc') selected @endif value="PriceAsc">По возрастанию цены</option>
                        <option @if($sort == 'RatingAsc') selected @endif value="RatingAsc">По возрастанию рейтинга</option>
                        <option @if($sort == 'RatingDesc') selected @endif value="RatingDesc">По убыванию рейтинга</option>
                    </select>
                    <button type="submit" class="btn btn-primary mt-3">Применить</button>
                </div>
            </form>
            @if($pagination['count'] == 0)
                <div class="alert alert-warning mt-3">
                    Блюд с такими параметрами нет
                </div>
            @endif
            <div class="row">
                @foreach($dishes as $dish)
                    <div class="col-xl-3 col-lg-4 ps-1 pe-1">
                        <div class="card mt-2" style="min-height: 620px;">
                            <div class="alert alert-warning" style="min-width:70px; position: absolute; top: 5px; left: 5px; font-weight: bold">{{ $dish->rating ?? 'Рейтинга нет' }}</div>
                            <img
                                src="{{ $dish->photo ? 'http://localhost:8000/storage/uploads/' . $dish->photo : 'https://avatars.mds.yandex.net/get-pdb/2800861/2b8a5ef7-141f-4f96-a398-c116a575bdc3/s1200' }}"
                                style="object-fit:cover; width:300px;height:300px; border-radius: 15px" class="card-img-top mt-2 m-auto"
                                alt="photo">
                            <div class="card-body">
                                <h5 class="card-title d-inline-block">
                                    {{ $dish->name }}
                                </h5>
                                @if(!$dish->isActive)
                                    <span class="text-danger">(блюдо неактивно)</span>
                                @endif
                                <div>
                                    <span class="fw-bolder">Цена: </span>
                                    <span>{{ $dish->price }}</span>
                                </div>
                                <div>
                                    <span class="fw-bolder">Описание: </span>
                                    <span>{{ App\Services\DishService::cutText($dish->description) }}</span>
                                </div>
                                <div>
                                    <span class="fw-bolder">Категория: </span>
                                    <span>{{ $dish->category }}</span>
                                </div>
                                <div>
                                    @if($dish->isVegetarian)
                                        <span class="text-success">Вегетарианское</span>
                                    @else
                                        <span class="text-danger">Невегетарианское</span>
                                    @endif
                                </div>
                                <div class="mt-3" style="position: absolute; bottom: 9px">
                                    <a href="{{ route('edit-dish', $dish->id) }}" class="btn btn-primary">Редактировать
                                        блюдо</a>
                                    <form action="{{ route('destroy-dish', $dish->id) }}" method="POST"
                                          class="d-inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger mt-2"
                                                onclick="return confirm('Вы уверены, что хотите удалить блюдо?')">
                                            Удалить блюдо
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-3">
                <nav aria-label="Page navigation example" @if($pagination['count'] == 0) class="d-none" @endif>
                    <ul class="pagination">
                        <li class="page-item @if($pagination['current'] == 1) disabled @endif"><a class="page-link"
                                                                                                  @if($pagination['current'] != 1) href="{{ $_SERVER['REQUEST_URI'] . '?' . '&page=' . ($pagination['current']-1) }}" @endif><-</a>
                        </li>
                        @for($i = 0; $i < $pagination['count']; $i++)
                            <li class="page-item @if($pagination['current'] == $i+1) active @endif"><a class="page-link"
                                                                                                       href="{{ $_SERVER['REQUEST_URI'] . '?' . '&page=' . ($i+1) }}">{{ $i+1 }}</a>
                            </li>
                        @endfor
                        <li class="page-item @if($pagination['current'] == $pagination['count']) disabled @endif"><a
                                class="page-link"
                                @if($pagination['current'] != $pagination['count']) href="{{ $_SERVER['REQUEST_URI'] . '?' . '&page=' . ($pagination['current']+1) }}" @endif>-></a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
@endsection

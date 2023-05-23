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
                <a href="{{ route('store-user') }}" class="btn btn-success">Создать пользователя</a>
            </div>
                <div class="card">
                    <div class="card-body">
                        <form method="get" action="{{ route('index-users') }}">
                            @csrf
                            <label for="name">Поиск по имени:</label>
                            <input type="text" class="form-control mt-2" id="name" name="name" onchange="this.form.submit()"
                                   @if($currentUser)value="{{ $currentUser }}"@endif>
                            <button type="submit" class="btn btn-primary mt-2">Найти</button>
                            <a href="{{ route('index-users') }}" class="btn btn-warning mt-2">Очистить поиск</a>
                        </form>
                    </div>
                </div>
            @foreach($users as $user)
                <div @if($user->isBanned) class='card mt-2  border-danger' @else class="card mt-2" @endif>
                    <div class="card-header">
                        id: {{ $user->id }}
                        @if($user->isBanned)
                            <span> - Пользователь забанен</span>
                        @endif
                    </div>
                    <div @if($user->isBanned) class='card-body text-danger' @else class="card-body" @endif>
                        <h5 class="card-title d-inline-block">{{ $user->fullName }}</h5>
                        @if($user->birthDate)
                            <span class="ms-2">({{ App\Services\UserService::dateToHuman($user->birthDate) }})</span>
                        @endif
                        <div>
                            <span class="fw-bolder">Почта: </span>
                            <span>{{ $user->email }}</span>
                        </div>
                        <div>
                            <span class="fw-bolder">Телефон: </span>
                            <span>{{ $user->phone }}</span>
                        </div>
                        <div>
                            <span class="fw-bolder">Пол: </span>
                            @if($user->gender)
                                <span>{{ App\Services\UserService::getGender($user->gender) }}</span>
                            @else
                                <span>-</span>
                            @endif
                        </div>
                        @if($user->address)
                            <div>
                                <span class="fw-bolder">Адрес: </span>
                                <span>{{ $user->address }}</span>
                            </div>
                        @endif
                        <div class="mt-3">
                            <a href="{{ route('edit-user', $user->id) }}" class="btn btn-primary">Редактировать
                                пользователя</a>
                            <form action="{{ route('destroy-user', $user->id) }}" method="POST" class="d-inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger ms-2"
                                        onclick="return confirm('Вы уверены, что хотите удалить пользователя?')">Удалить
                                    пользователя
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

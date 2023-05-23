@extends('layout')
@section('main')
    <div class="mt-5">
        <div class="card mt-2">
            <div class="card-header">
                <div>
                    Редактирование пользователя - {{ $user->fullName }}
                </div>
                <div class="mt-3">
                    <form action="{{ route('destroy-user', $user->id) }}" method="POST" class="d-inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Вы уверены, что хотите удалить пользователя?')">Удалить пользователя</button>
                    </form>
                    @if(!$user->isBanned)
                        <form action="{{ route('ban-user', $user->id) }}" method="POST" class="d-inline-block">
                            @csrf
                            @method('POST')
                            <button type="submit" class="btn btn-danger ms-2" onclick="return confirm('Вы уверены, что хотите заблокировать пользователя?')">Заблокировать пользователя</button>
                        </form>
                    @else
                        <form action="{{ route('unban-user', $user->id) }}" method="POST" class="d-inline-block">
                            @csrf
                            @method('POST')
                            <button type="submit" class="btn btn-success ms-2" onclick="return confirm('Вы уверены, что хотите разблокировать пользователя?')">Разблокировать пользователя</button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('update-user', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="fullName">Имя пользователя<span class="text-danger"> *</span></label>
                        <input type="text" class="form-control mt-2" id="fullName" name="fullName" required
                               value="{{ $user->fullName }}">
                    </div>
                    <div class="form-group mt-3">
                        <label for="birthDate">Дата рождения</label>
                        <input type="date" class="form-control mt-2" id="birthDate" name="birthDate"
                               value="{{ App\Services\UserService::dateToHuman($user->birthDate) }}">
                    </div>
                    <div class="form-group mt-3">
                        <label>Пол</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" id="male" value="male"
                                   @if($user->gender == 'male') checked @endif>
                            <label class="form-check-label" for="male">
                                Мужской
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" id="female" value="female"
                                   @if($user->gender == 'female') checked @endif">
                            <label class="form-check-label" for="female">
                                Женский
                            </label>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label for="phone">Телефон<span class="text-danger"> *</span></label>
                        <input type="tel" class="form-control mt-2" id="phone" name="phone" required
                               value="{{ $user->phone }}" placeholder="+7-XXX-XXX-XX-XX">
                    </div>
                    <div class="form-group mt-3">
                        <label for="email">Email<span class="text-danger"> *</span></label>
                        <span class="text-danger">(Внимание! Данный параметр используется пользователем для входа в систему)</span>
                        <input type="email" class="form-control mt-2" id="email" name="email" required
                               value="{{ $user->email }}">
                    </div>
                    <div class="form-group mt-3">
                        <label for="address">Адрес</label>
                        <input type="text" class="form-control mt-2" id="address" name="address"
                               value="{{ $user->address }}">
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Применить</button>
                        <a href="{{ route('index-users') }}" class="btn btn-danger ms-2">Отменить</a>
                    </div>
                </form>
                <form class="card-footer mt-3" action="{{ route('set-roles-user', $user->id) }}" method="POST">
                    @csrf
                    <div>Назначить пользователю <span class="text-success">{{ $user->fullName }}</span> роли</div>
                    <span class="form-check d-inline-block mt-2">
                        <input class="form-check-input" type="checkbox" value="Customer" id="Customer" disabled checked>
                        <label class="form-check-label" for="Customer">
                            Покупатель
                        </label>
                    </span>
                    <span class="form-check d-inline-block ms-3">
                        <input class="form-check-input" type="checkbox" value="true" id="Cook" name="Cook"
                               @foreach($roles as $role) @if($role->role == 'Cook') checked @endif @endforeach >
                        <label class="form-check-label" for="Cook">
                            Повар
                        </label>
                    </span>
                    <span class="form-check d-inline-block ms-3">
                        <input class="form-check-input" type="checkbox" value="true" id="Manager" name="Manager"
                               @foreach($roles as $role) @if($role->role == 'Manager') checked @endif @endforeach >
                        <label class="form-check-label" for="Manager">
                            Менеджер
                        </label>
                    </span>
                    <span class="form-check d-inline-block ms-3">
                        <input class="form-check-input" type="checkbox" value="true" id="Courier" name="Courier"
                               @foreach($roles as $role) @if($role->role == 'Courier') checked @endif @endforeach >
                        <label class="form-check-label" for="Courier">
                            Курьер
                        </label>
                    </span>
                    <select class="form-select mt-3" name="restaurant">
                        <option value="0" selected>Выберите ресторан</option>
                        @foreach($restaurants as $restaurant)
                            <option @if($userRestaurant) @if($userRestaurant->restaurantId == $restaurant->id) selected @endif @endif value="{{ $restaurant->id }}">{{ $restaurant->name }}</option>
                        @endforeach
                    </select>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-success">Назначить</button>
                    </div>
                </form>
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
            </div>
        </div>
    </div>
@endsection

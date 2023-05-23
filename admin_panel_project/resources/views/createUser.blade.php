@extends('layout')
@section('main')
    <div class="mt-5">
        <div class="card mt-2">
            <div class="card-body">
                <form action="{{ route('create-user') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="fullName">Имя пользователя<span class="text-danger"> *</span></label>
                        <input type="text" class="form-control mt-2" id="fullName" name="fullName" required>
                    </div>
                    <div class="form-group mt-3">
                        <label for="birthDate">Дата рождения</label>
                        <input type="date" class="form-control mt-2" id="birthDate" name="birthDate">
                    </div>
                    <div class="form-group mt-3">
                        <label>Пол</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" id="male" value="male">
                            <label class="form-check-label" for="male">
                                Мужской
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" id="female" value="female">
                            <label class="form-check-label" for="female">
                                Женский
                            </label>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label for="phone">Телефон<span class="text-danger"> *</span></label>
                        <input type="tel" class="form-control mt-2" id="phone" name="phone" required placeholder="+7-XXX-XXX-XX-XX">
                    </div>
                    <div class="form-group mt-3">
                        <label for="email">Email<span class="text-danger"> *</span></label>
                        <span class="text-danger">(Внимание! Данный параметр используется пользователем для входа в систему)</span>
                        <input type="email" class="form-control mt-2" id="email" name="email" required>
                    </div>
                    <div class="form-group mt-3">
                        <label for="password">Пароль<span class="text-danger"> *</span></label>
                        <span class="text-danger">(Внимание! Данный параметр используется пользователем для входа в систему)</span>
                        <input type="password" class="form-control mt-2" id="password" name="password" required>
                    </div>
                    <div class="form-group mt-3">
                        <label for="address">Адрес</label>
                        <input type="text" class="form-control mt-2" id="address" name="address">
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Создать</button>
                        <a href="{{ route('index-users') }}" class="btn btn-danger ms-2">Отменить</a>
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
            </div>
        </div>
    </div>
@endsection

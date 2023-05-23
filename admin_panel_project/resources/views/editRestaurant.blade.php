@extends('layout')
@section('main')
    <div class="mt-5">
        <div class="card mt-2">
            <div class="card-header">
                Редактирование ресторана "{{ $restaurant->name }}"
                <form action="{{ route('destroy-restaurant', $restaurant->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-warning mt-3" onClick="return confirm('Вы уверены, что хотите удалить ресторан?')">Удалить ресторан</button>
                </form>
            </div>
            <div class="card-body">
                <form action="{{ route('update-restaurant', $restaurant->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="name">Название ресторана<span class="text-danger"> *</span></label>
                        <input type="text" class="form-control mt-2" id="name" name="name" required value="{{ $restaurant->name }}">
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Применить</button>
                        <a href="{{ route('index-restaurants') }}" class="btn btn-danger ms-2">Отменить</a>
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

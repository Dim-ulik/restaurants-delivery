@extends('layout')
@section('main')
    <div class="mt-5">
        <div class="card mt-2">
            <div class="card-header">
                Создание категории
            </div>
            <div class="card-body">
                <form action="{{ route('create-category') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">Название категории<span class="text-danger"> *</span></label>
                        <input type="text" class="form-control mt-2" id="name" name="name" required>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Применить</button>
                        <a href="{{ route('index-categories') }}" class="btn btn-danger ms-2">Отменить</a>
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

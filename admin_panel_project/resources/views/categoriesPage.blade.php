@extends('layout')
@section('main')
    <div class="mt-3">
        @if(session('success'))
            <div class="alert alert-success col-12 mt-3">
                {{ session('success') }}
            </div>
        @endif
        <div class="mb-3">
            <a href="{{ route('store-category') }}" class="btn btn-success">Создать категорию</a>
        </div>
        @foreach($categories as $category)
            <div class="card mt-2">
                <div class="card-body">
                    <h5 class="card-title">{{ $category->category }}</h5>
                    <div class="mt-3">
                        <a href="{{ route('edit-category', $category->id) }}" class="btn btn-primary">Редактировать</a>
                        <form action="{{ route('destroy-category', $category->id) }}" method="POST" class="d-inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger ms-2" onClick="return confirm('Вы уверены, что хотите удалить категорию?')">Удалить категорию</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@extends('layout')
@section('main')
    <div class="mt-3">
        @if(session('success'))
            <div class="alert alert-success col-12 mt-3">
                {{ session('success') }}
            </div>
        @endif
        <div class="mb-3">
            <a href="{{ route('create-restaurant') }}" class="btn btn-success">Создать ресторан</a>
        </div>
        @foreach($restaurants as $restaurant)
            <div class="card mt-2">
                <div class="card-header">
                    id: {{ $restaurant->id }}
                </div>
                <div class="card-body">
                    <h5 class="card-title">{{ $restaurant->name }}</h5>
                    <div class="mt-3">
                        <a href="{{ route('edit-restaurant', $restaurant->id) }}" class="btn btn-primary">Редактировать</a>
                        <form action="{{ route('destroy-restaurant', $restaurant->id) }}" method="POST" class="d-inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger ms-2" onClick="return confirm('Вы уверены, что хотите удалить ресторан?')">Удалить ресторан</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

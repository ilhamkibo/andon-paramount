@extends('layouts.main')


@section('content')
<div class="container-fluid d-flex flex-column justify-content-center align-items-center" style="height: 80vh;">
    <img src="{{ asset('assets/image/404-error.png') }}" alt="404 Image" class="mb-4" style="width: 20%">
    <h1>404 Page Not Found!</h1>
    <a href="{{ url()->previous() }}" class="btn btn-primary mt-4">Go Back</a>
</div>
@endsection
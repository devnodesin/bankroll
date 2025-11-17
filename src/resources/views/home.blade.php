@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2>Welcome to {{ config('app.name') }}</h2>
            <p class="lead">Manage your bank transactions efficiently.</p>
        </div>
    </div>
</div>
@endsection

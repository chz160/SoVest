@extends('layouts.app')

@section('content')
    <div class="container text-center mt-5">
        <h1>Welcome to SoVest</h1>
        <p>Analyze, Predict, and Improve Your Market Insights</p>
        <div class="d-flex justify-content-center gap-3 mt-4">
            <a href="{{ url('search') }}" class="btn btn-primary">Search Stocks</a> 
            <a href="{{ url('trending') }}" class="btn btn-warning">Trending Predictions</a>
            <a href="{{ Auth::check() ? url('account') : url('login') }}" class="btn btn-success">My Account</a>
        </div>
    </div>
@endsection
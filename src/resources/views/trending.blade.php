@extends('layouts.app')

@section('title', 'Trending')

@php
    // Dummy trending predictions (Replace this with data from controller)
    $trending_predictions = [
        ['username' => 'Investor123', 'symbol' => 'AAPL', 'prediction' => 'Bullish', 'votes' => 120],
        ['username' => 'MarketGuru', 'symbol' => 'TSLA', 'prediction' => 'Bearish', 'votes' => 95],
        ['username' => 'StockSavvy', 'symbol' => 'AMZN', 'prediction' => 'Bullish', 'votes' => 75],
    ];
@endphp

@section('content')
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">SoVest</a>
            <img src="{{ asset('images/logo.png') }}" width="50px">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ url('/search') }}">Search</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/trending') }}">Trending</a></li>
                    @if (Auth::check())
                        <li class="nav-item"><a class="nav-link" href="{{ url('/account') }}">My Account</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/logout') }}">Logout</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ url('/login') }}">Login</a></li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <div class="container trending-container">
        <h2 class="text-center">Trending Predictions</h2>
        @foreach ($trending_predictions as $post)
            <div class="post-card">
                <div class="vote-section">
                    <button class="vote-btn">&#9650;</button>
                    <span class="vote-count">{{ $post['votes'] }}</span>
                </div>
                <h5>{{ $post['symbol'] }} - {{ $post['prediction'] }}</h5>
                <p>Posted by <strong>{{ $post['username'] }}</strong></p>
            </div>
        @endforeach
    </div>

@endsection

@push('styles')
    <style type="text/css">
        body {
            background-color: #2c2c2c;
            color: #d4d4d4;
        }

        .navbar {
            background-color: #1f1f1f;
        }

        .trending-container {
            max-width: 800px;
            margin: auto;
            margin-top: 30px;
        }

        .post-card {
            background: #1f1f1f;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .vote-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .vote-btn {
            background: none;
            border: none;
            color: #28a745;
            cursor: pointer;
            font-size: 1.5rem;
        }

        .vote-count {
            font-size: 1.2rem;
        }
    </style>
@endpush
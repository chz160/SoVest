@extends('layouts.app')

@section('title', $pageTitle ?? 'Trending Predictions')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/prediction.css') }}">
@endpush

@section('content')
<div class="container trending-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Trending Predictions</h2>
        <div>
            <a href="{{ route('leaderboard') }}" class="btn btn-outline-light me-2">Leaderboard</a>
            <a href="{{ route('predictions.create') }}" class="btn btn-primary">Create New Prediction</a>
        </div>
    </div>
    
    @foreach($trending_predictions as $post)
        <div class="post-card">
            <div class="card-header border-0 bg-transparent">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            {{ $post['symbol'] }} - 
                            <span class="{{ $post['prediction'] == 'Bullish' ? 'text-success' : 'text-danger' }}">
                                {{ $post['prediction'] }}
                            </span>
                        </h5>
                        
                        @if(isset($post['target_price']))
                            <small class="text-muted">Target: ${{ number_format($post['target_price'], 2) }}</small>
                        @endif
                    </div>
                    @php
                        echo renderPredictionBadge($post['accuracy']);
                    @endphp
                </div>
            </div>
            
            <div class="prediction-info mt-2">
                <div class="user-info">
                    <span>Posted by <strong>{{ $post['username'] }}</strong></span>
                    @if(isset($post['reputation_score']) && $post['reputation_score'] > 0)
                        <span class="badge bg-success reputation-badge">REP: {{ $post['reputation_score'] }}</span>
                    @endif
                </div>
                <div class="vote-section">
                    <button class="vote-btn" data-prediction-id="{{ $post['prediction_id'] ?? 0 }}">&#9650;</button>
                    <span class="vote-count">{{ $post['votes'] }}</span>
                </div>
            </div>
            
            <div class="prediction-visual" data-accuracy="{{ $post['accuracy'] ?? 0 }}"></div>
        </div>
    @endforeach
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/scoring.js') }}"></script>
@endpush
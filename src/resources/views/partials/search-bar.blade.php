{{-- 
    Reusable Search Bar Component
    
    This component can be included in any page to provide search functionality.
    
    Usage:
    1. Include this component in your page with @include('partials.search-bar')
    2. The component will automatically add required CSS and JS via @push
--}}

<div class="search-nav-container">
    <form action="{{ route('search') }}" method="GET" class="search-nav-form">
        <div class="input-group">
            <input type="text" class="form-control" name="query" 
                   id="navSearchInput" placeholder="{{ $placeholder ?? 'Search...' }}" 
                   autocomplete="off">
            <button class="btn {{ $buttonClass ?? 'btn-outline-success' }}" type="submit">
                <i class="bi bi-search"></i>
            </button>
        </div>
        <div id="navSearchSuggestions" class="nav-search-suggestions"></div>
    </form>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/search.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/search.js') }}" defer></script>
@endpush
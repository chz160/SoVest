{{--
    SoVest - Standardized Footer
    
    This file contains the standard footer used across all pages in the application.
    It includes copyright information, links, and common JavaScript.
--}}

<footer class="pt-4 my-md-5 pt-md-5 border-top">
    <div class="row">
        <div class="col-12 col-md">
            <img class="mb-2" src="{{ asset('images/logo.png') }}" alt="SoVest Logo" width="24" height="24">
            <small class="d-block mb-3 text-body-secondary">&copy; {{ date('Y') }} SoVest</small>
        </div>
        <div class="col-6 col-md">
            <h5>Features</h5>
            <ul class="list-unstyled text-small">
                <li><a class="link-secondary text-decoration-none" href="{{ route('search') }}">Stock Search</a></li>
                <li><a class="link-secondary text-decoration-none" href="{{ route('predictions.trending') }}">Trending Predictions</a></li>
                <li><a class="link-secondary text-decoration-none" href="{{ route('user.leaderboard') }}">Leaderboard</a></li>
            </ul>
        </div>
        <div class="col-6 col-md">
            <h5>Resources</h5>
            <ul class="list-unstyled text-small">
                <li><a class="link-secondary text-decoration-none" href="#" id="aboutLink" data-bs-toggle="modal" data-bs-target="#aboutModal">About SoVest</a></li>
                <li><a class="link-secondary text-decoration-none" href="#" id="privacyLink" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a></li>
                <li><a class="link-secondary text-decoration-none" href="#" id="contactLink" data-bs-toggle="modal" data-bs-target="#contactModal">Contact Us</a></li>
            </ul>
        </div>
        <div class="col-6 col-md">
            <h5>Connect</h5>
            <ul class="list-unstyled text-small">
                <li><a class="link-secondary text-decoration-none" href="#"><i class="bi bi-twitter"></i> Twitter</a></li>
                <li><a class="link-secondary text-decoration-none" href="#"><i class="bi bi-facebook"></i> Facebook</a></li>
                <li><a class="link-secondary text-decoration-none" href="#"><i class="bi bi-instagram"></i> Instagram</a></li>
            </ul>
        </div>
    </div>
</footer>

<!-- Modals -->
<div class="modal fade" id="aboutModal" tabindex="-1" aria-labelledby="aboutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title" id="aboutModalLabel">About SoVest</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>SoVest is a social platform for stock predictions and investment insights. Our mission is to democratize stock prediction by allowing users to share their predictions and build reputation based on accuracy.</p>
                <p>After becoming interested in investing at an early age, Nate and Nelson started an investment club at their Alma Mater. During this time, WallStreetBets, a subreddit dedicated to sharing stock and option adive and wins was becoming extremely popular due to the Game Stop short squeeze. Before the massive influx of users, genuinely good information and research could be found on WallStreetBets, but with the massive influx of users, it has become more
                about to Pump and Dump schemes rather than sharing quality information. SoVest has been created to give people looking for quality research a place to go, where it is impossible to fall victim to pump and dumps, because the Contributor's reputation is tied to every post.</p>
                <p>Created by Nate Pedigo and Nelson Hayslett.</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title" id="privacyModalLabel">Privacy Policy</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>SoVest takes your privacy seriously. We collect only the information necessary to provide our service and will never share your personal information with third parties without your consent.</p>
                <p>For more details, please contact us directly.</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalLabel">Contact Us</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Have questions or suggestions? Reach out to us!</p>
                <p>Email: <a href="mailto:contact@sovest.example.com">contact@sovest.example.com</a></p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle AJAX errors
    window.handleAjaxError = function(xhr, status, error) {
        console.error('AJAX Error:', status, error);
        // Display user-friendly error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger alert-dismissible fade show';
        errorDiv.innerHTML = `
            <strong>Error:</strong> Something went wrong. Please try again later.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.prepend(errorDiv);
        
        // Log error to server
        fetch('{{ route('api.search') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                type: 'ajax_error',
                status: status,
                error: error,
                url: window.location.href
            })
        }).catch(console.error);
    };
    
    // Add loading indicator to all AJAX requests
    const showLoading = function() {
        const loader = document.createElement('div');
        loader.id = 'ajax-loader';
        loader.innerHTML = '<div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>';
        loader.style.position = 'fixed';
        loader.style.top = '50%';
        loader.style.left = '50%';
        loader.style.transform = 'translate(-50%, -50%)';
        loader.style.zIndex = '9999';
        document.body.appendChild(loader);
    };
    
    const hideLoading = function() {
        const loader = document.getElementById('ajax-loader');
        if (loader) {
            loader.remove();
        }
    };
    
    // Add global AJAX handlers if jQuery is available
    if (typeof $ !== 'undefined') {
        $(document).ajaxStart(showLoading);
        $(document).ajaxStop(hideLoading);
        $(document).ajaxError(function(event, xhr, settings, error) {
            window.handleAjaxError(xhr, xhr.status, error);
        });
    }
});
</script>
@endpush
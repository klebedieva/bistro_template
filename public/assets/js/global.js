// Global cart count functionality
(function() {
    function unhideCartCount() {
        var el = document.getElementById('cartNavCount');
        if (el) { el.classList.remove('hidden'); }
    }
    document.addEventListener('DOMContentLoaded', unhideCartCount);
    window.addEventListener('load', unhideCartCount);
    // Run a few times after load to defeat late scripts
    var attempts = 0;
    var timer = setInterval(function() {
        unhideCartCount();
        attempts++;
        if (attempts > 10) clearInterval(timer);
    }, 150);
})();

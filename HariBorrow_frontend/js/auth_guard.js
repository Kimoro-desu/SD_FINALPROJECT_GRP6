// js/auth_guard.js
// Include this script in the <head> of any protected page.
(function() {
    const token = localStorage.getItem('jwt');
    if (!token) {
        // Fallback redirection to login if no token is found in localStorage
        window.location.href = 'login.html';
    }
})();

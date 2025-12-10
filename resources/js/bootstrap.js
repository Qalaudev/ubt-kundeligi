import axios from 'axios';
window.axios = axios;

// Configure axios defaults
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';

// Set up API token for authenticated requests
const apiToken = document.head.querySelector('meta[name="api-token"]');
if (apiToken && apiToken.content) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${apiToken.content}`;
}

// Set up CSRF token for web requests (non-API)
const csrfToken = document.head.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
}

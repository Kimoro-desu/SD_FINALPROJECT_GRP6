// Keep this XAMPP-relative so it works across host/port changes.
const API_BASE_URL = '/SD_FINALPROJECT_GRP6/HariBorrow_backend/api';

const api = {
    // Auth Token Management
    setToken(token) {
        localStorage.setItem('jwt', token);
    },
    getToken() {
        return localStorage.getItem('jwt');
    },
    removeToken() {
        localStorage.removeItem('jwt');
        localStorage.removeItem('user');
    },
    setUser(user) {
        localStorage.setItem('user', JSON.stringify(user));
    },
    getUser() {
        const user = localStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    },
    isAuthenticated() {
        return !!this.getToken();
    },

    // Wrapper for generic generic fetch without authentication
    async rawFetch(endpoint, options = {}) {
        // Allow callers to pass endpoints with or without the "/api" prefix
        // (API_BASE_URL already ends with "/api")
        const normalizedEndpoint = (endpoint || '').startsWith('/api/')
            ? endpoint.slice(4)
            : (endpoint || '');

        const url = `${API_BASE_URL}${normalizedEndpoint}`;
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json'
            }
        };

        const finalOptions = {
            cache: 'no-store',
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...(options.headers || {})
            }
        };

        if (options.body && typeof options.body !== 'string') {
            finalOptions.body = JSON.stringify(options.body);
        }

        try {
            const response = await fetch(url, finalOptions);
            const text = await response.text();
            let data = null;
            try {
                data = text ? JSON.parse(text) : null;
            } catch (e) {
                console.error('Failed to parse JSON:', text);
            }

            if (!response.ok || (data && data.status === 'error') || !data) {
                throw {
                    status: response.status,
                    data: data,
                    message: data?.message || text || 'API request failed'
                };
            }
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    // Wrapper for authenticated fetch (adds token)
    async authenticatedFetch(endpoint, options = {}) {
        const token = this.getToken();

        if (!token) {
            this.removeToken();
            window.location.href = 'login.php';
            throw new Error('No authentication token found');
        }

        const authOptions = {
            ...options,
            headers: {
                ...(options.headers || {}),
                'Authorization': `Bearer ${token}`
            }
        };

        try {
            return await this.rawFetch(endpoint, authOptions);
        } catch (error) {
            // Auto logout if the backend rejects the JWT token as expired or invalid
            const msg = error.message || '';
            if (msg.includes('Access denied') || msg.includes('Token expired') || msg.includes('Invalid token') || error.status === 401) {
                this.removeToken();
                window.location.href = 'login.php';
            }
            throw error;
        }
    }
};

window.api = api;
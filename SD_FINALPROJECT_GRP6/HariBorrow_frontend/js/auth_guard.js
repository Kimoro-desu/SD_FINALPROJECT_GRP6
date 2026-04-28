document.addEventListener('DOMContentLoaded', async () => {
    const currentUrl = window.location.pathname.toLowerCase();

    // Don't guard the auth pages themselves (prevents redirect loops)
    if (currentUrl.includes('login.php') || currentUrl.includes('sign_up.php')) {
        return;
    }

    // 1. Read auth state directly from localStorage (source of truth)
    const token = localStorage.getItem('jwt');
    let user = null;
    try {
        const rawUser = localStorage.getItem('user');
        user = rawUser ? JSON.parse(rawUser) : null;
    } catch (e) {
        user = null;
    }

    // 2. If there is no token at all, kick them to the login screen
    if (!token) {
        window.location.href = 'login.php';
        return;
    }

    // 3. If we are missing the user's role, fetch it from the database
    if (!user || !user.role) {
        try {
            const data = await window.api.authenticatedFetch('/api/users/profile.php');
            if (data && data.status === 'success') {
                user = {
                    id: data.profile.id,
                    name: data.profile.full_name,
                    email: data.profile.email,
                    role: data.profile.role
                };
                window.api.setUser(user);
            } else {
                window.location.href = 'login.php';
                return;
            }
        } catch (error) {
            console.error("Auth Guard Error:", error);
            window.location.href = 'login.php';
            return;
        }
    }

    // 4. THE FIX: Check if they are allowed to be on the Admin Dashboard
    const role = user.role ? user.role.trim().toLowerCase() : '';

    // If they are on admin_dashboard.php, ONLY allow admin roles
    if (currentUrl.includes('admin_dashboard') && role !== 'admin') {
        alert("Access Denied: Administrator privileges required.");
        window.location.href = 'borrower_lender_dashboard.php';
        return;
    }
});
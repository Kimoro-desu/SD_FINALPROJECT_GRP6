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

    // 3. If we are missing the user's role or profile_picture property, fetch it from the database
    if (!user || !user.role || typeof user.profile_picture === 'undefined') {
        try {
            const data = await window.api.authenticatedFetch('/api/users/profile.php');
            if (data && data.status === 'success') {
                user = {
                    id: data.profile.id,
                    name: data.profile.full_name,
                    email: data.profile.email,
                    role: data.profile.role,
                    profile_picture: data.profile.profile_picture
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

    function renderUserBadge(targetEl, name, profilePicture) {
        if (!targetEl) return;
        if (profilePicture) {
            targetEl.innerHTML = `<img src="${profilePicture}" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
            return;
        }

        const safeName = String(name || 'User').trim();
        const parts = safeName.split(/\s+/).filter(Boolean);
        const initials = ((parts[0] ? parts[0].charAt(0) : 'U') + (parts.length > 1 ? parts[parts.length - 1].charAt(0) : '')).toUpperCase();
        targetEl.textContent = initials;
    }

    // 5. Update global UI elements if they exist
    if (user) {
        const navName = document.getElementById('navUserName');
        const navAvatar = document.getElementById('navAvatar');
        const sidebarName = document.getElementById('sidebarName');
        const sidebarAvatar = document.getElementById('sidebarAvatar');
        const sidebarRole = document.getElementById('sidebarRole');
        
        if (navName) {
            navName.textContent = user.name;
        }

        if (sidebarName) {
            sidebarName.textContent = user.name;
        }

        if (sidebarRole) {
            const role = String(user.role || '').trim();
            sidebarRole.textContent = role ? (role.charAt(0).toUpperCase() + role.slice(1).toLowerCase()) : 'User';
        }

        renderUserBadge(navAvatar, user.name, user.profile_picture);
        renderUserBadge(sidebarAvatar, user.name, user.profile_picture);
    }
});
/**
 * HariBorrow Theme System
 * Clean toggle between Dark and Light modes — no canvas or particle effects.
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'hariborrow-theme';
  const DARK  = 'dark';
  const LIGHT = 'light';

  let currentTheme = DARK;

  /* ── INIT ── */
  function init() {
    currentTheme = localStorage.getItem(STORAGE_KEY) || LIGHT;
    applyTheme(currentTheme, false);

    // Remove any leftover canvas from previous version
    const oldCanvas = document.getElementById('themeCanvas');
    if (oldCanvas) oldCanvas.remove();

    // Create toggle button (if not already present)
    if (!document.getElementById('themeToggleBtn')) {
      const btn = document.createElement('button');
      btn.id = 'themeToggleBtn';
      btn.className = 'theme-toggle-btn';
      btn.setAttribute('aria-label', 'Toggle light/dark mode');
      btn.innerHTML = currentTheme === DARK
        ? '<span class="toggle-icon">☀️</span>'
        : '<span class="toggle-icon">🌙</span>';
      btn.addEventListener('click', toggle);
      document.body.appendChild(btn);
    }

    // Add mobile sidebar toggle logic
    const sidebarHeader = document.querySelector('.sidebar-header');
    const sidebar = document.querySelector('.sidebar');
    if (sidebarHeader && sidebar) {
      // Remove any existing listener to prevent duplicates
      const newHeader = sidebarHeader.cloneNode(true);
      sidebarHeader.parentNode.replaceChild(newHeader, sidebarHeader);
      newHeader.addEventListener('click', () => {
        if (window.innerWidth <= 900) {
          sidebar.classList.toggle('nav-open');
        }
      });
      
      // Auto-close sidebar when clicking a link on mobile
      const navLinks = sidebar.querySelectorAll('.nav-link, .drop-item');
      navLinks.forEach(link => {
        link.addEventListener('click', () => {
          if (window.innerWidth <= 900) {
            sidebar.classList.remove('nav-open');
          }
        });
      });
    }
  }

  /* ── THEME SWITCHING ── */
  function applyTheme(theme, save) {
    document.documentElement.setAttribute('data-theme', theme);
    currentTheme = theme;
    if (save !== false) localStorage.setItem(STORAGE_KEY, theme);
  }

  function toggle() {
    const next = currentTheme === DARK ? LIGHT : DARK;
    applyTheme(next, true);

    const btn = document.getElementById('themeToggleBtn');
    if (btn) {
      const icon = btn.querySelector('.toggle-icon');
      if (icon) {
        icon.style.transform = 'rotate(360deg) scale(0)';
        setTimeout(() => {
          icon.textContent = next === DARK ? '☀️' : '🌙';
          icon.style.transform = 'rotate(0) scale(1)';
        }, 250);
      }
    }
  }

  /* ── BOOTSTRAP ── */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Apply theme immediately (before DOM ready) to avoid flash
  const savedTheme = localStorage.getItem(STORAGE_KEY);
  if (savedTheme) {
    document.documentElement.setAttribute('data-theme', savedTheme);
  }
})();

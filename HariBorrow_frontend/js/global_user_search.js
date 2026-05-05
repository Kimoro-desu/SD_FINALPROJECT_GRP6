/**
 * HariBorrow — global user search strip + reviews modal (loaded after api.js).
 */
(function () {
    const STYLE_ID = 'hari-global-search-styles';

    function injectStyles() {
        if (document.getElementById(STYLE_ID)) return;
        const css = document.createElement('style');
        css.id = STYLE_ID;
        css.textContent = `
body.hari-with-global-search.hari-fixed-offset .top-nav:not(.hari-search-skip-offset) { top: 48px !important; }
body.hari-with-global-search.hari-fixed-offset aside.sidebar:not(.hari-search-skip-offset) {
  margin-top: 48px !important;
  height: calc(100vh - 48px) !important;
}
body.hari-with-global-search.hari-fixed-offset main.main-content:not(.hari-search-skip-offset) {
  margin-top: 48px !important;
  height: calc(100vh - 48px) !important;
  box-sizing: border-box !important;
}
body.hari-with-global-search main.dashboard:not(.hari-search-skip-offset) {
  padding-top: 188px !important;
}
body.hari-with-global-search .profile-container:not(.hari-search-skip-offset) {
  padding-top: 148px !important;
}
#hari-global-user-search-strip {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 48px;
  z-index: 10002;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 14px;
  background: rgba(12, 12, 16, 0.92);
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  font-family: 'Outfit', sans-serif;
}
#hari-global-user-search-strip .hari-gs-brand {
  color: #E5C07B;
  font-size: 13px;
  font-weight: 600;
  white-space: nowrap;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}
#hari-global-user-search-strip .hari-gs-wrap {
  flex: 1;
  max-width: 520px;
  margin: 0 auto;
  position: relative;
}
#hari-global-user-search-strip input {
  width: 100%;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px;
  padding: 8px 14px 8px 36px;
  color: #fff;
  font-size: 13px;
  outline: none;
}
#hari-global-user-search-strip input::placeholder { color: #6B665A; }
#hari-global-user-search-strip input:focus { border-color: rgba(229, 192, 123, 0.35); }
#hari-global-user-search-strip .hari-gs-ico {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #6B665A;
  font-size: 16px;
  pointer-events: none;
}
.hari-gs-dropdown {
  position: absolute;
  top: calc(100% + 6px);
  left: 0;
  right: 0;
  max-height: 280px;
  overflow-y: auto;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(15,15,22,0.98);
  box-shadow: 0 14px 40px rgba(0,0,0,0.5);
  display: none;
  z-index: 10003;
}
.hari-gs-dropdown.visible { display: block; }
.hari-gs-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  cursor: pointer;
  border-bottom: 1px solid rgba(255,255,255,0.04);
  text-align: left;
  color: #e2ddd6;
  font-size: 13px;
}
.hari-gs-item:hover { background: rgba(229, 192, 123, 0.08); }
.hari-gs-item:last-child { border-bottom: none; }
.hari-gs-avatar {
  width: 36px; height: 36px;
  border-radius: 50%;
  background: rgba(229,192,123,0.15);
  color: #E5C07B;
  display: flex; align-items: center; justify-content: center;
  font-weight: 600;
  font-size: 13px;
  flex-shrink: 0;
  overflow: hidden;
}
.hari-gs-avatar img { width: 100%; height: 100%; object-fit: cover; }
.hari-gs-meta { min-width: 0; flex: 1; }
.hari-gs-meta strong {
  display: block;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.hari-gs-meta span { font-size: 11px; color: #6B665A; }
.hari-gs-empty { padding: 12px; font-size: 12px; color: #6B665A; }
#hari-reviews-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.75);
  backdrop-filter: blur(6px);
  z-index: 10050;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 24px;
}
#hari-reviews-modal-overlay.visible { display: flex; }
#hari-reviews-modal-overlay .hari-rv-panel {
  width: 100%;
  max-width: 560px;
  max-height: 82vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  background: rgba(15, 15, 20, 0.98);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 16px;
  box-shadow: 0 24px 60px rgba(0,0,0,0.55);
}
.hari-rv-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 18px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}
.hari-rv-head h2 {
  font-size: 18px;
  font-weight: 500;
  color: #fff;
  margin: 0;
  font-family: 'Cormorant Garamond', Georgia, serif;
}
.hari-rv-close {
  background: transparent;
  border: 1px solid rgba(255,255,255,0.15);
  color: #6B665A;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  cursor: pointer;
}
.hari-rv-close:hover { border-color: #ff6b7a; color: #ff6b7a; }
.hari-rv-list {
  overflow-y: auto;
  padding: 8px 12px 16px;
}
.hari-rv-card {
  padding: 12px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.06);
  margin-bottom: 10px;
  background: rgba(255,255,255,0.02);
}
.hari-rv-card .rv-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 8px;
  margin-bottom: 6px;
}
.hari-rv-card .rv-stars { color: #E5C07B; font-size: 13px; letter-spacing: 0.06em; }
.hari-rv-card .rv-who { font-size: 12px; color: #A39E93; }
.hari-rv-card .rv-date { font-size: 11px; color: #6B665A; white-space: nowrap; }
.hari-rv-card .rv-txt { font-size: 13px; color: #e2ddd6; line-height: 1.45; margin-top: 4px; }
.hari-rv-empty { padding: 24px; text-align: center; color: #6B665A; font-size: 13px; }
.hari-rv-loading { padding: 24px; text-align: center; color: #A39E93; font-size: 13px; }
`;
        document.head.appendChild(css);
    }

    function initials(name) {
        const p = String(name || '').trim().split(/\s+/).filter(Boolean);
        const a = p[0]?.[0] || 'U';
        const b = p.length > 1 ? p[p.length - 1][0] : '';
        return (a + b).toUpperCase();
    }

    function esc(s) {
        return String(s ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    let searchTimer = null;

    function mountSearchStrip() {
        if (document.getElementById('hari-global-user-search-strip')) return;
        injectStyles();
        document.body.classList.add('hari-with-global-search', 'hari-fixed-offset');

        const strip = document.createElement('div');
        strip.id = 'hari-global-user-search-strip';
        strip.innerHTML = `
  <span class="hari-gs-brand">Find people</span>
  <div class="hari-gs-wrap">
    <span class="hari-gs-ico" aria-hidden="true">🔍</span>
    <input type="search" id="hariGlobalUserSearchInput" placeholder="Search by name, email, or ID…" autocomplete="off" />
    <div class="hari-gs-dropdown" id="hariGsDropdown" role="listbox"></div>
  </div>
`;
        document.body.insertBefore(strip, document.body.firstChild);

        const input = document.getElementById('hariGlobalUserSearchInput');
        const dd = document.getElementById('hariGsDropdown');

        function hideDd() {
            dd.classList.remove('visible');
            dd.innerHTML = '';
        }

        function goProfile(id) {
            hideDd();
            input.value = '';
            window.location.href = 'user_profile.php?id=' + encodeURIComponent(String(id));
        }

        input.addEventListener('input', function () {
            const q = input.value.trim();
            clearTimeout(searchTimer);
            if (q.length < 2) {
                hideDd();
                return;
            }
            searchTimer = setTimeout(async function () {
                try {
                    const res = await window.api.authenticatedFetch(
                        '/api/users/search.php?q=' + encodeURIComponent(q)
                    );
                    const users = Array.isArray(res.users) ? res.users : [];
                    if (!users.length) {
                        dd.innerHTML = '<div class="hari-gs-empty">No non-admin users match.</div>';
                        dd.classList.add('visible');
                        return;
                    }
                    dd.innerHTML = users
                        .map(function (u) {
                            const avInner = u.profile_picture
                                ? `<img src="${esc(u.profile_picture)}" alt="">`
                                : esc(initials(u.name));
                            return `
<div class="hari-gs-item" role="option" data-uid="${u.id}">
  <div class="hari-gs-avatar">${avInner}</div>
  <div class="hari-gs-meta">
    <strong>${esc(u.name)}</strong>
    <span>${esc(u.school_id || '—')} · ${esc(u.email || '')}</span>
  </div>
</div>`;
                        })
                        .join('');
                    dd.classList.add('visible');
                    dd.querySelectorAll('.hari-gs-item').forEach(function (el) {
                        el.addEventListener('mousedown', function (e) {
                            e.preventDefault();
                            goProfile(el.getAttribute('data-uid'));
                        });
                    });
                } catch (e) {
                    console.warn('Search failed', e);
                    hideDd();
                }
            }, 280);
        });

        document.addEventListener('click', function (e) {
            if (!strip.contains(e.target)) hideDd();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') hideDd();
        });
    }

    function mountReviewsModal() {
        if (document.getElementById('hari-reviews-modal-overlay')) return;
        injectStyles();
        const ov = document.createElement('div');
        ov.id = 'hari-reviews-modal-overlay';
        ov.innerHTML = `
<div class="hari-rv-panel" role="dialog" aria-modal="true">
  <div class="hari-rv-head">
    <h2 id="hariRvTitle">Reviews</h2>
    <button type="button" class="hari-rv-close" id="hariRvClose" aria-label="Close">×</button>
  </div>
  <div class="hari-rv-list" id="hariRvList"><div class="hari-rv-loading">Loading…</div></div>
</div>`;
        document.body.appendChild(ov);
        ov.addEventListener('click', function (e) {
            if (e.target === ov) closeReviewsModal();
        });
        document.getElementById('hariRvClose').addEventListener('click', closeReviewsModal);
    }

    function closeReviewsModal() {
        const ov = document.getElementById('hari-reviews-modal-overlay');
        if (ov) {
            ov.classList.remove('visible');
            document.getElementById('hariRvList').innerHTML = '';
        }
    }

    function formatWhen(s) {
        if (!s) return '—';
        const d = new Date(s);
        return Number.isNaN(d.getTime()) ? String(s) : d.toLocaleString();
    }

    async function openReviewsModal(userId, titleName) {
        mountReviewsModal();
        const ov = document.getElementById('hari-reviews-modal-overlay');
        const list = document.getElementById('hariRvList');
        const title = document.getElementById('hariRvTitle');
        title.textContent = titleName ? 'Reviews — ' + titleName : 'Reviews';
        list.innerHTML = '<div class="hari-rv-loading">Loading…</div>';
        ov.classList.add('visible');
        try {
            const res = await window.api.authenticatedFetch(
                '/api/users/received_reviews.php?user_id=' + encodeURIComponent(String(userId))
            );
            const revs = Array.isArray(res.reviews) ? res.reviews : [];
            if (!revs.length) {
                list.innerHTML =
                    '<div class="hari-rv-empty">No reviews yet.</div>';
                return;
            }
            list.innerHTML = revs
                .map(function (r) {
                    const rk = Math.max(1, Math.min(5, Number(r.rating) || 0));
                    const stars = '★'.repeat(rk) + '☆'.repeat(5 - rk);
                    const txt =
                        r.review_text && String(r.review_text).trim()
                            ? esc(r.review_text)
                            : '<em style="color:#6B665A;font-style:normal;">No written review.</em>';
                    return `
<div class="hari-rv-card">
  <div class="rv-top">
    <span class="rv-stars">${stars}</span>
    <span class="rv-date">${esc(formatWhen(r.created_at))}</span>
  </div>
  <div class="rv-who">From ${esc(r.rater_name)} · TXN #${esc(r.transaction_id)}</div>
  <div class="rv-txt">${txt}</div>
</div>`;
                })
                .join('');
        } catch (e) {
            list.innerHTML =
                '<div class="hari-rv-empty">' +
                esc(e?.message || e?.data?.message || 'Could not load reviews.') +
                '</div>';
        }
    }

    window.HariBorrowOpenReviewsModal = openReviewsModal;
    window.HariBorrowMountGlobalSearch = mountSearchStrip;

    function bootstrap() {
        const path = (window.location.pathname || '').toLowerCase();
        if (path.includes('login.php') || path.includes('sign_up.php')) return;
        if (!window.api || typeof window.api.getToken !== 'function' || !window.api.getToken()) return;
        mountSearchStrip();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrap);
    } else {
        bootstrap();
    }
})();

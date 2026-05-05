/**
 * HariBorrow Theme System
 * Dark mode: animated stars + moon
 * Light mode: animated sun + floating shapes
 */
(function () {
  'use strict';

  /* ── CONSTANTS ── */
  const STORAGE_KEY = 'hariborrow-theme';
  const DARK = 'dark';
  const LIGHT = 'light';

  /* ── STATE ── */
  let canvas, ctx, animId;
  let particles = [];
  let mouseX = -1000, mouseY = -1000;
  let currentTheme = DARK;

  /* ══════════════════════════════════
     INITIALISATION & ROUTING RESTRICTIONS
     ══════════════════════════════════ */
  function init() {
    // Read saved theme, default to Light
    currentTheme = localStorage.getItem(STORAGE_KEY) || LIGHT;

    applyTheme(currentTheme, false);

    // Create canvas
    canvas = document.getElementById('themeCanvas');
    if (!canvas) {
      canvas = document.createElement('canvas');
      canvas.id = 'themeCanvas';
      document.body.prepend(canvas);
    }
    ctx = canvas.getContext('2d');
    resize();

    // Create toggle button
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

    // Events
    window.addEventListener('resize', resize);
    document.addEventListener('mousemove', function (e) {
      mouseX = e.clientX;
      mouseY = e.clientY;
    });

    // Start animation
    spawnParticles();
    animate();
  }

  /* ══════════════════════════════════
     THEME SWITCHING
     ══════════════════════════════════ */
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

    // Re-create particles for new theme
    spawnParticles();
  }

  /* ══════════════════════════════════
     CANVAS SETUP
     ══════════════════════════════════ */
  function resize() {
    if (!canvas) return;
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
  }

  /* ══════════════════════════════════
     PARTICLE SYSTEM
     ══════════════════════════════════ */
  function spawnParticles() {
    particles = [];
    const w = window.innerWidth;
    const h = window.innerHeight;
    const area = w * h;

    if (currentTheme === DARK) {
      // Stars
      const starCount = Math.min(Math.floor(area / 4500), 180);
      for (let i = 0; i < starCount; i++) {
        particles.push({
          type: 'star',
          x: Math.random() * w,
          y: Math.random() * h,
          size: Math.random() * 2.2 + 0.5,
          baseAlpha: Math.random() * 0.6 + 0.2,
          alpha: 0,
          twinkleSpeed: Math.random() * 0.015 + 0.005,
          twinkleOffset: Math.random() * Math.PI * 2,
          vx: (Math.random() - 0.5) * 0.35,
          vy: (Math.random() - 0.5) * 0.25,
        });
      }
      // Moon
      particles.push({
        type: 'moon',
        x: w * 0.82,
        y: h * 0.15,
        size: Math.min(w, h) * 0.045,
        alpha: 0.92,
        vx: 0,
        vy: 0,
        phase: 0,
      });
      // Shooting stars (occasional)
      for (let i = 0; i < 3; i++) {
        particles.push(createShootingStar(w, h));
      }
    } else {
      // Sun
      particles.push({
        type: 'sun',
        x: w * 0.85,
        y: h * 0.13,
        size: Math.min(w, h) * 0.05,
        alpha: 0.85,
        vx: 0,
        vy: 0,
        rayPhase: 0,
      });
      // Floating glowing shapes and orbs
      const shapeCount = Math.min(Math.floor(area / 9000), 75);
      const shapeTypes = ['circle', 'triangle', 'diamond', 'mini-sun', 'ring', 'orb'];
      for (let i = 0; i < shapeCount; i++) {
        let isOrb = Math.random() > 0.7;
        particles.push({
          type: 'shape',
          shape: isOrb ? 'orb' : shapeTypes[Math.floor(Math.random() * shapeTypes.length)],
          x: Math.random() * w,
          y: Math.random() * h,
          size: isOrb ? Math.random() * 25 + 10 : Math.random() * 10 + 4,
          alpha: isOrb ? Math.random() * 0.08 + 0.02 : Math.random() * 0.18 + 0.04,
          baseAlpha: isOrb ? Math.random() * 0.08 + 0.02 : Math.random() * 0.18 + 0.04,
          rotation: Math.random() * Math.PI * 2,
          rotSpeed: (Math.random() - 0.5) * 0.008,
          vx: (Math.random() - 0.5) * 0.25,
          vy: (Math.random() - 0.5) * 0.2,
          floatOffset: Math.random() * Math.PI * 2,
          floatSpeed: Math.random() * 0.008 + 0.003,
        });
      }
      // Light rays / beams
      for (let i = 0; i < 6; i++) {
        particles.push({
          type: 'ray',
          angle: (Math.PI * 2 / 6) * i + Math.random() * 0.3,
          length: Math.random() * 120 + 60,
          alpha: Math.random() * 0.06 + 0.02,
          speed: Math.random() * 0.003 + 0.001,
        });
      }
    }
  }

  function createShootingStar(w, h) {
    return {
      type: 'shootingStar',
      x: Math.random() * w * 0.7,
      y: Math.random() * h * 0.4,
      size: Math.random() * 1.8 + 0.8,
      alpha: 0,
      maxAlpha: Math.random() * 0.7 + 0.3,
      vx: Math.random() * 3 + 2,
      vy: Math.random() * 1.5 + 0.8,
      life: 0,
      maxLife: Math.random() * 80 + 40,
      trail: [],
      waiting: Math.random() * 600 + 200,
    };
  }

  /* ══════════════════════════════════
     ANIMATION LOOP
     ══════════════════════════════════ */
  function animate() {
    if (!ctx || !canvas) { animId = requestAnimationFrame(animate); return; }

    ctx.clearRect(0, 0, canvas.width, canvas.height);
    const w = canvas.width;
    const h = canvas.height;
    const t = performance.now() * 0.001;

    // Light Mode: Mouse Hover Shine Effect
    if (currentTheme === LIGHT && mouseX > 0 && mouseY > 0) {
      ctx.save();
      const glowRadius = 400;
      const glow = ctx.createRadialGradient(mouseX, mouseY, 0, mouseX, mouseY, glowRadius);
      glow.addColorStop(0, 'rgba(252, 235, 175, 0.08)'); // Subtle bright sun color
      glow.addColorStop(0.3, 'rgba(229, 192, 123, 0.03)');
      glow.addColorStop(1, 'transparent');
      
      ctx.fillStyle = glow;
      ctx.globalCompositeOperation = 'screen';
      ctx.beginPath();
      ctx.arc(mouseX, mouseY, glowRadius, 0, Math.PI * 2);
      ctx.fill();
      ctx.restore();
    }

    for (let i = 0; i < particles.length; i++) {
      const p = particles[i];

      if (currentTheme === DARK) {
        drawDarkParticle(p, t, w, h, i);
      } else {
        drawLightParticle(p, t, w, h, i);
      }
    }

    animId = requestAnimationFrame(animate);
  }

  /* ── DARK MODE DRAWING ── */
  function drawDarkParticle(p, t, w, h, idx) {
    switch (p.type) {
      case 'star': {
        // Twinkle
        p.alpha = p.baseAlpha * (0.5 + 0.5 * Math.sin(t * p.twinkleSpeed * 60 + p.twinkleOffset));

        // Mouse repulsion
        const dx = p.x - mouseX;
        const dy = p.y - mouseY;
        const dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < 120) {
          const force = (120 - dist) / 120 * 0.4;
          p.x += dx / dist * force;
          p.y += dy / dist * force;
        }

        // Drift
        p.x += p.vx;
        p.y += p.vy;

        // Wrap
        if (p.x < -10) p.x = w + 10;
        if (p.x > w + 10) p.x = -10;
        if (p.y < -10) p.y = h + 10;
        if (p.y > h + 10) p.y = -10;

        // Draw star
        ctx.save();
        ctx.globalAlpha = p.alpha;
        ctx.fillStyle = '#fff';
        ctx.shadowBlur = p.size * 4;
        ctx.shadowColor = 'rgba(255, 255, 255, 0.4)';

        // 4-point star shape
        ctx.beginPath();
        const s = p.size;
        ctx.moveTo(p.x, p.y - s * 1.5);
        ctx.lineTo(p.x + s * 0.4, p.y - s * 0.4);
        ctx.lineTo(p.x + s * 1.5, p.y);
        ctx.lineTo(p.x + s * 0.4, p.y + s * 0.4);
        ctx.lineTo(p.x, p.y + s * 1.5);
        ctx.lineTo(p.x - s * 0.4, p.y + s * 0.4);
        ctx.lineTo(p.x - s * 1.5, p.y);
        ctx.lineTo(p.x - s * 0.4, p.y - s * 0.4);
        ctx.closePath();
        ctx.fill();
        ctx.restore();
        break;
      }

      case 'moon': {
        // Gentle floating
        const floatY = Math.sin(t * 0.3) * 5;
        const mx = p.x;
        const my = p.y + floatY;
        const r = p.size;

        ctx.save();
        ctx.globalAlpha = p.alpha;

        // Eclipse Corona (bright glowing outer ring)
        const corona = ctx.createRadialGradient(mx, my, r * 0.8, mx, my, r * 4);
        corona.addColorStop(0, 'rgba(252, 235, 175, 0.6)');
        corona.addColorStop(0.2, 'rgba(229, 192, 123, 0.3)');
        corona.addColorStop(0.5, 'rgba(229, 192, 123, 0.1)');
        corona.addColorStop(1, 'transparent');
        ctx.fillStyle = corona;
        ctx.beginPath();
        ctx.arc(mx, my, r * 4, 0, Math.PI * 2);
        ctx.fill();

        // Solar flares (dynamic inner glow)
        const flarePulse = Math.sin(t * 2) * 0.1 + 1;
        const flare = ctx.createRadialGradient(mx, my, r * 0.8, mx, my, r * 1.6 * flarePulse);
        flare.addColorStop(0, 'rgba(255, 255, 255, 0.8)');
        flare.addColorStop(1, 'transparent');
        ctx.fillStyle = flare;
        ctx.beginPath();
        ctx.arc(mx, my, r * 1.6 * flarePulse, 0, Math.PI * 2);
        ctx.fill();

        // Eclipse Dark Body (the moon blocking the sun)
        ctx.fillStyle = '#030304';
        ctx.shadowBlur = 15;
        ctx.shadowColor = 'rgba(252, 235, 175, 0.9)'; // bright rim light
        ctx.beginPath();
        ctx.arc(mx, my, r, 0, Math.PI * 2);
        ctx.fill();

        // Inner shadow to give the dark body depth
        ctx.shadowBlur = 0;
        const innerShadow = ctx.createRadialGradient(mx - r*0.2, my - r*0.2, r*0.4, mx, my, r);
        innerShadow.addColorStop(0, 'rgba(15, 15, 20, 0.5)');
        innerShadow.addColorStop(1, '#030304');
        ctx.fillStyle = innerShadow;
        ctx.beginPath();
        ctx.arc(mx, my, r, 0, Math.PI * 2);
        ctx.fill();

        ctx.restore();
        break;
      }

      case 'shootingStar': {
        if (p.waiting > 0) { p.waiting--; return; }

        p.life++;
        const lifeRatio = p.life / p.maxLife;

        if (lifeRatio < 0.15) {
          p.alpha = p.maxAlpha * (lifeRatio / 0.15);
        } else if (lifeRatio > 0.6) {
          p.alpha = p.maxAlpha * (1 - (lifeRatio - 0.6) / 0.4);
        } else {
          p.alpha = p.maxAlpha;
        }

        p.trail.push({ x: p.x, y: p.y, alpha: p.alpha });
        if (p.trail.length > 15) p.trail.shift();

        p.x += p.vx;
        p.y += p.vy;

        // Draw trail
        for (let j = 0; j < p.trail.length; j++) {
          const tp = p.trail[j];
          const trailAlpha = (j / p.trail.length) * tp.alpha * 0.5;
          ctx.save();
          ctx.globalAlpha = trailAlpha;
          ctx.fillStyle = '#FCEBAF';
          ctx.beginPath();
          ctx.arc(tp.x, tp.y, p.size * (j / p.trail.length), 0, Math.PI * 2);
          ctx.fill();
          ctx.restore();
        }

        // Draw head
        ctx.save();
        ctx.globalAlpha = p.alpha;
        ctx.fillStyle = '#fff';
        ctx.shadowBlur = 8;
        ctx.shadowColor = 'rgba(252, 235, 175, 0.6)';
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();

        // Reset if done
        if (p.life >= p.maxLife) {
          Object.assign(p, createShootingStar(w, h));
        }
        break;
      }
    }
  }

  /* ── LIGHT MODE DRAWING ── */
  function drawLightParticle(p, t, w, h, idx) {
    switch (p.type) {
      case 'sun': {
        const floatY = Math.sin(t * 0.25) * 4;
        const sx = p.x;
        const sy = p.y + floatY;
        const r = p.size;
        p.rayPhase = t;

        ctx.save();

        // Large ambient glow
        const outerGlow = ctx.createRadialGradient(sx, sy, r, sx, sy, r * 6);
        outerGlow.addColorStop(0, 'rgba(229, 192, 123, 0.08)');
        outerGlow.addColorStop(0.4, 'rgba(229, 192, 123, 0.03)');
        outerGlow.addColorStop(1, 'transparent');
        ctx.globalAlpha = 0.7;
        ctx.fillStyle = outerGlow;
        ctx.beginPath();
        ctx.arc(sx, sy, r * 6, 0, Math.PI * 2);
        ctx.fill();

        // Inner glow
        const innerGlow = ctx.createRadialGradient(sx, sy, r * 0.5, sx, sy, r * 2.2);
        innerGlow.addColorStop(0, 'rgba(252, 235, 175, 0.2)');
        innerGlow.addColorStop(1, 'transparent');
        ctx.globalAlpha = 0.6;
        ctx.fillStyle = innerGlow;
        ctx.beginPath();
        ctx.arc(sx, sy, r * 2.2, 0, Math.PI * 2);
        ctx.fill();

        // Sun body
        ctx.globalAlpha = p.alpha;
        const bodyGrad = ctx.createRadialGradient(sx - r * 0.2, sy - r * 0.2, 0, sx, sy, r);
        bodyGrad.addColorStop(0, '#FCEBAF');
        bodyGrad.addColorStop(0.7, '#E5C07B');
        bodyGrad.addColorStop(1, '#C9A84C');
        ctx.fillStyle = bodyGrad;
        ctx.shadowBlur = 30;
        ctx.shadowColor = 'rgba(229, 192, 123, 0.4)';
        ctx.beginPath();
        ctx.arc(sx, sy, r, 0, Math.PI * 2);
        ctx.fill();

        // Sun rays (rotating triangular beams)
        ctx.shadowBlur = 0;
        const rayCount = 10;
        for (let i = 0; i < rayCount; i++) {
          const angle = (Math.PI * 2 / rayCount) * i + t * 0.12;
          const rayLen = r * (1.3 + 0.3 * Math.sin(t * 1.5 + i));
          const rayWidth = r * 0.22;

          ctx.save();
          ctx.translate(sx, sy);
          ctx.rotate(angle);
          ctx.globalAlpha = 0.3 + 0.1 * Math.sin(t * 2 + i * 0.7);

          ctx.fillStyle = '#E5C07B';
          ctx.beginPath();
          ctx.moveTo(r * 0.9, -rayWidth * 0.5);
          ctx.lineTo(r * 0.9 + rayLen, 0);
          ctx.lineTo(r * 0.9, rayWidth * 0.5);
          ctx.closePath();
          ctx.fill();
          ctx.restore();
        }

        ctx.restore();
        break;
      }

      case 'shape': {
        // Floating motion
        const floatX = Math.sin(t * p.floatSpeed * 50 + p.floatOffset) * 0.5;
        const floatY = Math.cos(t * p.floatSpeed * 40 + p.floatOffset) * 0.4;

        p.x += p.vx + floatX;
        p.y += p.vy + floatY;
        p.rotation += p.rotSpeed;

        // Breathe alpha
        p.alpha = p.baseAlpha * (0.6 + 0.4 * Math.sin(t * 1.2 + p.floatOffset));

        // Mouse interaction — gentle attraction
        const dx = mouseX - p.x;
        const dy = mouseY - p.y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < 150 && dist > 1) {
          p.x += dx / dist * 0.3;
          p.y += dy / dist * 0.3;
          p.alpha = Math.min(p.alpha * 1.8, 0.35);
        }

        // Wrap around
        if (p.x < -30) p.x = w + 30;
        if (p.x > w + 30) p.x = -30;
        if (p.y < -30) p.y = h + 30;
        if (p.y > h + 30) p.y = -30;

        ctx.save();
        ctx.translate(p.x, p.y);
        ctx.rotate(p.rotation);
        ctx.globalAlpha = p.alpha;
        ctx.strokeStyle = '#A68A48';
        ctx.fillStyle = 'rgba(166, 138, 72, 0.15)';
        ctx.lineWidth = 1;

        switch (p.shape) {
          case 'circle':
            ctx.beginPath();
            ctx.arc(0, 0, p.size, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
            break;

          case 'triangle':
            ctx.beginPath();
            ctx.moveTo(0, -p.size);
            ctx.lineTo(p.size * 0.866, p.size * 0.5);
            ctx.lineTo(-p.size * 0.866, p.size * 0.5);
            ctx.closePath();
            ctx.fill();
            ctx.stroke();
            break;

          case 'diamond':
            ctx.beginPath();
            ctx.moveTo(0, -p.size);
            ctx.lineTo(p.size * 0.6, 0);
            ctx.lineTo(0, p.size);
            ctx.lineTo(-p.size * 0.6, 0);
            ctx.closePath();
            ctx.fill();
            ctx.stroke();
            break;

          case 'mini-sun': {
            // Small sun with tiny rays
            ctx.beginPath();
            ctx.arc(0, 0, p.size * 0.5, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
            const rCount = 6;
            for (let r = 0; r < rCount; r++) {
              const a = (Math.PI * 2 / rCount) * r;
              ctx.beginPath();
              ctx.moveTo(Math.cos(a) * p.size * 0.6, Math.sin(a) * p.size * 0.6);
              ctx.lineTo(Math.cos(a) * p.size, Math.sin(a) * p.size);
              ctx.stroke();
            }
            break;
          }

          case 'ring':
            ctx.beginPath();
            ctx.arc(0, 0, p.size, 0, Math.PI * 2);
            ctx.stroke();
            ctx.beginPath();
            ctx.arc(0, 0, p.size * 0.5, 0, Math.PI * 2);
            ctx.stroke();
            break;

          case 'orb':
            ctx.shadowBlur = 15;
            ctx.shadowColor = `rgba(229, 192, 123, ${p.alpha * 2})`;
            ctx.fillStyle = `rgba(229, 192, 123, ${p.alpha})`;
            ctx.beginPath();
            ctx.arc(0, 0, p.size, 0, Math.PI * 2);
            ctx.fill();
            break;
        }

        ctx.restore();
        break;
      }

      case 'ray': {
        // Rotating rays emanating from sun position
        const sunP = particles.find(pp => pp.type === 'sun');
        if (!sunP) break;
        const sx = sunP.x;
        const sy = sunP.y + Math.sin(t * 0.25) * 4;

        p.angle += p.speed;

        ctx.save();
        ctx.translate(sx, sy);
        ctx.rotate(p.angle);
        ctx.globalAlpha = p.alpha * (0.5 + 0.5 * Math.sin(t * 0.8 + p.angle));

        const grad = ctx.createLinearGradient(sunP.size * 1.5, 0, sunP.size * 1.5 + p.length, 0);
        grad.addColorStop(0, 'rgba(229, 192, 123, 0.15)');
        grad.addColorStop(1, 'transparent');
        ctx.fillStyle = grad;

        ctx.beginPath();
        ctx.moveTo(sunP.size * 1.5, -3);
        ctx.lineTo(sunP.size * 1.5 + p.length, 0);
        ctx.lineTo(sunP.size * 1.5, 3);
        ctx.closePath();
        ctx.fill();
        ctx.restore();
        break;
      }
    }
  }

  /* ══════════════════════════════════
     BOOTSTRAP
     ══════════════════════════════════ */
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

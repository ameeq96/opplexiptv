(function () {
  'use strict';

  const PERCENTS = [5, 10, 15, 20, 25];
  const COLORS = ['#fff5f5', '#ffe1e1', '#ffc9c9', '#ffacac', '#df0303'];
  const el = (sel, ctx = document) => ctx.querySelector(sel);

  // ---------- LocalStorage ----------
  const LS = {
    get nSpins() { return +localStorage.getItem('dw.spins') || 0; },
    set nSpins(v) { localStorage.setItem('dw.spins', String(v)); },
    get claimed() { return localStorage.getItem('dw.claimed'); },
    set claimed(v) { localStorage.setItem('dw.claimed', v); },
    get closedAt() { return +localStorage.getItem('dw.closedAt') || 0; },
    set closedAt(v) { localStorage.setItem('dw.closedAt', String(v)); }
  };

  // ---------- ONLY 5% or 10% ----------
  // null => weighted 5/10; 0 => always 5%; 1 => always 10%
  const FORCE_OUTCOME = null;

  function chooseIndex(spins) {
    if (FORCE_OUTCOME === 0 || FORCE_OUTCOME === 1) return FORCE_OUTCOME; // lock
    // weighted: mostly 5%, kabhi 10%
    const weights = (spins >= 2) ? [78, 22] : [65, 35]; // [5%,10%]
    const r = Math.random() * (weights[0] + weights[1]);
    return (r < weights[0]) ? 0 : 1; // 0 => 5% , 1 => 10%
  }

  async function captureOverlayAsBlob(node) {
    // high-res capture
    const canvas = await html2canvas(node, {
      backgroundColor: null,
      scale: Math.min(2, window.devicePixelRatio || 1.5)
    });
    return new Promise(res => canvas.toBlob(b => res(b), 'image/png', 0.92));
  }

  // ---------- Wheel drawing ----------
  function drawWheel(canvas) {
    const ctx = canvas.getContext('2d');
    const W = canvas.width, H = canvas.height, cx = W / 2, cy = H / 2, r = Math.min(W, H) / 2 - 4;
    ctx.clearRect(0, 0, W, H);
    const slice = (Math.PI * 2) / PERCENTS.length;

    for (let i = 0; i < PERCENTS.length; i++) {
      const st = i * slice, en = st + slice;
      ctx.beginPath(); ctx.moveTo(cx, cy); ctx.arc(cx, cy, r, st, en); ctx.closePath();
      ctx.fillStyle = COLORS[i % COLORS.length]; ctx.fill();

      // labels
      ctx.save();
      ctx.translate(cx, cy);
      const mid = st + slice / 2;
      ctx.rotate(mid);
      ctx.textAlign = 'right';
      ctx.fillStyle = '#111';
      ctx.font = 'bold 22px system-ui,-apple-system,Segoe UI,Roboto,Arial';
      ctx.fillText(PERCENTS[i] + '%', r - 16, 8);
      ctx.restore();
    }

    // center hub
    ctx.beginPath(); ctx.arc(cx, cy, 50, 0, Math.PI * 2); ctx.fillStyle = '#fff'; ctx.fill();
    ctx.lineWidth = 2; ctx.strokeStyle = '#111'; ctx.stroke();
    ctx.fillStyle = '#111'; ctx.font = 'bold 14px system-ui,-apple-system,Segoe UI,Roboto,Arial';
    ctx.textAlign = 'center';
    const centerLabel = (window.DW_I18N && window.DW_I18N.spin_center) || 'SPIN';
    ctx.fillText(centerLabel.toUpperCase(), cx, cy + 5);
  }

  // ---------- Confetti ----------
  function confetti(ms = 4000) {
    const c = document.createElement('canvas');
    c.style.position = 'fixed'; c.style.inset = '0'; c.style.pointerEvents = 'none'; c.style.zIndex = '100000';
    document.body.appendChild(c);
    const ctx = c.getContext('2d'); let W, H; const rs = () => { W = c.width = innerWidth; H = c.height = innerHeight; };
    rs(); addEventListener('resize', rs);
    const count = Math.min(300, Math.floor(W * H / 12000));
    const parts = Array.from({ length: count }, () => ({
      x: Math.random() * W, y: -20 - Math.random() * H * .3, s: 5 + Math.random() * 7,
      a: Math.random() * Math.PI, v: 1.5 + Math.random() * 3.5, w: Math.random() * .2 + .05,
      c: COLORS[Math.floor(Math.random() * COLORS.length)]
    }));
    const t0 = performance.now();
    (function loop(t) {
      ctx.clearRect(0, 0, W, H);
      parts.forEach(p => {
        p.y += p.v + (Math.random() * 2); p.x += Math.sin(p.a += p.w) * .8;
        ctx.save(); ctx.translate(p.x, p.y); ctx.rotate(p.a * 2); ctx.fillStyle = p.c; ctx.fillRect(-p.s / 2, -p.s / 2, p.s, p.s); ctx.restore();
      });
      if (t - t0 < ms) requestAnimationFrame(loop); else { document.body.removeChild(c); removeEventListener('resize', rs); }
    })(t0);
  }

  // ---------- Init ----------
  function init() {
    const overlay = el('#dw-overlay'); if (!overlay) return;
    const canvas = el('#dw-canvas', overlay), btnSpin = el('#dw-spin', overlay), btnClose = overlay.querySelector('.dw-close');
    const resBox = el('#dw-result', overlay), resVal = el('#dw-result-value', overlay), btnCopy = el('#dw-copy', overlay), note = el('#dw-note', overlay);

    drawWheel(canvas);

    let spinning = false; const N = PERCENTS.length, sliceDeg = 360 / N;

    function show() { overlay.classList.add('show'); overlay.setAttribute('aria-hidden', 'false'); }
    function hide() { overlay.classList.remove('show'); overlay.setAttribute('aria-hidden', 'true'); LS.closedAt = Date.now(); }
    window.DiscountWheel = { show, hide };

    const twelveH = 12 * 60 * 60 * 1000;
    if (!LS.claimed && (Date.now() - LS.closedAt > twelveH)) { setTimeout(show, 1000); }

    btnClose.addEventListener('click', hide);
    overlay.addEventListener('click', e => { if (e.target === overlay) hide(); });

    btnSpin.addEventListener('click', () => {
      if (spinning) return;

      btnSpin.hidden = true;
      const i18n = window.DW_I18N || {};
      if (LS.claimed) {
        const already = (i18n.already_unlocked || 'Discount already unlocked: :value%').replace(':value', LS.claimed);
        note.textContent = already;
        resVal.textContent = LS.claimed + '%';
        resBox.hidden = false; confetti(2500); return;
      }

      spinning = true;
      note.textContent = i18n.good_luck || 'Good luck!';
      btnSpin.disabled = true;

      // >>>>>>> ONLY 5% or 10% <<<<<<<
      const index = chooseIndex(LS.nSpins);     // 0 => 5% , 1 => 10%
      const discount = PERCENTS[index];

      // pointer at top (12-o'clock = 270deg). Stop EXACTLY at center of chosen slice.
      const sliceCenter = (index + 0.5) * sliceDeg;
      const pointerDeg = 270;
      const base = pointerDeg - sliceCenter;
      const extra = 360 * (4 + Math.floor(Math.random() * 2));  // 4–5 spins
      const target = extra + base;                               // no jitter => perfect center
      canvas.style.transform = `rotate(${target}deg)`;

      setTimeout(() => {
        LS.nSpins = LS.nSpins + 1;
        resVal.textContent = discount + '%';
        resBox.hidden = false;
        LS.claimed = String(discount);
        note.textContent = i18n.congrats || 'Congratulations! Your discount is ready.';
        confetti(3800);
        spinning = false; btnSpin.disabled = true;
      }, 6100);
    });

    btnCopy.addEventListener('click', () => {
      const d = LS.claimed || '5';
      const phone = btnCopy.dataset.waPhone || '16393903194';
      const tmpl = btnCopy.dataset.waTemplate || 'Hello, I got :discount% discount. Can you activate my subscription?';
      const msg = tmpl.replace(':discount', d);
      const url = `https://wa.me/${phone}?text=${encodeURIComponent(msg)}`;

      // close popup then navigate
      if (typeof window.DiscountWheel?.hide === 'function') {
        window.DiscountWheel.hide();
      } else {
        overlay.classList.remove('show');
        overlay.setAttribute('aria-hidden', 'true');
        LS.closedAt = Date.now();
      }
      setTimeout(() => { window.location.href = url; }, 120);
    });
  }

  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init, { once: true }); }
  else { init(); }
})();

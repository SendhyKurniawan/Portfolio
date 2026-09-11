// After a minute with no input, the page naps behind a bouncing chrome logo, like a 2000 desktop.
// Skipped while typing, while a log is open, in background tabs and for reduced-motion visitors.
(() => {
  const IDLE_MS = 60000;
  const SPEED = 120; // pixels per second
  const FADE_MS = 300;
  const TINTS = ['#4cc3df', '#ffb066', '#c7a6f5', '#b6ec6a', '#ff9bb3'];
  const WAKE_EVENTS = ['pointermove', 'pointerdown', 'keydown', 'wheel', 'touchstart', 'scroll'];
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

  let idleTimer = 0;
  let frame = 0;
  let saver = null;
  let logo = null;
  let pos = { x: 0, y: 0 };
  let dir = { x: 1, y: 1 };
  let lastTime = 0;
  let tintIndex = 0;

  function canNap() {
    if (reduceMotion.matches || document.hidden) return false;
    if (document.querySelector('.modal.show')) return false;
    const active = document.activeElement;
    return !(active && active.matches('input, textarea, select'));
  }

  function schedule() {
    clearTimeout(idleTimer);
    idleTimer = setTimeout(start, IDLE_MS);
  }

  function start() {
    if (!canNap()) {
      schedule();
      return;
    }
    saver = document.createElement('div');
    saver.className = 'screensaver';
    saver.setAttribute('aria-hidden', 'true');
    logo = document.createElement('div');
    logo.className = 'screensaver-logo';
    logo.textContent = 'KURSE CO.';
    const hint = document.createElement('p');
    hint.className = 'screensaver-hint';
    hint.textContent = window.matchMedia('(pointer: coarse)').matches ? 'Tap to wake up' : 'Move the mouse to wake up';
    saver.append(logo, hint);
    document.body.appendChild(saver);

    pos = {
      x: Math.random() * Math.max(0, saver.clientWidth - logo.offsetWidth),
      y: Math.random() * Math.max(0, saver.clientHeight - logo.offsetHeight),
    };
    dir = { x: Math.random() < 0.5 ? -1 : 1, y: Math.random() < 0.5 ? -1 : 1 };
    lastTime = performance.now();
    frame = requestAnimationFrame(step);
  }

  function bounce(axis, max) {
    if (pos[axis] > 0 && pos[axis] < max) return false;
    dir[axis] = pos[axis] <= 0 ? 1 : -1;
    pos[axis] = Math.min(Math.max(pos[axis], 0), max);
    return true;
  }

  function step(now) {
    const seconds = Math.min((now - lastTime) / 1000, 0.05);
    lastTime = now;
    pos.x += dir.x * SPEED * seconds;
    pos.y += dir.y * SPEED * seconds;
    const hitX = bounce('x', saver.clientWidth - logo.offsetWidth);
    const hitY = bounce('y', saver.clientHeight - logo.offsetHeight);
    if (hitX || hitY) {
      // New glow colour on every wall hit, like the logo that never quite reaches the corner
      tintIndex = (tintIndex + 1) % TINTS.length;
      logo.style.setProperty('--tint', TINTS[tintIndex]);
    }
    logo.style.transform = `translate(${pos.x}px, ${pos.y}px)`;
    frame = requestAnimationFrame(step);
  }

  function wake() {
    if (saver && !saver.classList.contains('is-waking')) {
      cancelAnimationFrame(frame);
      // Fade out before removing, so the click that woke the page doesn't land on a link underneath
      const closing = saver;
      closing.classList.add('is-waking');
      setTimeout(() => closing.remove(), FADE_MS);
      saver = null;
    }
    schedule();
  }

  WAKE_EVENTS.forEach(type => window.addEventListener(type, wake, { passive: true }));
  document.addEventListener('visibilitychange', wake);
  schedule();
})();

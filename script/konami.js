// Up up down down left right left right B A turns the site back into 1999:
// Comic Sans, a cursor that sparkles and a page that is forever under construction.
(() => {
  const CODE = ['arrowup', 'arrowup', 'arrowdown', 'arrowdown', 'arrowleft', 'arrowright', 'arrowleft', 'arrowright', 'b', 'a'];
  const MODE_ATTRIBUTE = 'data-mode-1999';
  const SPARKLE_GAP_MS = 60;
  const SPARKLE_LIFE_MS = 700;
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

  let progress = 0;
  let lastSparkle = 0;
  let panel = null;

  function element(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text) node.textContent = text;
    return node;
  }

  function sparkle(event) {
    const now = performance.now();
    if (reduceMotion.matches || now - lastSparkle < SPARKLE_GAP_MS) return;
    lastSparkle = now;
    const star = element('span', 'sparkle-1999');
    star.setAttribute('aria-hidden', 'true');
    star.style.left = `${event.clientX}px`;
    star.style.top = `${event.clientY}px`;
    star.style.setProperty('--spin', `${Math.random() * 360}deg`);
    document.body.appendChild(star);
    setTimeout(() => star.remove(), SPARKLE_LIFE_MS);
  }

  function buildPanel() {
    const box = element('div', 'mode-1999 window');
    box.setAttribute('role', 'status');

    const bar = element('div', 'window-bar');
    const dots = element('span', 'gel-dots');
    dots.setAttribute('aria-hidden', 'true');
    dots.append(element('span'), element('span'), element('span'));
    bar.append(dots, element('span', 'window-title', '1999.exe'));

    const body = element('div', 'window-body');
    body.append(
      element('p', 'mode-1999-line', '1999 mode engaged.'),
      element('p', null, 'Sparkling cursor, Comic Sans, and a site that is permanently under construction.')
    );
    const off = element('button', 'btn-gel btn-gel--small', 'Back to 2026');
    off.type = 'button';
    off.addEventListener('click', leave);
    body.append(off);

    box.append(bar, body);
    document.body.appendChild(box);
    return box;
  }

  function enter() {
    document.documentElement.setAttribute(MODE_ATTRIBUTE, 'on');
    panel = buildPanel();
    document.addEventListener('pointermove', sparkle, { passive: true });
    window.kurseSound?.play('chime');
  }

  function leave() {
    document.documentElement.removeAttribute(MODE_ATTRIBUTE);
    document.removeEventListener('pointermove', sparkle);
    document.querySelectorAll('.sparkle-1999').forEach(star => star.remove());
    panel?.remove();
    panel = null;
  }

  document.addEventListener('keydown', event => {
    // Letters typed into the guestbook belong to the message, not to the cheat code
    if (event.target instanceof Element && event.target.closest('input, textarea, select')) return;
    const key = event.key.toLowerCase();
    if (key === CODE[progress]) {
      progress += 1;
    } else {
      progress = key === CODE[0] ? 1 : 0;
    }
    if (progress === CODE.length) {
      progress = 0;
      panel ? leave() : enter();
    }
  });
})();

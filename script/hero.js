// Hero text rotation with a glitch burst on each swap.
// Skipped entirely for visitors who prefer reduced motion.
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function rotateText(element, texts, intervalMs) {
  if (!element || prefersReducedMotion) return;
  let index = 0;

  setInterval(() => {
    if (document.hidden) return;

    element.style.animation = 'none';
    void element.offsetWidth; // restart the CSS animation

    index = (index + 1) % texts.length;
    element.setAttribute('data-text', texts[index]);
    element.textContent = texts[index];

    element.style.animation = 'glitch-anim-1 0.5s infinite';
    setTimeout(() => {
      element.style.animation = 'glitch-anim-1 3s infinite linear alternate-reverse';
    }, 500);
  }, intervalMs);
}

rotateText(document.querySelector('.hero-subtitle'), [
  "[ ARCHITECTING DIGITAL EXPERIENCES ]",
  "[ CREATIVE DEVELOPER ]",
  "[ SYSTEM ARCHITECT ]",
  "[ FULL STACK ENGINEER ]"
], 4000);

// KURNIAWAN SENDHY <-> KURSE.CO
rotateText(document.querySelector('.hero-title'), ["KURNIAWAN SENDHY", "KURSE.CO"], 5000);

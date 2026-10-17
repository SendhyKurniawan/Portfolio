// Windows open and close the 2000 way: the finished window grows out of whatever was
// clicked in a few choppy steps, and shrinks back into it on close. The modals carry
// no .fade, so Bootstrap shows and hides them at once instead of fading over the zoom.
(() => {
  const STEPS = 8;
  const still = window.matchMedia('(prefers-reduced-motion: reduce)');

  // Transform that lays a window measured at `win` exactly over `rect`
  const onto = (rect, win) =>
    `translate(${rect.left - win.left}px, ${rect.top - win.top}px) scale(${rect.width / win.width}, ${rect.height / win.height})`;

  document.querySelectorAll('.modal').forEach(modal => {
    const content = modal.querySelector('.modal-content');
    let opener = null;
    let zoom = null;
    let closing = false;
    // Bumped on every open and close, so a zoom still waiting on a picture knows it is stale
    let run = 0;

    const step = (from, to, fill = 'none') => content.animate(
      [{ transformOrigin: '0 0', transform: from }, { transformOrigin: '0 0', transform: to }],
      { duration: 300, easing: `steps(${STEPS})`, fill }
    );

    function cleanUp() {
      if (zoom) zoom.cancel();
      zoom = null;
      modal.classList.remove('is-zooming');
    }

    modal.addEventListener('show.bs.modal', event => {
      run++;
      opener = still.matches ? null : event.relatedTarget;
      if (opener) modal.classList.add('is-zooming');
    });

    modal.addEventListener('shown.bs.modal', async () => {
      if (!opener) return;
      const mine = run;
      const from = opener.getBoundingClientRect();
      // Let pictures load first so the window grows to its finished size
      const pictures = [...content.querySelectorAll('img[src]')].map(img => img.decode().catch(() => {}));
      await Promise.race([Promise.all(pictures), new Promise(done => setTimeout(done, 1000))]);
      if (mine !== run) return;

      const to = content.getBoundingClientRect();
      modal.classList.remove('is-zooming');
      zoom = step(onto(from, to), 'none');
    });

    modal.addEventListener('hide.bs.modal', event => {
      if (closing) {
        // Another Escape or click while the window is still shrinking
        if (zoom.playState !== 'finished') {
          event.preventDefault();
          return;
        }
        closing = false;
      } else if (opener && opener.offsetWidth && !modal.classList.contains('is-zooming')) {
        // Hold Bootstrap back, shrink into the opener, then close for real
        event.preventDefault();
        closing = true;
        if (zoom) zoom.cancel();
        zoom = step('none', onto(opener.getBoundingClientRect(), content.getBoundingClientRect()), 'forwards');
        zoom.finished.then(() => bootstrap.Modal.getInstance(modal).hide());
        return;
      }
      run++;
      cleanUp();
    });
  });
})();

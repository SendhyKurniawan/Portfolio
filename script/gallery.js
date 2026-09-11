// One picture viewer for the whole page. Anything with data-viewer joins a group,
// and the arrows walk that group without closing the window.
(() => {
  const modal = document.getElementById('viewerModal');
  if (!modal || !window.bootstrap) return;

  const image = modal.querySelector('#viewerImage');
  const title = modal.querySelector('#viewerTitle');
  const count = modal.querySelector('#viewerCount');
  const steppers = modal.querySelectorAll('[data-viewer-step]');
  let group = [];
  let index = 0;

  function show(next) {
    if (!group.length) return;
    // Wrap around, so the last Next lands back on the first picture
    index = (next + group.length) % group.length;
    const picture = group[index];
    const caption = picture.getAttribute('data-caption') || '';
    image.src = picture.getAttribute('data-src');
    image.alt = caption;
    title.textContent = caption;
    count.textContent = `${index + 1} of ${group.length}`;
    steppers.forEach(button => { button.hidden = group.length < 2; });
  }

  modal.addEventListener('show.bs.modal', event => {
    const opener = event.relatedTarget;
    if (!opener) return;
    group = [...document.querySelectorAll(`[data-viewer="${opener.getAttribute('data-viewer')}"]`)];
    show(group.indexOf(opener));
  });

  modal.addEventListener('hidden.bs.modal', () => {
    // Drop the file so a reopen doesn't flash the previous picture
    image.removeAttribute('src');
  });

  steppers.forEach(button => {
    button.addEventListener('click', () => show(index + Number(button.getAttribute('data-viewer-step'))));
  });

  modal.addEventListener('keydown', event => {
    if (event.key === 'ArrowLeft') show(index - 1);
    if (event.key === 'ArrowRight') show(index + 1);
  });

  document.querySelectorAll('[data-viewer]').forEach(opener => {
    opener.setAttribute('data-bs-toggle', 'modal');
    opener.setAttribute('data-bs-target', '#viewerModal');
  });
})();

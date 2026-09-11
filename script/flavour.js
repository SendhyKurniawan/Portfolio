// iMac flavour picker: recolours every title bar, gel button and link, remembered per browser
(() => {
  const STORAGE_KEY = 'kurse-flavour';
  const FLAVOURS = ['bondi', 'tangerine', 'grape', 'lime', 'strawberry'];
  const root = document.documentElement;
  const current = FLAVOURS.includes(root.dataset.flavour) ? root.dataset.flavour : 'bondi';

  document.querySelectorAll('input[name="flavour"]').forEach(input => {
    input.checked = input.value === current;
    input.addEventListener('change', () => {
      if (!FLAVOURS.includes(input.value)) return;
      if (input.value === 'bondi') delete root.dataset.flavour;
      else root.dataset.flavour = input.value;
      try {
        localStorage.setItem(STORAGE_KEY, input.value);
      } catch (e) {
        // Not saved, but the page still switches colour
      }
      window.kurseSound?.play('pop');
    });
  });
})();
